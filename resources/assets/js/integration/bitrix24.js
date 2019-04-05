window.Vue = require('vue');
window.axios = require('axios');

import svgIcon from "./Svg";

new Vue({
    el: '#kp10-widget-bitrix24',
    props: {
        offer: {
            default: ""
        }
    },
    components: {
        svgIcon
    },
    data: {
        integration: false,
        integrationStatus: '',
        integrationStatusText: '',
        integrationStatusMessage: '',
        integrationStatusDomain: '',
        widget: false,
        apiKey: '',
        kp10Host: '',
        dealId: '',
        createOfferBlock: false,
        showCreateForm: false,
        selectTemplateShow: false,
        showKpBtnAndName: false,
        creatingOfferBlock: false,
        currency: '',
        template: [],
        offerId: '',
        offerName: '',
        disabledBtn: false,
        preloader: false,
        productImg: '',
        productName: '',
        productDesc: '',
        productCost: '',
        productCount: '',
        productPrice: '',
        productDiscount: '',
        specToken: '',
        uId: '',
        isErrorName: false,
        isErrorId: false,
        errorMessage: '',

        offerUrl: "",
        dataOfferId: "",
        loadOfferShow: false,
        variants: [],
        variantsForCancel: [],
        cardTabsActive: "kp-selected kp-card-tabs-item-preload",
        // currency: 'uah',
        selectedVariantIndex: 0,
        showEditOfferBtn: false,
        totalCostValue: 0,
        kpGroupCost: [],
        kpDiscount: [],
        rotateArrow: true,
        arrowIcon: "arrow-down",
        kpTooltipCopy: 'Скопировать ссылку',
        productGoodColl: [],
        tempSearch: [],
        defaultProductImg: '',
        showKP10Widget: false,
        showCreateProduct: true,
        tax: 0,
        varSelected: 0,
        noClick: true,
        uid: 0
    },
    mounted() {

        if (data.PLACEMENT == 'CRM_DEAL_DETAIL_TAB') {
            this.dealId = JSON.parse(data.PLACEMENT_OPTIONS).ID;
        }

        this.bindWidgetInDealCard();
    },
    methods: {
        bindWidgetInDealCard() {

            if (typeof data.PLACEMENT !== 'undefined' && data.PLACEMENT == 'DEFAULT') {

                let placement = 'CRM_DEAL_DETAIL_TAB';

                BX24.callMethod('placement.get', '', (response) => {
                    if (response.status == 200) {
                        let answer = response.answer,
                            isset_placement = 0;

                        if (answer.result.length > 0) {

                            $.each(answer.result, function (key, value) {

                                if (value.placement == placement) {
                                    isset_placement = 1;

                                    return false;
                                }
                            });
                        }

                        //приложение не было встроено
                        if (!isset_placement) {

                            this.integrationStatusText = 'Подождите, пожалуйста, приложение устанавливается...';
                            this.integration = true;

                            BX24.callMethod('placement.bind', {
                                PLACEMENT: placement,
                                HANDLER: 'https://admin.kp10.pro/integration/bitrix24',
                                // HANDLER: 'https://kp10.loc/integration/bitrix24',
                                TITLE: 'КП 10',
                                DESCRIPTION: 'установка приложения КП 10'
                            }, (bind) => {

                                //получаем информацию по текущему пользователю
                                BX24.callMethod('user.current', {}, (res) => {

                                    let userInfo = {
                                        current_user_id: res.data().ID,
                                        current_user_name: res.data().NAME,
                                        current_user_last_name: res.data().LAST_NAME,
                                        current_user_email: res.data().EMAIL,
                                        current_user_phone: res.data().PERSONAL_MOBILE
                                    };

                                    // let employees = [];

                                    //получаем всех сотрудников компании
                                    // BX24.callMethod('user.get', {}, (ans) => {
                                    //
                                    //     let result = ans.answer.result;
                                    //
                                         //убираем из массива пользователя, который в данный момент устанавливает виджет
                                        // $.each(result, function (key, value) {
                                        //     if (value.ID != res.data().ID && value.ACTIVE) {
                                        //         employees.push({
                                        //             user_id: value.ID,
                                        //             user_name: value.NAME,
                                        //             user_last_name: value.LAST_NAME,
                                        //             user_email: value.EMAIL,
                                        //             user_phone: value.PERSONAL_MOBILE
                                        //         });
                                        //     }
                                        // });

                                        /**
                                         * проверяем существует ли аккаунт компании на кп10, которая устанавливает наше приложение
                                         */
                                        window.axios.post('/integration/bitrix24/check', {
                                            settings: data,
                                            user: userInfo
                                            // employees: employees
                                        })
                                            .then(response => {

                                                this.integrationStatus = response.data.status;

                                                if (this.integrationStatus === 'success') {

                                                    let employees = response.data.tokens,
                                                        integration = response.data.integration,
                                                        domain = response.data.domain;

                                                    this.integrationStatusDomain = 'https://' + data.DOMAIN + '/crm/deal/category/0/';
                                                    this.integrationStatusText = 'Интеграция успешно завершена!';

                                                    this.integrationStatusMessage = '';
                                                    if(integration){
                                                        this.integrationStatusMessage = "Мы создали Вам новый <a href='https://"+ domain +"' target='_blank'>аккаунт КП10</a>.<br>Доступы от него отправлены в виде письма на почту, указанную в Вашем профиле. <br>Спасибо, что выбрали наш продукт.<br>";
                                                    }

                                                    this.integrationStatusMessage += "Теперь Вы можете использовать функционал виджета. Найти его можно в любой карточке <a href='" + this.integrationStatusDomain + "' target='_blank'>сделки.</a>";

                                                    BX24.appOption.set('domain', domain);

                                                    if (employees.length > 0) {
                                                        $.each(employees, function (key, value) {
                                                            BX24.appOption.set(value.id, value.token);
                                                        });
                                                    }

                                                    BX24.callBind('ONCRMDEALUPDATE', 'https://admin.kp10.pro/integration/bitrix24/events');
                                                    // BX24.callBind('ONCRMDEALUPDATE', 'https://postb.in/a2IgHkgE');
                                                } else {
                                                    this.integrationStatusText = 'Ошибка установки приложения (виджета) КП10:';
                                                    this.integrationStatusMessage = response.data.message;
                                                }

                                                //отображаем блок интеграции
                                                this.integration = true;
                                            })
                                            .catch((error) => {

                                                this.integrationStatus = 'error';
                                                this.integrationStatusText = 'Ошибка установки приложения (виджета) КП10:';
                                                this.integrationStatusMessage = error.response.data.message + ' (' + error.response.data.errors + ')';

                                                //отображаем блок интеграции
                                                this.integration = true;

                                                // BX24.callMethod('placement.unbind', {
                                                //     PLACEMENT: placement,
                                                //     // HANDLER: 'https://admin.kp10.pro/integration/bitrix24',
                                                //     // HANDLER: 'https://kp10.loc/integration/bitrix24'
                                                // });
                                            });
                                    // });
                                });
                            });
                        } else { //приложение уже встроено
                            this.integrationStatus = 'installed';

                            this.integrationStatusDomain = 'https://' + data.DOMAIN + '/crm/deal/category/0/';
                            this.integrationStatusText = 'Интеграция с КП10 уже проведена!';
                            this.integrationStatusMessage = "Вы можете использовать функционал виджета. Найти его можно в любой карточке <a href='" + this.integrationStatusDomain + "' target='_blank'>сделки.</a>";

                            //отображаем блок интеграции
                            this.integration = true;
                        }
                    }
                });
            } else {
                //получаем информацию по текущему пользователю
                BX24.callMethod('user.current', {}, (res) => {
                    let current_user = res.answer.result,
                        domain = BX24.appOption.get('domain'),
                        apiKey = BX24.appOption.get('kp-' + current_user.ID);

                    if (typeof apiKey !== 'undefined' && typeof domain !== 'undefined') {

                        this.apiKey = apiKey;
                        this.kp10Host = domain;

                        this.loadOffer();
                    }
                });
            }
        },

        getUsers(start, count, auth) {
            //todo AIM: пока не используем данный метод
            window.axios.post('https://worksman.bitrix24.ru/rest/user.get.json', {
                auth: auth,
                start: start
            }).then(response => {
                let result = response.data.result,
                    next = response.data.next,
                    total = response.data.total;

                if(typeof total !== 'undefined' && typeof next !== 'undefined' && total > next*count){
                    this.getUsers(Number(next), count+1, auth);
                }
            });
        },

        loadOffer() {
            window.axios.defaults.headers.common["Accept"] = "application/json";
            window.axios.defaults.headers.common["Authorization"] = "Bearer " + this.apiKey;

            window.axios.get("https://" + this.kp10Host + "/api/bitrix24/deals/" + this.dealId + "/offer")
                .then(response => {

                    let offers = response.data.offer,
                        check = false;

                    if (offers && offers.currency) {
                        this.currency = this.offerCurrency(response.data.offer);
                    }

                    if (offers) {
                        this.specToken = response.data.token;
                        this.uId = response.data.user;

                        this.defaultProductImg = response.data.offer.productEmptyImg;
                        response.data.offer.variants.forEach(variant => {

                            if (variant.selected == 1) {
                                this.varSelected = true;
                            }

                            if (variant.products && variant.products.length) {
                                check = true;

                                //Sort
                                variant.products.sort(function (a, b) {
                                    return a.index - b.index;
                                });

                                variant.products.forEach(product => {
                                    //Sort values
                                    product.values && product.values.sort(function (a, b) {
                                        return a.index - b.index;
                                    });
                                });
                            }

                            //Sort fields
                            variant.fields && variant.fields.sort(function (a, b) {
                                return a.index - b.index;
                            });
                        });

                        // не показуємо блок створення КП
                        this.createOfferBlock = false;
                        this.showKP10Widget = true;
                    } else {
                        // показуємо блок створення КП
                        this.createOfferBlock = true;
                    }

                    this.widget = true;

                    // show/hide kp10 widget
                    this.rotateArrow = localStorage.getItem("hideShowKP10") == "true";

                    if (this.rotateArrow) {
                        this.arrowIcon = "arrow-up";
                    } else {
                        this.arrowIcon = "arrow-down";
                    }

                    if (!check) {
                        //Show message with link to editor. Need for first run
                        // return;
                    }

                    this.offerUrl = response.data.offer.url;

                    let copy = JSON.parse(JSON.stringify(response.data.offer.variants));

                    this._beforeEditingCache = copy;

                    this.variants = response.data.offer.variants;

                    this.variants.forEach(variant => {
                        this.calculatePositionsPrices(variant.id);
                    });

                    this.$nextTick(() => {
                        $('.search-product').select2({
                            placeholder: "Выберите товар",
                            width: '80%',
                            templateResult: this.imageSearch,
                            language: {
                                noResults: function (params) {
                                    return 'Товар не найден';
                                }
                            },
                            language: "ru",
                            // minimumInputLength: 3,
                            ajax: {
                                cache: true,
                                delay: 250,
                                headers: {
                                    "Accept": "application/json",
                                    "Authorization": "Bearer " + this.apiKey
                                },
                                url: "https://" + this.kp10Host + "/api/products/list/json",
                                data: function (params) {
                                    let query = {
                                        search: params.term,
                                        page: params.page || 1
                                    };

                                    return query;
                                },
                                processResults: (data, params) => {
                                    params.page = params.page || 1;

                                    this.tempSearch = data.products;

                                    return {
                                        results: $.map(data.products, (item) => {
                                            if (item && typeof item == 'object') {
                                                return {
                                                    id: item.id,
                                                    text: item.name,
                                                    img: item.file[0],
                                                    cost: item.cost
                                                };
                                            }
                                        }),
                                        pagination: {
                                            more: params.page * 10 < data.total
                                        }
                                    };
                                }
                            }
                        }).on("change", e => {
                            $('#select2-searchProduct-container').text('');
                            e.stopImmediatePropagation();
                            let prod_id = $(e.target).val(),
                                selectedVariant = this.variants[this.selectedVariantIndex];

                            this.tempSearch.forEach((item) => {
                                if (item.id == prod_id) {
                                    let productId = 0,
                                        // productId = item.id,
                                        index = selectedVariant.products.length,
                                        product = selectedVariant.products,
                                        d = new Date(),
                                        cProductId = d.getTime(), //new product id;
                                        type = selectedVariant.type,
                                        fakeProductId = '#modal-product-' + (cProductId + productId) + '-' + type,
                                        fields = selectedVariant.fields,
                                        values = [];

                                    // selectedVariant = this.variants[this.selectedVariantIndex];

                                    if (!selectedVariant['db-id']) {
                                        selectedVariant['db-id'] = selectedVariant.id;
                                    }

                                    fields.forEach((v, k) => {
                                        let valueInPrice = 0,
                                            value = '';

                                        switch (v.type) {
                                            case "name":
                                                value = item.name;
                                                break;
                                            case "cost":
                                                value = this.numberFormat(item.cost);
                                                break;
                                            case "count":
                                                value = '1';
                                                break;
                                            case "price":
                                                value = this.numberFormat(item.cost);
                                                break;
                                            case "discount":
                                                value = '0';
                                                break;
                                            case "good-coll":
                                                value = v.value;
                                                valueInPrice = 1;
                                                break;
                                        }

                                        values.push({
                                            'db-id': 0,
                                            index: k,
                                            type: v.type,
                                            value: value,
                                            value_in_price: valueInPrice,
                                            variant_product_id: item.id
                                        });
                                    });

                                    // if(selectedVariant.products.length > 0){
                                    //
                                    //     for (let i = 0; i < selectedVariant.products.length; i++) {
                                    //         let props = selectedVariant.products[i];
                                    //
                                    //         props['db-id'] = 0;
                                    //
                                    //         if (props.group == 0) {
                                    //
                                    //             let val = props.values;
                                    //             let value = '';
                                    //
                                    //             val.forEach((v, k) => {
                                    //                 let valueInPrice = 0;
                                    //
                                    //                 switch (v.type) {
                                    //                     case "name":
                                    //                         value = item.name;
                                    //                         break;
                                    //                     case "cost":
                                    //                         value = this.numberFormat(item.cost);
                                    //                         break;
                                    //                     case "count":
                                    //                         value = '1';
                                    //                         break;
                                    //                     case "price":
                                    //                         value = this.numberFormat(item.cost);
                                    //                         break;
                                    //                     case "discount":
                                    //                         value = '0';
                                    //                         break;
                                    //                     case "good-coll":
                                    //                         value = v.value;
                                    //                         valueInPrice = 1;
                                    //                         break;
                                    //                 }
                                    //
                                    //                 values.push({
                                    //                     'db-id': 0,
                                    //                     index: k,
                                    //                     type: v.type,
                                    //                     value: value,
                                    //                     value_in_price: valueInPrice,
                                    //                     variant_product_id: item.id
                                    //                 });
                                    //             });
                                    //             break;
                                    //         }
                                    //     }
                                    // }else{
                                    //
                                    //     let fields = selectedVariant.fields;
                                    //
                                    //     fields.forEach((v, k) => {
                                    //         let valueInPrice = 0,
                                    //             value = '';
                                    //
                                    //         switch (v.type) {
                                    //             case "name":
                                    //                 value = item.name;
                                    //                 break;
                                    //             case "cost":
                                    //                 value = this.numberFormat(item.cost);
                                    //                 break;
                                    //             case "count":
                                    //                 value = '1';
                                    //                 break;
                                    //             case "price":
                                    //                 value = this.numberFormat(item.cost);
                                    //                 break;
                                    //             case "discount":
                                    //                 value = '0';
                                    //                 break;
                                    //             case "good-coll":
                                    //                 value = v.value;
                                    //                 valueInPrice = 1;
                                    //                 break;
                                    //         }
                                    //
                                    //         values.push({
                                    //             'db-id': 0,
                                    //             index: k,
                                    //             type: v.type,
                                    //             value: value,
                                    //             value_in_price: valueInPrice,
                                    //             variant_product_id: item.id
                                    //         });
                                    //     });
                                    // }

                                    selectedVariant.products.push({
                                        'description': item.description,
                                        'fake_product_id': fakeProductId,
                                        'group': 0,
                                        'db-id': 0,
                                        'id': 0,
                                        'image': item.file[0],
                                        'offer_id': this.offerId,
                                        'product_id': productId,
                                        'index': index + 1,
                                        'values': values,
                                        'variant_id': selectedVariant.id
                                    });

                                    selectedVariant['db-id'] = selectedVariant.id;

                                    for (let i = 0; i < selectedVariant.products.length; i++) {
                                        if (!selectedVariant.products[i]['db-id']) {
                                            selectedVariant.products[i]['db-id'] = selectedVariant.products[i].id;
                                        }
                                    }

                                    this.calculatePositionsPrices(selectedVariant.id);
                                    this.showEditOfferBtn = true;
                                }
                            });

                        });
                    });
                })
                .catch((error) => {
                    // window.ajaxError(error);
                });
        },

        switchTab(index) {
            this.selectedVariantIndex = index;
            this.rotateArrow = 1;
            this.arrowIcon = "arrow-up";
            localStorage.setItem("hideShowKP10", true);
        },

        cancelVariantSelection() {
            window.axios
                .post("https://" + this.kp10Host + "/api/editor/" + this.offerId + "/cancel-variant-selection")
                .then(response => {

                    this.loadOffer();
                    this.varSelected = 0;
                    this.noClick = false;

                })
                .catch(error => {
                    // window.ajaxError(error);
                });
        },

        cancelEdit: function () {
            let copy = JSON.parse(JSON.stringify(this._beforeEditingCache));

            this.variants = copy;
            this.showEditOfferBtn = false;

            // this._beforeEditingCache = Object.assign({}, this.variants);
        },

        saveForm() {
            let copy = JSON.parse(JSON.stringify(this.variants));
            var result = [];

            copy.forEach(function (params) {

                let fields = [],
                    products = [],
                    values = [],
                    variant = [];
                var variant_id = params.id;

                params.fields.forEach(function (field) {
                    let name = field.name,
                        fieldId = field.id;

                    fields.push({
                        name: name,
                        'db-id': fieldId
                    });
                });

                params.products.forEach(function (product) {
                    var values = [];

                    if (product.variant_id == variant_id) {
                        product.values.forEach(function (val) {

                            values.push({
                                'db-id': val.id || 0,
                                value: val.value,
                                type: val.type,
                                valueInPrice: val.value_in_price,
                                index: val.index
                            });

                        });

                        products.push({
                            'db-id': product.id,
                            group: product.group,
                            index: product.index,
                            description: product.description,
                            image: product.image,
                            values: values,
                            fakeProductId: product.fake_product_id,
                        });
                    }
                });

                variant = {
                    'id': params.id,
                    'name': params.name,
                    'price': params.price,
                    'fields': fields,
                    'products': products
                };

                result.push(variant);
            });

            // send
            window.axios.defaults.headers.common["Accept"] = "application/json";
            window.axios.defaults.headers.common["Authorization"] = "Bearer " + this.apiKey;
            window.axios
                .post("https://" + this.kp10Host + "/api/editor/" + this.offerId + "/store", {settings: {variants: result}})
                .then(response => {
                    let message = response.data.message;

                    $("#kp10-popup").find('.modal-body').html('<div>' + message + '</div>');
                    $("#kp10-popup").modal("show");

                    this.loadOffer();
                    this.showEditOfferBtn = false;

                    setTimeout(function () {
                        $("#kp10-popup").modal("hide");
                    }, 1000);

                })
                .catch(error => {
                    // window.ajaxError(error);
                });
        },

        // numberFormat(str) {
        //     str = str + ""; //convert to string
        //     str = str.replace(/(\.(.*))/g, "");
        //     let arr = str.split(""),
        //         str_temp = "";
        //     if (str.length > 3) {
        //         for (let i = arr.length - 1, j = 1; i >= 0; i--, j++) {
        //             str_temp = arr[i] + str_temp;
        //             if (j % 3 == 0) {
        //                 str_temp = " " + str_temp;
        //             }
        //         }
        //         return str_temp;
        //     } else {
        //         return str;
        //     }
        // },
        //
        // parseF(str) {
        //     if (str) {
        //         let parse = str && str.replace(/[^0-9.]/g, "");
        //         if (parse) {
        //             return parseFloat(parse);
        //         }
        //     }
        //     return 0;
        // },

        numberFormat(str) {
            str = str + '';//convert to string
            let after = str.split('.');
            after = after[1] ? '.' + after[1] : '';
            str = str.replace(/(\.(.*))/g, '');
            let arr = str.split(''),
                str_temp = '';
            if (str.length > 3) {
                for (let i = arr.length - 1, j = 1; i >= 0; i-- , j++) {
                    str_temp = arr[i] + str_temp;
                    if (j % 3 == 0) {
                        str_temp = ' ' + str_temp;
                    }
                }
                return str_temp + after;
            } else {
                return str + after;
            }
        },

        parseF(str) {
            if (str) {
                str = str + "";
                let parse = str.replace(/[^0-9.]/g, ''); //todo AIM походу сюда надо добавить запятую
                if (parse) {
                    return str.indexOf('.') === -1 ? parseFloat(parse) : parseFloat(parse).toFixed(2);
                }
            }
            return 0;
        },

        calculatePositionsPrices(variantId) {
            let special_discounts = [],
                count = 0,
                discount = 0,
                totalCost = 0,
                totalCostWithoutDiscount = 0,
                price = 0,
                groupId = 0,
                groupCosts = [],
                goodsValue = 1,
                discountEnabledGlobal = false,
                specialDiscount = 0,
                discountEnabled = false;

            this.variants.forEach(variant => {

                if (variant.id == variantId) {

                    this.offerId = variant.offer_id;

                    special_discounts = variant.special_discounts;

                    //Sort
                    variant.products.sort(function (a, b) {
                        return a.index - b.index;
                    });

                    variant.products.forEach(product => {

                        //Sort values
                        product.values && product.values.sort(function (a, b) {
                            return a.index - b.index;
                        });

                        if (product.group) {
                            groupId = product.id;
                            return;
                        }

                        let price = 0,
                            count = 0,
                            discount = 0,
                            goodsValue = 1;
                        // cost = 0;

                        product.values.forEach(element => {
                            switch (element.type) {
                                case "price":
                                    price = this.parseF(element.value);
                                    break;
                                case "count":
                                    count = this.parseF(element.value);
                                    break;
                                case "discount":
                                    discount = this.parseF(element.value);
                                    discountEnabled = discountEnabledGlobal = true;
                                    break;
                                case "good-coll":
                                    if (element.value_in_price) {
                                        goodsValue *= this.parseF(element.value);
                                    }
                                    break;
                                case "cost":
                                    // cost = this.parseF(element.value);
                                    break;
                            }
                        });

                        let cost = count * price * goodsValue;

                        if (discountEnabled && discount) {

                            if(discount > 100){
                                discount = 100;
                            }

                            //add discount
                            cost -= cost * discount * 0.01;
                        }

                        // price-with-discount
                        let price_with_discount = price - price * discount * 0.01;

                        //Update product cost, price-with-discoun
                        product.values.forEach(element => {
                            switch (element.type) {
                                case "cost":
                                    element.value = this.parseF(cost);
                                    break;
                                case "price-with-discount":
                                    element.value = this.parseF(price_with_discount);
                                    break;
                            }
                        });

                        //Calculate tax
                        if (variant.tax == 1) {
                            let totalSum = variant.price;

                            this.tax = this.numberFormat(Math.round(totalSum / 1.18 * 0.18));
                        }

                        //calculate total cost
                        totalCost += cost;
                        //calculate total cost without discount
                        // TODO: перевірити чи коректно працює спеціальна знижка при додаванні стовпчика
                        if (discountEnabled || special_discounts.length) {
                            totalCostWithoutDiscount += price * count * goodsValue;
                            this.kpDiscount[variant.id] = this.parseF(totalCostWithoutDiscount);
                            if (!this.kpDiscount[variant.id]) {
                                this.kpDiscount[variant.id] = "0";
                            }
                        }

                        //Group cost calculation
                        //find index in array
                        let group = groupCosts.filter(a => a.groupId == groupId);

                        if (group.length) {
                            group[0].cost += cost;
                        } else {
                            //for new one
                            //Using for groups
                            groupCosts.push({
                                groupId: groupId,
                                cost: cost
                            });
                        }
                    });
                }
            });

            //Special discount
            if (special_discounts.length) {
                special_discounts.forEach(element => {
                    specialDiscount += this.parseF(element.value);
                });

                //ad discount to final price
                totalCost -= specialDiscount;
            }

            this.variants.forEach((variant) => {

                if (variantId == variant.id) {
                    variant.price = this.parseF(totalCost);
                    variant.products.forEach((product) => {
                        if (product.group == 1) {
                            groupCosts.forEach((item) => {
                                if (item.groupId == product.id) {
                                    this.kpGroupCost[product.id] = item.cost;
                                }
                            });
                        }
                    });
                }
            });
        },

        editContent(type) {

            if(!this.varSelected){
                if (type == "name") {
                    return 'true';
                } else if (type == "count") {
                    return 'true';
                } else if (type == "price") {
                    return 'true';
                } else if (type == "discount") {
                    return 'true';
                } else if (type == "good-coll") {
                    return 'true';
                }
            }

            return 'false';
        },

        isNumber: function (evt, type) {
            if (type != 'name') {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                    evt.preventDefault();
                } else {
                    return true;
                }
            }
        },

        //On change item value
        changeItemValue: _.debounce(function (item, value, variantId) {
            value.value = item;
            this.calculatePositionsPrices(variantId);
            this.showEditOfferBtn = true;

        }, 500),

        //On change item name
        changeItemName: _.debounce(function (e) {
            // let $this = $(e.target);

            this.showEditOfferBtn = true;
        }, 500),

        hideShowKP10() {
            if (this.rotateArrow) {
                localStorage.setItem("hideShowKP10", false);
                this.arrowIcon = "arrow-down";
            } else {
                localStorage.setItem("hideShowKP10", true);
                this.arrowIcon = "arrow-up";
            }

            this.rotateArrow = !this.rotateArrow;
        },

        printValue(value) {
            let result = value.value;
            switch (value.type) {
                case "cost":
                    result = this.numberFormat(value.value);
                    if (result.length == 0) {
                        result = '0';
                    }
                    break;
            }
            return result;
        },

        //Copy to clipboard
        kpCopyLink() {
            const el = document.createElement('textarea');
            // el.value = 'https://' + this.kp10Host + '/' + this.offerUrl;
            el.value = 'https://' + this.kp10Host + '/' + this.offerUrl;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);

            this.kpTooltipCopy = 'Ссылка скопирована';
            setTimeout(() => {
                this.kpTooltipCopy = 'Скопировать ссылку';
            }, 1000);
        },

        showDescription(product) {
            this.productImg = product.image;
            this.productDesc = product.description;
            this.productGoodColl = [];

            product.values.forEach(element => {
                switch (element.type) {
                    case "name":
                        this.productName = element.value;
                        break;
                    case "cost":
                        this.productCost = element.value;
                        break;
                    case "count":
                        this.productCount = element.value;
                        break;
                    case "price":
                        this.productPrice = element.value;
                        break;
                    case "discount":
                        this.productDiscount = element.value;
                        break;
                    case "good-coll":
                        this.productGoodColl.push({
                            'name': this.variants[this.selectedVariantIndex].fields[element.index].name,
                            'value': element.value
                        });
                        break;
                }
            });

            $("#kp10Description").modal();
        },

        imageSearch(state) {

            // show/hide create product button
            // if (state.disabled) {
            //   this.showCreateProduct = true;
            // } else {
            //   this.showCreateProduct = false;
            // }
            // <svg-icon :icon="currency"></svg-icon>
            if (!state.img) {
                return;
            }
            return $('<span><img class="kp10-widget-product-thumbnail" src="https://' + this.kp10Host + state.img + '" class="img-flag">' + state.text + ' ' + this.numberFormat(state.cost) + '<svg-icon :icon="currency"></svg-icon>' + '</span>');
        },

        createProduct() {
            let variant = this.variants[this.selectedVariantIndex],
                products = variant.products,
                cnt = products.length,
                fields = variant.fields;

            var values = [];

            // for (var i = 0; i < cnt; i++) {
            //   let props = products[i];

            //   props['db-id'] = 0;

            //   if (props.group == 0) {
            //     let val = props.values;
            //     var value = '';

            //     val.forEach( (v, k) => {
            //       let valueInPrice = 0;

            //       switch (v.type) {
            //         case "name":
            //           value = 'Введите название';
            //           break;
            //         case "cost":
            //           value = '0';
            //           break;
            //         case "count":
            //           value = '1';
            //           break;
            //         case "price":
            //           value = '0';
            //           break;
            //         case "discount":
            //           value = '0';
            //           break;
            //         case "good-coll":
            //           value = v.value;
            //           valueInPrice = 1;
            //           break;
            //       }

            //       values.push({
            //         'db-id' : 0,
            //         index: k,
            //         type: v.type,
            //         value: value,
            //         value_in_price: valueInPrice,
            //         variant_product_id: '0'
            //       });

            //     });
            //     break;
            //   }
            // }

            for (var i = 0; i < fields.length; i++) {
                //let props = products[i];

                //props['db-id'] = 0;

                // if (props.group == 0) {
                let val = fields[i];
                var value = '';

                // $(val).forEach( (v, k) => {

                let valueInPrice = 0;

                switch (val.type) {
                    case "name":
                        value = 'Введите название';
                        break;
                    case "cost":
                        value = '0';
                        break;
                    case "count":
                        value = '1';
                        break;
                    case "price":
                        value = '0';
                        break;
                    case "discount":
                        value = '0';
                        break;
                    case "good-coll":
                        // value = v.value;
                        value = '0';
                        valueInPrice = 1;
                        break;
                }

                values.push({
                    'db-id': 0,
                    index: i,
                    type: val.type,
                    value: value,
                    value_in_price: valueInPrice,
                    variant_product_id: '0'
                });

                // });
                // break;
                // }
            }

            var productId = '0',
                d = new Date(),
                cProductId = d.getTime(),
                type = variant.type,
                fakeProductId = '#modal-product-' + (cProductId + productId) + '-' + type;

            products.push({
                'description': '',
                'fake_product_id': fakeProductId,
                'group': 0,
                'db-id': 0,
                'id': 0,
                'image': this.defaultProductImg,
                'offer_id': this.offerId,
                'product_id': '0',
                'index': cnt + 1,
                'values': values,
                'variant_id': variant.id
            });
        },

        //Return class for currency
        offerCurrency(offer) {
            let currency = {
                'icon': true
            };

            if (offer.currency && offer.currency.data && offer.currency.data.system) {

                let charCode = offer.currency.data.system.char_code.toLowerCase();
                currency = charCode;
            } else {
                //Default
                currency = 'rub';
            }

            return currency;
        },

        addSelect() {
            this.showCreateForm = true;
            window.axios.defaults.headers.common["Accept"] = "application/json";
            window.axios.defaults.headers.common["Authorization"] = "Bearer " + this.apiKey;

            window.axios.get("https://" + this.kp10Host + "/api/offers/list/json")
                .then(response => {
                    this.template = response.data.data.data;
                    this.$nextTick(() => {
                        $('.select-template').select2({
                            placeholder: "Выберите шаблон",
                            width: '100%',
                            // minimumInputLength: 3,
                            ajax: {
                                delay: 250,
                                headers: {
                                    "Accept": "application/json",
                                    "Authorization": "Bearer " + this.apiKey
                                },
                                url: "https://" + this.kp10Host + "/api/offers/list/json",
                                data: function (params) {
                                    let query = {
                                        search: params.term,
                                        page: params.page || 1
                                    };
                                    // Query parameters will be ?search=[term]&page=[page]
                                    return query;
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    // Tranforms the top-level key of the response object from 'items' to 'results'
                                    return {
                                        results: $.map(data.data.data, function (item) {
                                            if (item && typeof item == 'object') {
                                                return {
                                                    id: item.id,
                                                    text: item.offer_name + ' ' + item.template.version
                                                };
                                            }
                                        }),
                                        pagination: {
                                            more: params.page * 10 < data.total
                                        }
                                    };
                                }
                            }
                        }).on("change", e => {
                            //update data in parent component
                            this.offerId = $(e.target).val();

                            if (this.offerId != '') {
                                this.noClick = false;
                                this.disabledBtn = false;

                                this.showKpBtnAndName = true;
                            }
                        });
                        this.selectTemplateShow = true;
                    });
                })
                .catch(error => {
                });
        },

        selectTemplate(index) {
            this.offerId = index.target.value;
        },

        createOffer() {
            if (this.offerId == '') {
                return;
            }

            if (this.offerName == '') {
                this.isErrorName = true;
                return;
            }

            this.preloader = true;
            this.creatingOfferBlock = true;

            window.axios
                .put("https://" + this.kp10Host + "/api/offers/" + this.offerId + "/copy", {name: this.offerName})
                .then(response => {
                    this.dataOfferId = response.data.offer.id;
                    window.axios
                        .put(
                            "https://" + this.kp10Host + "/api/bitrix24/deals/" + this.dealId + "/offer", {id: response.data.offer.id}
                        )
                        .then(response => {
                            this.loadOffer();
                        })
                        .catch(error => {
                            this.errorMessage = error.response.data.message;
                        });
                })
                .catch(error => {
                    this.errorMessage = error.response.data.message;
                });
        },

        openEditor() {
            this.loadOfferShow = true;
            this.showCreateForm = false;
            $("#kp10-popup").modal("hide");
        },

        kpNameBlockFocus() {
            this.isErrorName = false;
        }
    }
});