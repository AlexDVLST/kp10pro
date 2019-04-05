// var VueRouter = require('vue-router/dist/vue-router.common.js');
import VueRouter from 'vue-router'
Vue.use(VueRouter);

// const data = {
//     name: 'amoCRM',
//     scopes: []
// };

// axios.post('/oauth/personal-access-tokens', data)
//     .then(response => {
//         console.log(response.data.accessToken);
//     })
//     .catch (response => {
//         // List errors on response...
// 	});

// axios.get('/oauth/personal-access-tokens')
// 		.then(response => {
// 			console.log(response.data);
// 		});

// var tokenId = "43542517ad28b2a965e929194f32ca512d9ddc05f64c4bbd13960744eeaec54a8ea39f23a43c6fd2";

// axios.delete('/oauth/personal-access-tokens/' + tokenId);

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
		loaded: false,
		pagination: {
			'current_page': 1
		},
		offers: {},
		searchString: "",
		orderby: "",
		order: "asc",
		checkedOffers: [],
		showPannel: false, //panel with trash... buttons
		clickToggleSelection: false,
		systemStates: [], //all offer states
		showByIndex: null,
		modalCopy: {
			offer: [], //current selected offer
			title: 'Создание КП',
			templateTitle: 'Название нового КП',
			templateName: '',
			isTemplate: false,
			errors: {
				templateName: false,
				noSelectOfferText: 'Необходимо выбрать КП!',
				noSelectOffer: false
			}
		},
		kpListShow: false
	},
	created: function () {
		this.start();
	},
	mounted() {
		this.fetchProducts();
		$(this.$refs.modalCopy).on('hidden.bs.modal', function () {
			this.kpListShow = false;
		});
	},
	methods: {
		start() {
			let query = this.$route.query;
			this.searchString = query.search;
			this.orderby = query.orderby;
			this.order = query.order;
			this.pagination.current_page = query.page;
			$('#searchfield').attr('value', this.searchString);
		},
		fetchProducts() {
			let pageData = {
				'search': this.searchString,
				'orderby': this.orderby,
				'order': this.order,
			};
			pageData = JSON.parse(JSON.stringify(pageData));
			window.axios.get('/offers/list/json?page=' + this.pagination.current_page + '&' + jQuery.param(pageData))  //this.pagination.current_page
				.then(response => {

					//View marker 
					this.loaded = true;
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

					//Reset to defaults
					//clear checked

					//this.checkedOffers = [];
					//hide pannel
					//this.showPannel = false;

					// Чекаем все нужные элементы в зависимости от списка выбранных элементов
					this.offers.forEach((item, i, arr) => {
						// console.log(this.checkedOffers);
						// console.log(item.id);
						//console.log(this.checkedOffers.indexOf(item.id) == 0);
						if (this.checkedOffers.indexOf(item.id) !== -1) {
							item.check = 1;
						}
					});
				})
				.catch((error) => {
					window.ajaxError(error);
				});
			//Get system offer states
			window.axios.get('/offers/systemStates')
				.then(response => {
					this.systemStates = response.data;
				})
				.catch((error) => {
					window.ajaxError(error);
				});
				
			this.changeUrl();
		},
		search() {
			this.searchString = $('#searchfield').val();
			this.changeUrl();
			this.fetchProducts();
		},
		//Delete selected offer
		removeOffer(offerId) {
			modal({
				'modalTitle': 'Удаление коммерческого предложения',
				'modalMessage': 'Вы действительно хотите удалить коммерческое предложение?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': ($modal) => {
					window.axios.post('/offers/' + offerId + '/trash')
						.then((response) => {
							this.fetchProducts();
						})
						.catch((error) => {
							window.ajaxError(error);
						});

					//Hide modal
					$modal.modal('hide');
				}
			});
		},
		//Delete offers from DB
		removeOffers() {
			if (this.checkedOffers.length === 0) {
				return;
			}

			modal({
				'modalTitle': 'Удаление коммерческих предложений',
				'modalMessage': 'Вы действительно хотите удалить коммерческие предложения?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': ($modal) => {
					window.axios.post('/offers/trash', this.checkedOffers)
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
		//
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
			if (url !== "") {
				window.history.replaceState(null, null, '/offers?' + url);
			}
		},
		//check permission
		canDelete(offer) {
			if (offer.system === 1) {
				return false;
			}
			let permissions = window.laravel.user.permissions.filter(function (name, index) {
				return name === 'delete offer' || name === 'delete-own offer'
			});

			//Can delete all offers
			if (permissions.indexOf('delete offer') !== -1) {
				return true;
			}
			//Can delete own offers
			if (permissions.indexOf('delete-own offer') !== -1 && window.laravel.user.id === offer.user_id) {
				return true;
			}

			return false;
		},
		//check permission
		canCreate(offer) {
			let permissions = window.laravel.user.permissions.filter(function (name, index) {
				return name === 'create offer'
			});

			//Can delete all offers
			if (permissions.indexOf('create offer') !== -1) {
				return true;
			}

			return false;
		},
		//
		copyToClipbord(e, url) {
			const el = document.createElement('textarea');
			el.value = location.protocol + '//' + url;
			document.body.appendChild(el);
			el.select();
			document.execCommand('copy');
			document.body.removeChild(el);

			let $a = $(e.target).closest('a');

			$a.attr('data-original-title', 'Ссылка скопирована')
				.tooltip('show');

			setTimeout(function () {
				$a.attr('data-original-title', 'Скопировать ссылку')
			}, 200);
		},
		getDate(dt) {
			return dt.split(' ')[1];
		},
		getTime(dt) {
			return dt.split(' ')[0];
		},
		//Select/unselect offers
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
		//Update checkd offer
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
		//Change offer state
		setState(offer, state) {
			window.axios.put('/offers/' + offer.id + '/state', { stateId: state.id })
				.then((response) => {
					offer.state.data = state;
				})
				.catch((error) => {
					window.ajaxError(error);
				});
		},
		//Copy template
		copyTemplate(offer) { 
			this.kpListShow = false;
			let $modal = $(this.$refs.modalCopy);

			this.modalCopy.offer = offer;
			this.modalCopy.templateName = offer.offer_name;
			$modal.modal('show');
		},
		createTemplate(offer) {
			this.copyTemplate(offer);
		},
		createOffer() {
			let $modal = $(this.$refs.modalCopy);
			
			$('#kp-list').select2({
				placeholder: "Выберите шаблон",
				width: '570px',
				// minimumInputLength: 3,
				ajax: {
				  delay: 250,
				  url: "/offers/list/json",
				  data: function(params) {
					var query = {
					  search: params.term,
					  page: params.page || 1
					};
					// Query parameters will be ?search=[term]&page=[page]
					return query;
				  },
				  processResults: function(data, params) {
					params.page = params.page || 1;

					return {
					  results: $.map(data.data.data, function(item) {
						if (item && typeof item == 'object') {
							return {
							  id: item.id,
							  text: item.offer_name+' '+ item.template.version
							};
						}
					  }),
					  pagination: {
						more: params.page * 10 < data.total
					  }
					};
				  }
				}
			  })
			  .on("change", e => {
				//update data in parent component
				// this.offerId = $(e.target).val();
				this.modalCopy.offer.id = $(e.target).val();
				this.modalCopy.errors.noSelectOffer = false;
			  });

			this.kpListShow = true;
			$modal.modal('show');
		},
		//Modal, change
		modalCopyIsTemplate() {
			//update
			this.modalCopy.isTemplate = !this.modalCopy.isTemplate;

			if (this.modalCopy.isTemplate) {
				this.modalCopy.title = 'Создание шаблона';
				this.modalCopy.templateTitle = 'Название нового шаблона';
			} else {
				this.modalCopy.title = 'Создание КП';
				this.modalCopy.templateTitle = 'Название нового КП';
			}
		},
		//Modal, save
		modalCopySave() {

			if (!this.modalCopy.templateName) {
				return;
			}
			if (!this.modalCopy.offer.id) {
				this.modalCopy.errors.noSelectOffer = true;
				return;
			}
			
			window.axios.put('/offers/' + this.modalCopy.offer.id + '/copy',
				{ name: this.modalCopy.templateName, isTemplate: this.modalCopy.isTemplate })
				.then((response) => {
					//close modal
					$(this.$refs.modalCopy).modal('hide');
					//Open template
					location.href = '/editor/' + response.data.offer.id;

					// this.fetchProducts();
				})
				.catch((error) => {
					window.ajaxError(error);
				});
		},
		//Modal, set template name
		modalCopySetTemplateName(e) {
			this.modalCopy.templateName = e.target.value;

			if (!this.modalCopy.templateName) {
				//setError
				this.modalCopy.errors.templateName = true;
			} else {
				this.modalCopy.errors.templateName = false;
			}
		},
		//Show delimeter
		showVariantDelimeter(offer, index) {
			let show = false;
			offer.variants.forEach(function (variant, i) {
				if (i > index && variant.active) {
					show = true;
				}
			});
			return show;
		},
		//Format price
		numberFormat(str) {
			return window.numberFormat(str);
		},
		downloadFile() {
			let $modal = $(this.$refs.modalDownloadFile);
			$modal.modal('show');
		},
		//Return class for currency
		offerCurrency: function (offer) {
			let currency = {
				'icon': true
			};

			if (offer.currency && offer.currency.data && offer.currency.data.system) {
				let charCode = offer.currency.data.system.char_code.toLowerCase();
				currency['icon-' + charCode] = true;
			} else {
				//Default
				currency['icon-rub'] = true;
			}

			return currency;
		}
	},
});
