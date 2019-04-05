var VueRouter = require('vue-router/dist/vue-router.common.js');
Vue.use(VueRouter);

var router = new VueRouter({
	mode: 'history',
	watch: {
		'$route'(to, from) {
			// react to route changes...
		}
	}
});

const app = new Vue({
	router,
	el: '#app',
	data: {
		pagination: {
			'current_page': 1
		},
		offers: {},
		searchString: "",
		orderby: "",
		order: "asc",
		checkedOffers: [],
		showPannel: false,
		showByIndex: null
	},
	created: function () {
		this.start();
	},
	methods: {
		start() {
			let query = this.$route.query;
			this.orderby = query.orderby;
			this.order = query.order;
			this.pagination.current_page = query.page;
			this.searchString = query.search;
		},
		fetchProducts() {
			let pageData = {
				'search': this.searchString,
				'orderby': this.orderby,
				'order': this.order,
			};
			pageData = JSON.parse(JSON.stringify(pageData));
			axios.get('/settings/offers/removed/json?page=' + this.pagination.current_page + '&' + jQuery.param(pageData))  //this.pagination.current_page
				.then(response => {
					this.offers = response.data.data.data;
					this.pagination = response.data.pagination;

					this.$nextTick(function () {
						//Tooltop
						$('[data-toggle="tooltip"]').tooltip();
						//
						$('input[type="checkbox"]').iCheck({
							checkboxClass: 'icheckbox_flat-blue'
						}).on('ifChanged', (e) => {
							//Fix for vue event
							$(e.target).trigger('click');
						});
					});

					// Чекаем все нужные элементы в зависимости от списка выбранных элементов
					this.offers.forEach((item, i, arr) => {
						// console.log(this.checkedOffers);
						// console.log(item.id);
						// console.log(this.checkedOffers.indexOf(item.id) == 0);
						if(this.checkedOffers.indexOf(item.id) !== -1) {
							item.check = 1;
						}
					});

				})
				.catch(error => {
					console.log(error.response.data);
				});

			this.changeUrl();
		},
		deleteOffer(offer_id) {
			modal({
				'modalTitle': 'Удаление коммерческого предложения',
				'modalMessage': 'Вы уверены что хотите удалить коммерческое предложение навсегда?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'default',
				'onOk': function () {
					axios.delete('/offers/' + offer_id).then(function (response) {
						window.location.reload();
					});
				},
				'onCancel': function () {
				}
			})
		},
		restoreOffer(offer_id) {
			modal({
				'modalTitle': 'Восстановление коммерческого предложения',
				'modalMessage': 'Восстановить коммерческое предложение?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'default',
				'onOk': function () {
					axios.post('/offers/' + offer_id + '/restore').then(function (response) {
						window.location.reload();
					});
				},
				'onCancel': function () {
				}
			})
		},
		sortby(sortfield) {
			if (this.order == "asc") {
				this.order = "desc"
			}
			else {
				this.order = "asc"
			}
			this.orderby = sortfield;
			this.changeUrl();
			this.fetchProducts();
		},
		changeUrl() {
			let url = "";
			let pageData = {
				'search': this.searchString,
				'orderby': this.orderby,
				'order': this.order,
				'page': this.pagination.current_page
			};
			pageData = JSON.parse(JSON.stringify(pageData));
			url = jQuery.param(pageData);
			if(url!=="") {
				window.history.replaceState(null, null, '/settings/offers/removed?' + url);
			}
		},
		search() {
			this.searchString = $('#searchfield').val();
			this.changeUrl();
			this.fetchProducts();
		},

		updateCheckedOffers(offer) {
			if (!offer.system) {

				let index = this.checkedOffers.indexOf(offer.id);


				if (index === -1) {
					//Add
					this.checkedOffers.push(offer.id);
					offer.check = 1;
				} else {
					//Remove
					this.checkedOffers.splice(index, 1);
					offer.check = 0;
				}

				//Show/hide pannel
				if (this.checkedOffers.length > 0) {
					this.showPannel = true;
				} else {
					this.showPannel = false;
				}

			}
		},
		toogleSelection() {
			if (this.clickToggleSelection) {
				//Uncheck all checkboxes
				$("input[type='checkbox']").slice(1).iCheck("uncheck")
			} else {
				//Check all checkboxes
				$("input[type='checkbox']").slice(1).iCheck("check");
			}

			this.clickToggleSelection = !this.clickToggleSelection;
		},

		restoreOffers(){
			// Восстановление всех выделенных КП-шек

			if (this.checkedOffers.length === 0) {
				return;
			}

			modal({
				'modalTitle': 'Восстановление коммерческих предложений',
				'modalMessage': 'Восстановить выбранные коммерческие предложения?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': ($modal) => {
					window.axios.post('/offers/restore', this.checkedOffers)
						.then((response) => {
							this.fetchProducts();
							//Find first checkbox and click on it
							$("input[type='checkbox']:first-child").iCheck("uncheck");

						})
						.catch((error) => {
							window.ajaxError(error);
						});

					//Hide modal
					$modal.modal('hide');
				},
				'onCancel': function () {
				}
			});

		}
	},
	mounted() {
		this.fetchProducts();
	}
});
