const app = new Vue({
	el: '#app',
	data: {
		customfields: {},
		pagination: { 
			'current_page': 1
		},
	},
	methods: {
		fetchProducts() {
			axios.get('/product-custom-fields/list/json?page=' + this.pagination.current_page)  //this.pagination.current_page
				.then(response => {
					this.customfields = response.data.data.data;
					this.pagination = response.data.pagination;
				})
				.catch(error => {
					console.log(error.response.data);
				});
		},
		addCustomField() {
			let message = "<label>Введите название для нового поля</label><br>" +
				"Обратите внимание, что новое поле добавится ко всем товарам!<br><br>"+
				"<input id='product-dopfield-name' class='form-control' type='text' value='' placeholder='Введите название нового поля'><br>" +
				"<label>Выберите тип дополнительного поля</label>" +
				"<select id='product-dopfield-type' class='form-control'>" +
				"<option value='name'>Название</option>" +
				"<option value='article'>Артикул</option>" +
				"<option value='cost'>Цена</option>" +
				"<option value='primecost'>Себестоимость</option>" +
				"<option value='description'>Описание</option>" +
				"</select>";
			modal({
				'modalTitle': 'Добавление дополнительного поля',
				'modalMessage': message,
				'okTitle': 'Добавить', 
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': function () {
					let product_dopfield_name = $('#product-dopfield-name').val();
					let product_dopfield_type = $('#product-dopfield-type').val();
					let product_dop_field = {
						'name': product_dopfield_name,
						'type': product_dopfield_type
					};
					axios.post('/settings/product-custom-fields', {product_dop_field: product_dop_field}).then(function (response) {
						document.location.href = location.href;
					}); 

				},
				'onCancel': function () {
				}
			})
		},
		deleteCustomField(custom_field_id) {
			modal({
				'modalTitle': 'Удаление дополнительного поля',
				'modalMessage': 'Вы действительно хотите удалить данное поле?',
				'okTitle': 'Да',
				'cancelTitle': 'Отмена',
				'type': 'modal-default',
				'onOk': function () {
					axios.delete('/settings/product-custom-fields/' + custom_field_id).then(function (response) {
						document.location.href = location.href;
					});
				},
				'onCancel': function () {
				}
			}) 
		},
		editCustomField(custom_field_id) {
			axios.get('/settings/product-custom-fields/' + custom_field_id).then(function (response) {
				name = response.data['name'];
				type = response.data['type'];
				let message = "<label>Введите название для нового поля</label><br>" +
					"<input id='product-dopfield-name' class='form-control' type='text' value='" + name + "' placeholder='Введите название нового поля'><br>" +
					"<label>Выберите тип дополнительного поля</label>" +
					"<select id='product-dopfield-type' class='form-control'>";

				//Todo Безобразие. Нужно исправить этот балаган!

				if (type == 'name') {
					message += "<option selected='selected' value='name'>Название</option>";
				}
				else {
					message += "<option value='name'>Название</option>";
				}

				if (type == 'article') {
					message += "<option selected='selected' value='article'>Артикул</option>";
				}
				else {
					message += "<option value='article'>Артикул</option>";
				}

				if (type == 'cost') {
					message += "<option selected='selected' value='cost'>Цена</option>";
				}
				else {
					message += "<option value='cost'>Цена</option>";
				}

				if (type == 'primecost') {
					message += "<option selected='selected' value='primecost'>Себестоимость</option>";
				}
				else {
					message += "<option value='primecost'>Себестоимость</option>";
				}

				if (type == 'description') {
					message += "<option selected='selected' value='description'>Описание</option>";
				}
				else {
					message += "<option value='description'>Описание</option>";
				}

				message += "</select>";

				modal({
					'modalTitle': 'Редактирование дополнительного поля',
					'modalMessage': message,
					'okTitle': 'Да',
					'cancelTitle': 'Отмена',
					'type': 'modal-default',
					'onOk': function () {
						let product_dopfield_name = $('#product-dopfield-name').val();
						let product_dopfield_type = $('#product-dopfield-type').val();
						let product_dop_field = {
							'name': product_dopfield_name,
							'type': product_dopfield_type
						};
						axios.put('/settings/product-custom-fields/' + custom_field_id, {product_dop_field: product_dop_field}).then(function (response) {
							document.location.href = location.href;
						});
					},
					'onCancel': function () {
					}
				})
			});
		}
	},
	mounted() {
		this.fetchProducts();
	}
});

