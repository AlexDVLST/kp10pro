import FileManager from '../components/FileManager';

window.productEdit = new Vue({
    el: '#product-edit-app',
    components: { 'file-manager': FileManager },
    data: {
        fileSrc: "",
        fileId: "",
        //Modal file manager
        showModalFileManager: false
    },
    mounted: function () {
        this.start();
        //Event when double click on image
        this.$refs.fileManager.$on('dblclick-image', (file) => {
            this.fileSrc = file.src;
            this.fileId = file.id;
            this.closeModal();
        });
        //Редактирование товара
        this.$refs.fileManager.$on('cropper-finished', (data) => {
            window.productEdit.fileId = data.fileId;
            window.productEdit.fileSrc = data.fileSrc;
        });
    },
    methods: {
        start() {
            let url = location.href;
            let pageType = $('.save-product').attr('data-type');
            if (pageType == "edit") {
                url = url.substring(0, url.lastIndexOf('/') + 1);
                axios.get(url + 'file')
                    .then(response => {
                        if (response.data.file === undefined) {
                            this.fileSrc = "/storage/resource/templates/base/product/empty.png";
                        } else {
                            this.fileSrc = '/' + response.data.path.replace('public', 'storage') + '/' + response.data.file;
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        //console.log(error.response.data);
                    });
            } else {
                this.fileSrc = "/storage/resource/templates/base/product/empty.png";
            }
        },
        isNumber: function (evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                evt.preventDefault();
            } else {
                return true;
            }
        },
        changeFile(el) {
            //Show modal
			this.showModalFileManager = true;
	
			let path = this.$refs.fileManager.path;
			if (!/Товары/.test(path)) {
			  path += "/Товары";
			}
			this.$refs.fileManager.show(path);
        },

        saveProduct() {

            //TODO: необхідно використовувати css
            $('#product-edit-app').find('input.form-control').css('border', '1px solid rgb(210, 214, 222)');
            $('#product-edit-app').find('textarea.form-control').css('border', '1px solid rgb(210, 214, 222)');
            
            //TODO: необхідно використовувати data для зберігання даних у моделі
            let product = {
                'product_name': $('#product_name').val(),
                'product_article': $('#product_article').val(),
                'product_cost': $('#product_cost').val(),
                'product_prime_cost': $('#product_prime_cost').val(),
                'description': $('#description').val(),
                'fileId': $('.product-file').attr('data-id')
            };

            //Дополнительыне поля товаров
            let productDopfields = [];
            let customFieldId = "";
            let dopfieldValue = "";
            $.each($('.product-dopfield'), function (index, value) {
                dopfieldValue = $(this).val();
                customFieldId = $(this).attr('data-customFieldId');
                productDopfields.push({ 'dopfieldValue': dopfieldValue, 'customFieldId': customFieldId });
            });
            //edit or add
            let pageType = $('.save-product').attr('data-type');
            switch (pageType) {
                case "edit":
                    axios.put('/products/' + $("#product-id").val(), {
                        product: product,
                        product_dopfields: productDopfields
                    }).then(function (response) {
                        if (response.data.status == "error") {
                            let field = "";
                            for (let key in response.data.fields) {
                                field = response.data.fields[key];
                                $('#' + key).css('border', '1px solid red');
                                // $('#lable_' + key).html(field);
                            }
                        } else {
                            location.reload();
                            // modal({
                            // 'modalTitle': 'Продукт сохранён',
                            // 'modalMessage': 'Продукт сохранён',
                            // 'okTitle': 'OK',
                            // 'cancelTitle': '',
                            // 'type': 'modal-default',
                            // 'onOk': function () {
                            //     location.reload();
                            // },
                            // 'onCancel': function () {
                            //     location.reload();
                            // }
                            // });
                        }
                    });
                    break;
                case "add":
                    axios.post('/products', {
                        product: product,
                        product_dopfields: productDopfields
                    }).then(function (response) {
                        if (response.data.status == "error") {
                            let field = "";
                            for (let key in response.data.fields) {
                                field = response.data.fields[key];
                                $('#' + key).css('border', '1px solid red');
                                $('#lable_' + key).html(field);
                            }
                        } else {
                            location.href = '/products';
                            // modal({
                            //     'modalTitle': 'Продукт сохранён',
                            //     'modalMessage': 'Продукт сохранён',
                            //     'okTitle': 'OK',
                            //     'cancelTitle': '',
                            //     'type': 'modal-default',
                            //     'onOk': function () {
                            //         location.href = '/products';
                            //     },
                            // }); 
                        }
                    });
                    break;
            }
        },
        addCustomField() {
            let message = "Внимание! Перед добавлением нового поля сохраните товар!<br>" +
                "Обратите внимание, что новое поле добавится ко всем товарам!<br><br>" +
                "<label>Введите название для нового поля</label> <br>" +
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
                    axios.post('/settings/product-custom-fields', { product_dop_field: product_dop_field }).then(function (response) {
                        document.location.href = location.href;
                    });

                },
                'onCancel': function () {
                }
            })
        },
        //Close modal with file manager
        closeModal: function () {
            this.showModalFileManager = false;
        }
    }
});