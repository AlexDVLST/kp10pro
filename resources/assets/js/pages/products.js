import VueRouter from 'vue-router';
import FileManager from '../components/FileManager';
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
	components: { 'file-manager': FileManager },
	data: {

		/*
		* Single product image change
		*/

		fileSrc: "",
		fileId: "",
		prodId: "",
		products: {},
		pagination: {
			'current_page': 1
		},

		orderby: "", // По какому полю идёт сортировка
		order: "asc", // Сортировка по умолчанию или по убыванию

		/*
		* Базовая валюта
		*/

		basicCurrency: "", // todo переделать. Валюты базовые должны сразу попадать в базу данных а не так
		enableProductColl: [], // Список выбранных отображаемых полей
		enableFieldCheckboxBlock: false, 	// Отображение окна выбора отображаемых полей
		collapsedDescriptions: [], // Список развёрнутых описаний продуктов

		/*
		* Search
		*/

		searchString: "", // Строка поиска
		typingTimer: "", // Время ожидания ввода в строку поиска

		/*
		* Product checkboxes
		*/

		// Список выбранных товаров. Чекбокс с лева.
		checkedProduct: [],
		// Выделен ли хотя бы один чекбокс в товарах
		isProductsSelectChecked: false,
		lastCheckedProductId: false,

		existingProductsCount: 0,
		newProductsCount: 0,
		//Modal file manager
		showModalFileManager: false

	},
	created: function () {
		this.start();
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
			axios.get('/products/list/json?page=' + this.pagination.current_page + '&' + jQuery.param(pageData))  //this.pagination.current_page
				.then(response => {
					this.products = response.data.products;
					this.pagination = response.data.pagination;

					if (typeof (response.data.basicCurrency) == "undefined") {
						this.basicCurrency = "руб.";
					}
					else {
						this.basicCurrency = response.data.basicCurrency.sign;
					}
				})
				.catch(error => {
					console.log(error.response.data);
				});
			this.changeUrl();
		},
		search() {
			clearTimeout(this.typingTimer);
			this.typingTimer = setTimeout(this.searchDoneTyping, 600);
		},

		searchDoneTyping() {
			this.searchString = $('#searchfield').val();
			this.changeUrl();
			this.fetchProducts();
		},

		isProductsCheckboxChecked(event) {
			if (this.checkedProduct.length == 0) {
				this.isProductsSelectChecked = false;
			} else {
				this.isProductsSelectChecked = true;
			}
		},

		removeProduct(product_id) {
			modal({
				'modalTitle': 'Удаление товара',
				'modalMessage': 'Вы действительно хотите удалить товар?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': function () {
					axios.post(location.href + '/' + product_id + '/remove/').then(function (response) {
						location.reload();
					});
				},
				'onCancel': function () {
				}
			})
		},
		sortby(sortfield) {
			if (this.order == "asc") {
				this.order = "desc";
			}
			else {
				this.order = "asc";
			}
			this.orderby = sortfield;
			this.changeUrl();
			this.fetchProducts();
		},

		shiftClick(e) {
			if (e.shiftKey) {
				// SHIFT is pressed
				let currentCheckedProductId = e.target.value;
				let from = $('.productSelect').index($('#' + currentCheckedProductId));
				let to = $('.productSelect').index($('#' + this.lastCheckedProductId));
				if (from > to) {
					from = [to, to = from][0];
				}

				$('.productSelect').slice(from, to).each(function (index, value) {
					app.checkedProduct.push($(this).val());
				});
				this.lastCheckedProductId = e.target.value;
			} else {
				// SHIF is not pressed 
				this.lastCheckedProductId = e.target.value;
			}
		},

		selectAllProducts(event) {
			if (event.target.checked === true) {
				axios.get('/products/list/json?quickList')
					.then(response => {
						let allProducts = response.data.products;
						let product = "";
						for (var index in allProducts) {
							product = allProducts[index];
							this.checkedProduct.push(product.id);
						}
					});
				this.isProductsSelectChecked = true;
			} else {
				this.checkedProduct = [];
				this.isProductsSelectChecked = false;
			}

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
			if (url !== "") {
				window.history.replaceState(null, null, '/products?' + url);
			}
		},
		importExcel() {
			// Окно выбора загружаемого файла
			let html = "<input id='file' type='file' value='Загрузить файл'>";
			html += '<div class="progress sm"><div class="exelProgressBar progress-bar progress-bar-aqua" style=""></div></div>';
			modal({
				'modalTitle': 'Импорт товаров из Excel',
				'modalMessage': html,
				'okTitle': 'Импорт',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': function () {
					// Выбранный файл для загрузки:
					let formData = new FormData();
					let excelFile = document.querySelector('#file');
					formData.append("file", excelFile.files[0]);
					// Опправляем для начала файл на валидацию данных.
					axios.post('/products/excelCheck', formData, {
						headers: {
							'Content-Type': 'multipart/form-data'
						}
					}).then(function (response) {

						// Ответ после валидации файла 
						let data = response.data;
						// Список конфкликтных артиклов (товаров) 
						let existingProducts = data.files.existingProducts;
						// Список новых артиклов (товаров)
						let newProducts = data.files.newProducts;
						// Обьявляем текст сообщения
						let html = "";
						// Если всё же конфликты есть:

						app.existingProductsCount = existingProducts.length;

						app.newProductsCount = newProducts.length;

						if (existingProducts.length > 0) {
							// Текст сообщения
							html = "<label>Внимание! При загрузке обнаружены товары с артикулами, которые уже есть в каталоге.</label> <br>";
							// Список конфликтных артикулов. (товаров)
							html += "<select style='width:100%' size='5'>";
							existingProducts.forEach(function (product) {
								html += "<option>" + product + "</option>";
							});
							html += "</select>";

							html += '<div class="progress sm"><div class="exelProgressBar progress-bar progress-bar-aqua" style=""></div></div>';

							// Количество новых и старых артикулов. (товаров)
							let countImportProducts = newProducts.length + existingProducts.length;
							// Форма
							html += '<br><br><label>Выберите дальнейшее действие:</label><select class="importExcelConflictSelector form-control">';
								if(newProducts.length > 0){
									html += '<option value="onlynew">Загрузить только новые ( ' + newProducts.length + ' )  ( добавить только товары с новыми артикулами )</option>';
									html += '<option value="addandreplace">Загрузить и обновить ( ' + newProducts.length + ' новых ' + existingProducts.length + ' обновить )</option>';
								}
								else{
									html += '<option value="addandreplace">Обновить ( '+existingProducts.length+' )</option>';
								}

								html +=	'<option value="allnew">Создать как новые (  ' + countImportProducts + ' новых ) </option>' +
								'</select>';

							modal({
								'modalTitle': 'Импорт товаров из Excel',
								'modalMessage': html,
								'okTitle': 'Импорт',
								'cancelTitle': 'Отмена',
								'type': 'modal-default',
								'onOk': function () {
									app.importProductsExel($('.importExcelConflictSelector').val(), excelFile);
								}
							});
						} else {

							// Конфликтов нет. Все товары новые.
							let formData = new FormData();
							let excelFile = document.querySelector('#file');
							formData.append("file", excelFile.files[0]);
							formData.append("type", 'noconflict');
							formData.append("existingProductsCount", app.existingProductsCount);
							formData.append("newProductsCount", app.newProductsCount);

							app.excelProgress = setTimeout(app.excelProgressFunction, 600);

							axios.post('/products/excel', formData, {
								headers: {
									'Content-Type': 'multipart/form-data'
								},

							}).then(function (response) {
								console.log(response);
								location.reload();   // Перезагрузка страницы
							});
						}
					});
				},
				'onCancel': function () {
				}
			})
		},


		importProductsExel(type, excelFile) {
			let formData = new FormData();
			formData.append("file", excelFile.files[0]);
			formData.append("type", type);
			formData.append("existingProductsCount", app.existingProductsCount);
			formData.append("newProductsCount", app.newProductsCount);

			//clearTimeout(app.excelProgress);
			app.excelProgress = setTimeout(app.excelProgressFunction, 600);

			axios.post('/products/excel', formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}).then(function (response) {
				location.reload();   // Перезагрузка страницы
			});
		},

		// Отрисовка progressBar
		excelProgressFunction() {
			clearTimeout(app.excelProgress);
			app.excelProgress = setTimeout(app.excelProgressFunction, 600);
			axios.get('/products/excel/progress', { progress: 'progressstatus' }).then(function (response) {
				let excelImoirtProgresses = response.data.progress;
				excelImoirtProgresses = parseInt(excelImoirtProgresses);
				if (excelImoirtProgresses > 0) {
					$('.exelProgressBar').css('width', '' + excelImoirtProgresses + '%');
				}
			});
		},

		deleteSelected() {
			let checkedProducts = this.checkedProduct;
			modal({
				'modalTitle': 'Удалить выбранные товары?',
				'modalMessage': 'Внимание! Выбранные товары будут удалены!',
				'okTitle': 'Удалить',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': function () {
					axios.post('/products/remove', { data: checkedProducts }).then(function (response) {
						location.reload();
					});
				},
				'onCancel': function () {
				}
			})
		},
		changeFile(prodId) {
			this.prodId = prodId;
			
			//Show modal
			this.showModalFileManager = true;
	
			let path = this.$refs.fileManager.path;
			if (!/Товары/.test(path)) {
			  path += "/Товары";
			}
			this.$refs.fileManager.show(path);
		},
		saveFile() {
			let product = {
				'fileId': this.fileId
			};
			axios.put('/products/' + this.prodId, {
				product: product, updateType: 'file'
			}).then(function (response) {
				location.reload();
			});
		},
		ProductColl() {
			let enableProductColl = this.enableProductColl;
			// Сохранение отображаемых полей
			axios.post('/products/setusercollumns', { enableProductColl: enableProductColl })
				.then(response => {
				})
				.catch(error => {
				});
		},
		getUserCollumns() {
			axios.get('/products/getusercollumns')
				.then(response => {
					var enableProductColl = [];
					if (response.data) {
						this.enableProductColl = response.data;
					}
				})
				.catch(error => {
				});
		},
		displayFieldsCheckboxes() {
			this.enableFieldCheckboxBlock = !this.enableFieldCheckboxBlock;
		},

		showDescription(productid) {
			// Клик по кнопке (развернуть описание) работает для разворачивания описания и сворачивания.
			// Есть ли в массиве развёрнутых описаний наш товар ?
			if (this.collapsedDescriptions.indexOf(productid) + 1 == 0) {
				// Добавляем id товара, описание которого хотим развернуть в массив
				this.collapsedDescriptions.push(productid);
			} else {
				// Если товар уже есть в массисе, удаляем его.
				this.collapsedDescriptions.splice(this.collapsedDescriptions.indexOf(productid), 1);
			}
		},
		//Close modal with file manager
		closeModal: function(){
			this.showModalFileManager = false;
		}
	},
	mounted() {
		this.fetchProducts();
		this.$refs.fileManager.$on('dblclick-image', (file) => {
			this.fileSrc = file.src;
			this.fileId = file.id;
			this.closeModal();
			this.saveFile();
		});
		//Редактирование товара
		this.$refs.fileManager.$on('cropper-finished', (data) => {
			window.productEdit.fileId = data.fileId;
			window.productEdit.fileSrc = data.fileSrc;
			this.saveFile();
		});
		this.getUserCollumns();
	}
});

