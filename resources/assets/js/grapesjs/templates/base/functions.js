module.exports = {

    editor: '',
    wrapper: '',
    config: '',

    init: function (editor, config) {
        this.editor = editor;
        this.wrapper = editor.DomComponents.getWrapper();
        this.config = config;
    },
    //Check if products stored in DB
    checkConfig: function () {
        if (this.config && this.config.offer) {
            if (this.config.offer.variants && this.config.offer.system != 1) {
                if (!this.editor.DomComponents.getWrapper().view.$el.find('#cp-settings').length) {
                    console.warn('Application does not initialized!');
                    //Enable editor
                    editor.configuring = false;
                    return;
                }

                let cpSettingsModel = this.editor.DomComponents.getWrapper().view.$el.find('#cp-settings').data('model'),
                    cpSettings = cpSettingsModel.get('cp-settings');

                //Set db-id for variant data
                let update = this.updateVariantsProducts();

                let offerUpdate = new Date(this.config.offer.updated_at),
                    offerCreate = new Date(this.config.offer.created_at),
                    selected = false,
                    updateTemplate = false,
                    updateVariantProducts = false;
                // params = {};

                for (let v = 0; v < this.config.offer.variants.length; v++) {
                    const variant = this.config.offer.variants[v];

                    let $navTab = this.wrapper.view.$el.find('.nav-tabs a[data-db-id="' + variant.id + '"]'),
                        $activeTab = $navTab.length && this.wrapper.view.$el.find('.tab-pane[role="tabpanel"]' + $navTab.attr('href'));

                    //If variant id not equal stored in db
                    //Fo first offer open
                    if (!$navTab.length) {
                        update = updateVariantProducts = true;
                        // params.updateId = true;
                    }

                    if (variant.selected) {
                        selected = true;
                    }

                    //Check if variant update date greater than offer update date. Using for update template html
                    if (!updateTemplate && new Date(variant.updated_at) > offerUpdate) {
                        updateTemplate = true;
                    }

                    //If variant fields empty
                    if (variant.fields) {
                        //Check for need update cpSettings
                        if (!variant.fields.length) {
                            update = true;
                        }

                        //Check variant field update date
                        !updateTemplate && variant.fields.forEach((field) => {
                            if (new Date(field.updated_at) > offerUpdate) {
                                updateTemplate = true;
                            }
                        });
                    }

                    if (variant.products) {

                        //Check variant product update date
                        for (let p = 0; p < this.config.offer.variants[v].products.length; p++) {
                            const product = this.config.offer.variants[v].products[p];

                            if (!updateTemplate && new Date(product.updated_at) > offerUpdate) {
                                updateTemplate = true;
                            }

                            let $hiddenProduct = '';

                            if (!product.group) {
                                //Product cannot exist without fake_product_id
                                if (!product.fake_product_id || product.fake_product_id == '' || product.fake_product_id == 0) {
                                    console.error('Product broken', product);
                                    //Mark product for remove from DB
                                    this.updateCpSettingsVariantProducts({ delete: { product: { 'db-id': product.id } } });
                                    //
                                    updateTemplate = true;
                                    return;
                                }

                                //Fancybox
                                $hiddenProduct = this.wrapper.view.$el.children(product.fake_product_id);

                                //Fancybox
                                if ($hiddenProduct.length) {
                                    this.updateContent($hiddenProduct.find('.modal-product__info > div').data('model'), product.description);
                                }
                            }

                            //When somthing happens and in db exist fake product or group
                            if (!product.delete && !product.values.length) {
                                console.error('Product broken', product);
                                //Mark product for remove from DB
                                this.updateCpSettingsVariantProducts({ delete: { product: { 'db-id': product.id } } });
                                //
                                updateTemplate = true;
                                continue;
                            }

                            if (product.values) {
                                //For new products
                                let newProduct = {};
                                //If product not found
                                //Group also pass in it
                                if ($activeTab && !product.group && !this.wrapper.view.$el.children(product.fake_product_id).length) {
                                    //If product values exist
                                    if (product.values && product.values.length) {
                                        newProduct = {
                                            $activeTab: $activeTab,
                                            id: product.product_id,
                                            fakeProductId: product.fake_product_id,
                                            name: '',
                                            count: 0,
                                            file: product.image,
                                            cost: 0,
                                            description: product.description,
                                            fake: product.product_id ? false : true,
                                            goodsColls: [],
                                            disableCalculate: true
                                        };
                                    }
                                }
                                let newProductLength = Object.keys(newProduct).length;

                                //Store index for good coll
                                let goodCollIndex = 1;

                                if (product.values.length) {

                                    for (let vindex = 0; vindex < product.values.length; vindex++) {
                                        const value = product.values[vindex];

                                        if (new Date(value.updated_at) > offerUpdate) {
                                            updateTemplate = true;
                                        }
                                        let $el = this.wrapper.view.$el.find('.row [data-db-id="' + value.id + '"]');

                                        if (!product.group) {

                                            let $list = $el.length && $el.closest('[role="tabpanel"]').find('.tab-content__list a[data-src="' + product.fake_product_id + '"]');

                                            switch (value.type) {
                                                case 'name':
                                                    if ($el.length) {
                                                        try {
                                                            this.updateContent($el.find('.row div:nth-child(2) span').data('model'), value.value);
                                                            //List
                                                            this.updateContent($list.find('.kp10-cell-name > span').data('model'), value.value);
                                                            //Fancybox
                                                            this.updateContent($hiddenProduct.find('.kp10-cell-name > span').data('model'), value.value);
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                    }
                                                    if (newProduct && newProductLength) {
                                                        newProduct.name = value.value;
                                                    }
                                                    break;
                                                case 'price':
                                                    let price = this.parseF(value.value);
                                                    if ($el.length) {
                                                        try {
                                                            this.updateContent($el.find('.kp10-cell-price').data('model'), price);
                                                            //List
                                                            this.updateContent($list.find('.kp10-cell-price').data('model'), price);
                                                            //Fancybox
                                                            this.updateContent($hiddenProduct.find('.kp10-cell-price').data('model'), price);
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                    }
                                                    if (newProduct && newProductLength) {
                                                        newProduct.cost = value.value;
                                                    }
                                                    break;
                                                case 'count':
                                                    let count = this.parseF(value.value);
                                                    if ($el.length) {
                                                        try {
                                                            this.updateContent($el.data('model'), count);
                                                            //List
                                                            this.updateContent($list.find('.kp10-cell-count').data('model'), count);
                                                            //Fancybox
                                                            this.updateContent($hiddenProduct.find('.kp10-cell-count').data('model'), count);
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                    }
                                                    if (newProduct && newProductLength) {
                                                        newProduct.count = count;
                                                    }
                                                    break;
                                                case 'discount':
                                                    let discount = this.parseF(value.value);
                                                    if ($el.length) {
                                                        try {
                                                            this.updateContent($el.data('model'), discount);
                                                            //Fancybox
                                                            this.updateContent($hiddenProduct.find('.kp10-discount').data('model'), discount);
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                    }
                                                    if (newProduct && newProductLength) {
                                                        newProduct.discount = discount;
                                                    }
                                                    break;
                                                case 'good-coll':
                                                    if ($el.length) {
                                                        try {
                                                            this.updateContent($el.data('model'), value.value);

                                                            this.updateContent(
                                                                $hiddenProduct.find('.modal-product__content > div.kp10-goods-coll > div:nth-child(' + goodCollIndex + ') span').data('model'),
                                                                value.value);
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                        //Incement index
                                                        goodCollIndex += 1;
                                                    }

                                                    if (newProduct && newProductLength) {
                                                        newProduct.goodsColls.push({
                                                            index: value.index, //Fix for template
                                                            value: value.value
                                                        });
                                                    }
                                                    break;
                                                case 'cost':
                                                    if ($el.length) {
                                                        try {
                                                            let cost = this.parseF(value.value);
                                                            this.updateContent($el.find('.kp10-cell-cost').data('model'), this.numberFormat(cost));
                                                            //Fancybox
                                                            this.updateContent($hiddenProduct.find('.kp10-cell-cost').data('model'), this.numberFormat(cost));
                                                        } catch (e) {
                                                            console.error(e);
                                                        }
                                                    }
                                                    break;
                                            }
                                        } else {
                                            $el.length && this.updateContent($el.children().eq(0).data('model'), value.value);
                                        }

                                    }
                                }

                                //If product not found
                                if (newProduct && newProductLength) {
                                    //Add it for current variant
                                    this.addProduct(newProduct);
                                    updateVariantProducts = true;
                                }
                            }
                        }
                    }

                    if (update || updateTemplate) {
                        this.calculatePositionsPrices(variant.id);
                    }
                }

                //If product was added from widget
                if (updateVariantProducts) {
                    //Set db-id for variant data
                    update = this.updateVariantsProducts();
                }

                //Check if employee in template the same as in DB
                if (this.config.offer.employee && this.config.offer.employee.user_id) {
                    //Find employee on the page
                    let $userId = this.editor.DomComponents.getWrapper().view.$el.find('div.person.message__person > .person-container'),
                        userId = $userId.length ? $userId.data('id') : 0;

                    if (userId != this.config.offer.employee.user_id) {

                        this.editor.BlockManager.getAll().forEach((block) => {
                            //Find same user
                            if (block.get('id') == 'employee-' + this.config.offer.employee.user_id) {
                                //Update employee
                                editor.addComponents(block.get('content'));
                            }
                        });
                    }
                }

                // Integration fields
                if (this.config.integration && this.config.integration.fields) {
                    //Megaplan
                    if (this.config.integration.system_crm_id === 1) {
                        //Each field
                        if (this.config.offer.megaplan_deal && this.config.offer.megaplan_deal.values) {
                            this.config.offer.megaplan_deal.values.forEach((field) => {

                                let fieldValue = field.megaplan_field_values;
                                //
                                if (fieldValue && field.field) { //&& new Date(field.updated_at) > offerUpdate

                                    let $field = this.wrapper.view.$el.find('.add-order[data-field-id="' + field.field.field_id + '"]');
                                    //Find field on the page
                                    if ($field.length) {

                                        //Bool
                                        if (field.field.content_type == 'BoolField') {
                                            fieldValue = fieldValue == 1 ? 'Да' : 'Нет';
                                        }
                                        //Дата
                                        if (field.field.content_type == 'DateField' || field.field.content_type == 'DateTimeField') {
                                            fieldValue = this.formatIntegrationFieldDate(field);
                                        }
                                        //Update field content
                                        this.updateContent($field.children().eq(1).data('model'), fieldValue);

                                        $field.children().eq(1).data('model').get('traits').each((trait) => {
                                            let name = trait.get('name');

                                            //Флаг
                                            if (field.field.content_type == 'BoolField' && name == 'integration-field-' + field.field.field_id) {
                                                trait.set('checked', fieldValue == 'Да' ? 1 : 0);
                                            }
                                            //Список
                                            if (field.field.content_type == 'EnumField' && name == 'integration-field-' + field.field.field_id) {
                                                trait.set('selected', fieldValue);
                                            }
                                            //Дата
                                            if ((field.field.content_type == 'DateField' || field.field.content_type == 'DateTimeField') && name == 'integration-field-' + field.field.field_id) {
                                                trait.set('value', fieldValue);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                    //amoCRM
                    if (this.config.integration.system_crm_id === 2) {
                        //Each field
                        if (this.config.offer.amocrm_deal && this.config.offer.amocrm_deal.data && this.config.offer.amocrm_deal.data.fields) {
                            this.config.offer.amocrm_deal.data.fields.forEach((field) => {
                                let value = field.values && field.values[0],
                                    dataField = this.config.integration.fields.find((el) => {
                                        return el.amocrm_field_id == field.amocrm_field_id;
                                    });
                                //                                
                                if (value) { //&& new Date(value.updated_at) > offerUpdate
                                    let fieldValue = value.amocrm_field_value;

                                    let $field = this.wrapper.view.$el.find('.add-order[data-field-id="' + field.amocrm_field_id + '"]');
                                    //Find field on the page
                                    if ($field.length) {

                                        //Флаг
                                        if (dataField.amocrm_field_type_id == 3) {
                                            fieldValue = fieldValue == 1 ? 'Да' : 'Нет';
                                        }
                                        //Дата | День рождения
                                        if ([6, 14].indexOf(dataField.amocrm_field_type_id) !== -1) {
                                            fieldValue = this.formatIntegrationFieldDate(value);
                                        }
                                        //Update field content
                                        this.updateContent($field.children().eq(1).data('model'), fieldValue);

                                        $field.children().eq(1).data('model').get('traits').each((trait) => {
                                            let name = trait.get('name');
                                            //Флаг
                                            if (name == 'integration-field-3') {
                                                trait.set('checked', fieldValue == 'Да' ? 'yes' : 'no');
                                            }
                                            //Список | Переключатель
                                            if (name == 'integration-field-4' || name == 'integration-field-10') {
                                                trait.set('selected', value.amocrm_field_enum_id);
                                            }
                                            //Дата
                                            if (name == 'integration-field-6' || name == 'integration-field-14') {
                                                trait.set('value', fieldValue);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                    //Bitrix24
                    if (this.config.integration.system_crm_id === 3) {
                        //Each field
                        if (this.config.offer.bitrix24_deal && this.config.offer.bitrix24_deal.data && this.config.offer.bitrix24_deal.data.fields) {
                            this.config.offer.bitrix24_deal.data.fields.forEach((field) => {
                                let value = field.values && field.values[0],
                                    dataField = this.config.integration.fields.find((el) => {
                                        return el.bitrix24_field_id == field.bitrix24_field_id;
                                    });
                                //
                                if (value) { //&& new Date(value.updated_at) > offerUpdate
                                    let fieldValue = value.bitrix24_field_value;

                                    let $field = this.wrapper.view.$el.find('.add-order[data-field-id="' + field.bitrix24_field_id + '"]');
                                    //Find field on the page
                                    if ($field.length) {

                                        //boolean
                                        if (dataField.bitrix24_field_type_id == 'boolean') {
                                            fieldValue = fieldValue == 1 ? 'Да' : 'Нет';
                                        }
                                        //date
                                        if (dataField.bitrix24_field_type_id == 'date') {
                                            fieldValue = this.formatIntegrationFieldDate(value);
                                        }
                                        //money
                                        if (dataField.bitrix24_field_type_id == 'money') {
                                            //Get value without currency
                                            fieldValue = fieldValue.split('|')[0];
                                        }
                                        //Update field content
                                        this.updateContent($field.children().eq(1).data('model'), fieldValue);

                                        $field.children().eq(1).data('model').get('traits').each((trait) => {
                                            let name = trait.get('name');
                                            //boolean
                                            if (name == 'integration-field-boolean') {
                                                trait.set('checked', fieldValue == 'Да' ? 'yes' : 'no');
                                            }
                                            //enumeration
                                            if (name == 'integration-field-enumeration') {
                                                trait.set('selected', value.bitrix24_field_enum_id);
                                            }
                                            //date
                                            if (name == 'integration-field-date') {
                                                trait.set('value', fieldValue);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                }

                //Update client
                if (this.config.offer.client_relation && this.config.offer.client_relation.client) {
                    let client = this.config.offer.client_relation.client;
                    this.updateCpSettings({ client: { id: client.id, name: client.displayName } });
                    //Update client on the page
                    this.updateClient();
                }
                //Update contact person
                if (this.config.offer.contact_person_relation && this.config.offer.contact_person_relation.client) {
                    let contact_person = this.config.offer.contact_person_relation.client;
                    this.updateCpSettings({ contactPerson: { id: contact_person.id, name: contact_person.displayName } });
                    //Update contact person on the page
                    this.updateContactPerson();
                }

                //If need update variant field names and products
                if (!selected && (update || updateTemplate)) {

                    //If product was added from widget
                    this.updateCpSettingsVariantProducts();

                    //Update currency
                    if (this.config.offer.currency) {
                        let currencySeetings = this.getCpSettings('currency'),
                            currency = this.config.currencies.find((item) => {
                                return this.config.offer.currency.currency_id ? //If default currency not 0
                                    item.id == this.config.offer.currency.currency_id : //By currency id
                                    item.basic == 1 //Basic currency
                            });

                        //If currency different
                        if (!currencySeetings || (currencySeetings && currencySeetings.id != this.config.offer.currency.currency_id)) {
                            //Update sign
                            this.changeCurrency(currency);
                        }

                        this.updateCpSettings({ currency: currency });
                    }

                    //Add custom settings
                    this.editor.StorageManager.get('remote').set('params', { settings: cpSettings });
                    //Update html. Run editor.store()
                    this.editor.saveHtml = true;
                    //Save 
                    this.editor.store();

                    if (updateTemplate) {
                        //No nee to check after save | storage:end
                        this.editor.checkConfig = false;
                    }
                } else {
                    //Enable editor
                    editor.configuring = false;
                }
            } else {
                //Enable editor
                editor.configuring = false;
            }
        }
    },

    /**
     * Calculate position discount and price for active tab positions
     */
    calculatePositionsPrices: function (variantId) {
        //find active model
        let _this = this,
            wrapper = this.wrapper,
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $navTabActive = wrapper.view.$el.find('ul.nav-tabs li.active'),
            $headerPrice = $navTabActive.find('.kp10-header-price');

        if (variantId) {
            $navTabActive = wrapper.view.$el.find('.nav-tabs a[data-db-id="' + variantId + '"]');
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane' + $navTabActive.attr('href'));
            $headerPrice = $navTabActive.find('.kp10-header-price');
        }

        //Fix after for the first time
        if (!$activeTab.length) {
            return;
        }

        let positionsLength = $activeTab.find('.tab-pane-inner__menu-row').length,
            positions = positionsLength ? $activeTab.find('.tab-pane-inner__menu-row').data('model') : [],
            countIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-count').index(),
            priceIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price').index(),
            discountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index(),
            priceWithDiscountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price-with-discount').index(),
            costIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-cost').index(),
            $specialDiscountRow = $activeTab.find('.kp10-row-special-discount'),

            discountEnabled = $activeTab.find('.kp10-cell-discount').length,
            specialDiscountEnabled = $specialDiscountRow.length,
            totalCost = 0, //total cost
            totalCostWithoutDiscount = 0,

            groupIndex = 0, //using for update group price
            groupCosts = [];

        //Change value for discount on each position
        if (positionsLength) {
            positions.collection.each(function (m, i) {
                //group index
                if (m.view.$el.find('.pane-title').length) {
                    groupIndex = i;
                }

                if (i !== 0 && !m.view.$el.find('.pane-title').length) {

                    let mComp = m.get('components'),
                        count = 0,
                        discount = 0,
                        price = 0,
                        goodsValue = 1;

                    //walk on colls
                    $.each(mComp.models, function () {
                        //check each coll
                        switch (this.view.$el.index()) {
                            case priceIndex:
                                price += _this.parseF(this.view.$el.find('.kp10-cell-price').text());
                                break;
                            case countIndex:
                                count = _this.parseF(this.view.el.innerHTML);
                                break;
                            case discountIndex:
                                discount = _this.parseF(this.view.el.innerHTML);
                                break;
                            default:
                                if (this.get('type') === 'add-goods-coll' && this.get('valueInPrice')) {
                                    //custom coll. Check if use value in Price
                                    goodsValue *= _this.parseF(this.view.el.innerHTML);
                                }
                                break;
                        }
                    });

                    let cost = (count * price * goodsValue);

                    if (discountEnabled && discount) {
                        //add discount
                        cost -= cost * discount * 0.01;
                    }

                    //update cost
                    if (mComp.at(costIndex)) {
                        mComp.at(costIndex).view.$el.find('.kp10-cell-cost').data('model').set('content', _this.numberFormat(_this.parseF(cost)));
                    } else {
                        console.warn('Model at index ' + costIndex + ' undefined');
                    }
                    //update price with discount
                    if (discountEnabled) {
                        mComp.at(priceWithDiscountIndex).set('content', _this.numberFormat(_this.parseF(price - (price * discount * 0.01))));
                    }

                    //calculate total cost
                    totalCost += cost;
                    //calculate total cost without discount
                    if (discountEnabled || specialDiscountEnabled) {
                        totalCostWithoutDiscount += price * count * goodsValue;
                    }
                    //update cost in hidden offer block
                    let relativeId = m.view.$el.data('src'); //get offer id
                    if (relativeId) {
                        _this.updateProductModel({
                            relativeId: relativeId,
                            cost: _this.numberFormat(cost)
                        });
                    }

                    //Group cost calculation
                    //find index in array
                    let existedIndex = groupCosts.filter(a => a.groupIndex == groupIndex);

                    if (existedIndex.length) {
                        existedIndex[0].cost += cost;
                    } else { //for new one
                        //Using for groups
                        groupCosts.push({
                            groupIndex: groupIndex,
                            cost: cost
                        });
                    }
                }
            });
        }

        //special discount
        if (specialDiscountEnabled) {
            let specialDiscountModel = $specialDiscountRow.data('model'),
                specialDiscount = 0;

            specialDiscountModel.collection.each(function (m, i) {
                specialDiscount += _this.parseF(m.view.$el.find('.kp10-special-discount-value').text());
            });

            //ad discount to final price
            totalCost -= specialDiscount;
        }

        //update total cost
        $activeTab.find('.price-decoration .tab-pane-inner__price-cell_finally span').data('model').set('content', this.numberFormat(_this.parseF(totalCost)));

        //update total cost without discount
        if (discountEnabled || specialDiscountEnabled) {
            $activeTab.find('.kp10-pane-discount > div:last-child span').data('model').set('content', this.numberFormat(_this.parseF(totalCostWithoutDiscount)));
        }

        //update group cost
        if (positionsLength) {
            positions.collection.each((m, i) => {
                //group index
                if (m.view.$el.find('.pane-title').length) {
                    let data = groupCosts.filter(a => a.groupIndex == i),
                        cost = 0;
                    //exist value
                    if (data.length) {
                        cost = data[0].cost;
                    }

                    if (positions.collection.models[i].view.$el.find('.kp10-group-cost').length) {
                        positions.collection.models[i].view.$el.find('.kp10-group-cost').data('model').set('content', this.numberFormat(_this.parseF(cost)));
                    }
                }
            });
        }
        //update cost in tab
        $headerPrice.data('model').set('content', this.numberFormat(_this.parseF(totalCost)));
        //Calculate tax
        this.updateTax();
        //Updatea variant settings
        this.updateCpSettingsVariant({ variantId: variantId });
    },

    /**
     * get only number from str
     * @param val
     * @returns {Number}
     */
    parseI: function (str) {
        return str ? parseInt(str.replace(/[^0-9.]/g, '')) : 0;
    },

    parseF: function (str) {
        if (str) {
            str = str + "";
            let parse = str.replace(/[^0-9.]/g, '');
            if (parse) {
                return str.indexOf('.') === -1 ? parseFloat(parse) : parseFloat(parse).toFixed(2);
            }
        }
        return 0;
    },

    addSpecialDiscount: function () {
        let _this = this,
            $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            totalCostWithoutDiscount = 0,
            mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
            priceIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price').index(),
            countIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-count').index();

        //control discount add
        if ($activeTab.find('.price-decoration .kp10-pane-discount').length > 0) {
            //remove fake block
            // model.collection.remove(model);
            return;
        }

        mModel.collection.each(function (m, i) {
            let mComps = m.get('components');
            //exclude last
            if (m.get('type') !== 'add-discount' && !mComps.models[0].view.$el.hasClass('pane-title')) {
                if (!m.view.$el.hasClass('tab-pane-inner__menu-row-heading')) {
                    //calculate total cost without discount
                    totalCostWithoutDiscount += _this.parseF(mComps.at(countIndex).view.el.innerHTML) * _this.parseF(mComps.at(priceIndex).view.$el.find('.kp10-cell-price').text());
                }
            }
        });

        let discount =
            `<div class="kp10-pane-discount" 
                data-gjs-badgable="false" 
                data-gjs-draggable="false" 
                data-gjs-removable="false"
                data-gjs-copyable="false" 
                data-gjs-type="discount">
                <div class="tab-pane-inner__price-cell" 
                    data-gjs-badgable="false" 
                    data-gjs-draggable="false" 
                    data-gjs-stylable="false" 
                    data-gjs-copyable="false"
                    data-gjs-removable="false" 
                    data-gjs-editable="false" 
                    data-gjs-type="discount">Цена без скидки</div>
                <div class="tab-pane-inner__price-cell discount" 
                    data-gjs-badgable="false" 
                    data-gjs-draggable="false" 
                    data-gjs-stylable="false" 
                    data-gjs-copyable="false" 
                    data-gjs-removable="false" 
                    data-gjs-editable="false" 
                    data-gjs-type="discount">
                    <span data-gjs-badgable="false" 
                        data-gjs-draggable="false" 
                        data-gjs-stylable="false" 
                        data-gjs-copyable="false" 
                        data-gjs-removable="false"
                        data-gjs-editable="false" 
                        data-gjs-type="discount">${this.numberFormat(totalCostWithoutDiscount)}</span>
                    <i class="fa fa-rub currency" 
                        data-gjs-badgable="false" 
                        data-gjs-stylable="false" 
                        data-gjs-droppable="false" 
                        data-gjs-draggable="false" 
                        data-gjs-removable="false" 
                        data-gjs-copyable="false"
                        data-gjs-editable="false"></i>
                </div>
            </div>`;

        //add discount in bottom
        let $discount = $activeTab.find('.price-decoration .kp10-discount'),
            discountBlockModel = $discount.data('model');

        discountBlockModel.get('components').add(discount);

        //Open Component settings
        this.openComponentSettings();

        //trigger click on discount
        this.editor.select($discount.find('.kp10-pane-discount').data('model'));
        $discount.find('.kp10-pane-discount').addClass('gjs-comp-selected');

    },

    removeDiscount: function () {
        let _this = this,
            wrapper = this.wrapper,
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $specialDiscountRow = $activeTab.find('.kp10-row-special-discount'),
            cellNameWidthCurrent = this.parseI($activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').attr('class').match(/col-md-\d/g)[0]),
            discountCellLength = $activeTab.find('.kp10-cell-discount').length,
            cellNameWidth = cellNameWidthCurrent + (discountCellLength ? 3 : 0), // 3 - width of the discount colls, 0 - if discount not added
            mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
            $discount = $activeTab.find('.price-decoration .kp10-discount .kp10-pane-discount'),
            // discountModel = $discount.data('model'),
            result = [], //models for removing
            values = [],
            fields = []; //For remove from db

        if (mModel && mModel.collection && mModel.collection.models) {
            //rows
            for (let i in mModel.collection.models) {

                let mComps = mModel.collection.models[i].get('components');
                //don't use group title
                if (!mComps.models[0].view.$el.hasClass('pane-title')) {

                    for (var m in mComps.models) {
                        let cellModel = mComps.models[m];
                        if (cellModel.get('type') === 'discount') {
                            //Values
                            if (!mModel.collection.models[i].view.$el.hasClass('tab-pane-inner__menu-row-heading')) {
                                //For remove from DB
                                values.push({
                                    'product-id': mModel.collection.models[i].view.$el.attr('data-db-id'),
                                    'db-id': cellModel.view.$el.attr('data-db-id'),
                                    delete: true
                                });
                            } else { //Fields
                                fields.push({
                                    'db-id': cellModel.view.$el.attr('data-db-id'),
                                    delete: true
                                });
                            }

                            //find models for remove
                            result.push(cellModel);
                        }

                        let $el = cellModel.view.$el;
                        //update cell-name class
                        if ($el.hasClass('kp10-cell-name')) {

                            //find class
                            let cModel = cellModel.attributes.classes.models.find(function (item) {
                                return item.id.match(/col-md-\d/g);
                            });
                            //remove class
                            cellModel.attributes.classes.remove(cModel);
                            //add class
                            cellModel.attributes.classes.add({ name: 'col-md-' + cellNameWidth });
                        }
                    }

                    //remove discount from hidden offer block
                    let relativeId = mModel.collection.models[i].view.$el.data('src');
                    if (relativeId) {
                        //remove from hidden block
                        let $hiddenProductDiscount = wrapper.view.$el.children(relativeId).find('.kp10-discount-container > div');

                        if ($hiddenProductDiscount.length) {
                            $hiddenProductDiscount.data('model').destroy();
                        }

                        let $fancyProductDiscount = wrapper.view.$el.find('.fancybox-container .kp10-discount-container > div');

                        if ($fancyProductDiscount.length) {
                            $fancyProductDiscount.data('model').destroy();
                        }
                    }
                }
            }

            //remove elements from collection
            for (let m in result) {
                result[m].collection.remove(result[m]);
            }

            let discountEnabled = $activeTab.find('.kp10-cell-discount').length;

            //Remove discount in bottom
            //Remove only if special discount already removed
            if (!$specialDiscountRow.length && !discountEnabled && $discount.length) {
                $discount.data('model').destroy();
            }
            // priceComps.remove(priceComps.models[0]);
            //refresh
            this.calculatePositionsPrices();

            //Mark product for remove from DB
            this.updateCpSettingsVariantProducts({ delete: { fields: fields, values: values } });

            //trigger click on discount
            this.editor.select(mModel);
        }

    },

    removeSpecialDiscount: function (model) {

        setTimeout(() => {
            //refresh positions prices
            this.calculatePositionsPrices();

            let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
                discountEnabled = $activeTab.find('.kp10-cell-discount').length,
                specialDiscountLength = $activeTab.find('.kp10-row-special-discount').length,
                $specialDiscount = $activeTab.find('.kp10-special-discount .kp10-row-special-discount[data-db-id="' + model.view.$el.attr('data-db-id') + '"]');

            //Check if component was realy deleted
            if (!$specialDiscount.length && model.view.$el.attr('data-db-id')) {
                //Mark special discount for remove from DB
                this.updateCpSettingsVariantProducts({ delete: { specialDiscount: { 'db-id': model.view.$el.attr('data-db-id'), delete: true } } });

                //check if discount not added for current variant
                if (!discountEnabled && !specialDiscountLength) {
                    this.removeDiscount();
                }
            } else {
                //Fix if component was moved
                this.updateCpSettingsVariantProducts();
            }
        }, 100);
    },

    removeVariant: function (model) {

        let _this = this,
            wrapper = this.wrapper,
            variantType = model.view.$el.attr('href'),
            index = model.view.$el.parent().index(),
            $tabContent = wrapper.view.$el.find('.tab-content.cp-options__content .tab-content'),
            $navTabs = wrapper.view.$el.find('ul.nav-tabs');

        //remove tabs except last
        if ($navTabs.find('a[role="tab"]').length === 1) {
            return;
        }

        if ($navTabs.find('a[href^="' + variantType + '"]').length) {
            $tabContent.find(variantType).data('model').destroy();
        }

        //remove tab element
        model.collection.parent.collection.models[index].destroy();

        //activate first tab
        $navTabs.find('a[role="tab"]:first-child').click();

    },

    setVariantRecommended: function (status) {
        let _this = this,
            wrapper = this.wrapper,
            $navTabActive = this.wrapper.view.$el.find('ul.nav-tabs li.active a'),
            html = '<span class="label_top label_top_recomended corporate-bg-color" \
                data-gjs-badgable="false" data-gjs-stylable="false" data-gjs-droppable="false" \
                data-gjs-draggable="false" data-gjs-removable="false" data-gjs-copyable="false" \
                data-gjs-editable="false">Рекомендуем</span>';

        //Remove recommended from current variant
        if (!status && $navTabActive.find('span.label_top_recomended').length) {
            let mModel = $navTabActive.find('span.label_top_recomended').data('model');
            mModel.collection.remove(mModel);

            //Update variant settings
            this.updateCpSettingsVariant({ recommended: status ? true : false });
            return;
        }

        //find and remove from other variants
        if (wrapper.view.$el.find('span.label_top_recomended').length) {
            let mModel = wrapper.view.$el.find('span.label_top_recomended').data('model');
            mModel.collection.remove(mModel);
        }

        //Add recommended
        $navTabActive.data('model').get('components').add(html, { at: 1 });

        //Update variant settings
        this.updateCpSettingsVariant({ recommended: status ? true : false });

    },
    //Set variant active/inactive
    setVariantActivity: function (trait, status, selected) {
        let _this = this,
            $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $navTabActive = this.wrapper.view.$el.find('ul.nav-tabs li.active'),
            tabContentModel = $activeTab.data('model'),
            navTabModel = $navTabActive.data('model'),
            className = 'disabled-variant',
            deactivatedVariants = 0,
            traitSelected = false;

        //Count deactivated variants
        $navTabActive.data('model').collection.each(function (model) {
            //Get a element
            let aModel = model.get('components').first();
            //Check trait activity
            aModel.get('traits').each(function (modelA) {
                //Get only checked
                if (modelA.get('name') === 'activity' && false === modelA.get('checked')) {
                    deactivatedVariants += 1;
                }
            });
        });

        //If variant selected
        if (selected) {
            //restore status
            trait.setValue('yes');
            trait.model.set('checked', 'yes');
            window.message('Невозможно выключить выбранный вариант');
            return;
        }

        //allow disable variants except last
        if (deactivatedVariants > 2) {
            //restore status
            trait.setValue('yes');
            trait.model.set('checked', 'yes');
            window.message('Необходимо оставить хоть один активный вариант');
            return;
        }

        if (status) {

            let tabClassModel = tabContentModel.attributes.classes.models.find(function (item) {
                return item.id.match(/disabled-variant/g);
            });
            // //remove class
            tabContentModel.attributes.classes.remove(tabClassModel);

            let navClassModel = navTabModel.attributes.classes.models.find(function (item) {
                return item.id.match(/disabled-variant/g);
            });
            // //remove class
            navTabModel.attributes.classes.remove(navClassModel);
        } else {
            //add class
            tabContentModel.attributes.classes.add({ name: className });
            navTabModel.attributes.classes.add({ name: className });
        }
    },

    copyProductsFromVariant: function (id) {

        if (id) {
            let _this = this,
                $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
                mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
                sourceModel = this.wrapper.view.$el.find(id + '.tab-pane .tab-pane-inner__menu-row-heading').data('model');

            //Show info message
            window.message(this.config.messages.products.copying);

            setTimeout(() => {
                //check if source model has discount
                if (sourceModel.view.$el.find('.kp10-cell-discount').length) {
                    //add discount for current model
                    mModel.collection.add('<div class="add-discount" data-gjs-type="add-discount" data-gjs-draggable=".tab-pane-inner__menu" data-gjs-copyable="false"></div>');
                }

                //Get source product list
                for (let i in sourceModel.collection.models) {
                    let sModel = sourceModel.collection.models[i],
                        sCompModel = sModel.get('components').models;
                    if (i != 0) {
                        //For Non group 
                        if (!sModel.view.$el.find('.pane-title').length) {

                            let src = sModel.view.$el.data('src'),
                                name = sModel.view.$el.find('.kp10-cell-name span').text().trim(),
                                count = sModel.view.$el.find('.kp10-cell-count').text().trim(),
                                price = sModel.view.$el.find('.kp10-cell-price').text().trim(),
                                photo = sModel.view.$el.find('.kp10-cell-name img').attr('src'),
                                id = sModel.view.$el.attr('data-id') ? sModel.view.$el.attr('data-id') : '0',
                                fake = sModel.view.$el.data('fake') != '0',
                                description = this.wrapper.view.$el.children(src).find('.modal-product__info > div').text().trim(),
                                goodsColls = [],
                                discountCools = [];

                            //For custom colls
                            sModel.get('components').each((colModel, index) => {
                                if (colModel.get('type') === 'add-goods-coll') {
                                    //Save value
                                    goodsColls.push({
                                        id: id,
                                        index: index,
                                        value: this.parseF(colModel.view.$el.text())
                                    });
                                }
                                if (colModel.get('type') === 'discount') {
                                    discountCools.push({
                                        index: index,
                                        value: this.parseF(colModel.view.$el.text())
                                    });
                                }
                            });

                            //generate unique id for product
                            let d = new Date(),
                                cProductId = d.getTime(); //new product id

                            //Add product
                            this.addProduct({
                                id: id,
                                cProductId: cProductId,
                                name: name,
                                file: photo,
                                cost: price,
                                count: count,
                                description: description,
                                goodsColls: goodsColls,
                                discountCools: discountCools,
                                fake: id === '0' || fake, //fix for default product
                                disableCalculate: true
                            });

                        } else {
                            let groupModel = sModel.clone();
                            groupModel.addAttributes({ 'data-db-id': 0 });
                            groupModel.set({ 'data-copied': true }); //Fix for currency
                            groupModel.get('components').at(0).addAttributes({ 'data-db-id': 0 });
                            //Group
                            mModel.collection.add(groupModel);
                        }

                    } else {
                        let rowModel = mModel.collection.models[0], //title row
                            cellComponent = rowModel.get('components');
                        //If title in source model has more columns then in current
                        for (let m in sCompModel) {

                            let hModel = sCompModel[m],
                                sourceClass = hModel.view.$el.attr('class').match(/(kp10-[a-z-]+)/g);

                            sourceClass = sourceClass ? sourceClass[0] : '';

                            //if current model in this position doesn't have class
                            if (sourceClass && cellComponent.models[m] && !cellComponent.models[m].view.$el.hasClass(sourceClass)) {
                                //add source model to current
                                if (rowModel.view.$el.hasClass('tab-pane-inner__menu-row-heading')) {
                                    //if parent don't have class
                                    //add only custom coll
                                    if (!rowModel.view.$el.find('.' + sourceClass).length && sourceClass === 'kp10-good-coll') {
                                        let headerModel = hModel.clone();
                                        headerModel.addAttributes({ 'data-db-id': 0 });
                                        headerModel.get('components').at(0).addAttributes({ 'data-db-id': 0 });
                                        //copy with header title
                                        cellComponent.add(headerModel, { at: m });
                                    }
                                }
                            }
                        }
                    }
                }
                //refresh
                this.calculatePositionsPrices();
                //Update currency relative to current settings
                this.changeCurrencyFromSettings();
                //Need update html
                this.editor.saveHtml = true;
                //Hide message
                window.hideMessage();
            }, 1000);
        }
    },

    numberFormat: function (str) {
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
    /**
     * Update offer name, count, price
     * @param model
     */
    updateProductModel: function (model) {

        let _this = this,
            $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $fancyContainer = this.wrapper.view.$el.find('.fancybox-container');

        if (model && model.relativeId) {
            //update data in table
            let $tableRow = this.wrapper.view.$el.find('.tab-content .tab-content__table .js-fancybox-offer[data-src="' + model.relativeId + '"]');

            //update offer img
            if (model.imgAttributes) {
                $tableRow.find('.kp10-cell-name img').data('model').set(model.imgAttributes);
            }
            //update offer name
            if (model.name) {
                this.updateContent($tableRow.find('.kp10-cell-name span').data('model'), model.name);
            }
            //update offer count
            if (model.count) {
                this.updateContent($tableRow.find('.kp10-cell-count').data('model'), model.count);
            }
            //update offer price
            if (model.price) {
                this.updateContent($tableRow.find('.kp10-cell-price').data('model'), model.price);
            }
            //update offer discount
            if (model.discount) {
                this.updateContent($tableRow.find('.kp10-discount').data('model'), model.discount);
            }

            //update data in list
            let $listRow = this.wrapper.view.$el.find('.tab-content .tab-content__list .js-fancybox-offer[data-src="' + model.relativeId + '"]');

            //update offer img
            if (model.imgAttributes) {
                $listRow.find('img.card-offer__preview-img').data('model').set(model.imgAttributes);
            }
            //update offer name
            if (model.name) {
                this.updateContent($listRow.find('.kp10-cell-name > span').data('model'), model.name);
            }
            //update offer count
            if (model.count) {
                this.updateContent($listRow.find('.kp10-cell-count').data('model'), model.count);
            }
            //update offer price
            if (model.price) {
                this.updateContent($listRow.find('.kp10-cell-price').data('model'), model.price);
            }

            let $hiddenProduct = this.wrapper.view.$el.children(model.relativeId);
            if ($hiddenProduct.length) {
                //update data in hidden block
                if (model.name) {
                    this.updateContent($hiddenProduct.find('.kp10-cell-name > span').data('model'), model.name);
                }
                if (model.count) {
                    this.updateContent($hiddenProduct.find('.kp10-cell-count').data('model'), model.count);
                }
                if (model.price) {
                    this.updateContent($hiddenProduct.find('.kp10-cell-price').data('model'), model.price);
                }
                if (model.cost) {
                    this.updateContent($hiddenProduct.find('.kp10-cell-cost').data('model'), model.cost);
                }
                if (model.discount) {
                    this.updateContent($hiddenProduct.find('.kp10-discount').data('model'), model.discount);
                }
            }
            //fancybox update
            if (model.cost) {
                //model exists
                if ($fancyContainer.find('.fancybox-stage > .fancybox-slide > div[data-src="' + model.relativeId + '"]').length) {
                    this.updateContent($fancyContainer.find('.kp10-cell-cost').data('model'), model.cost);
                }
            }

            if (model.goodsColl) {

                //custom fields
                if (model.goodsColl.title) {
                    //find model by index
                    if ($hiddenProduct.length) {
                        $hiddenProduct.find('.kp10-goods-coll').data('model').get('components').each(function (m) {
                            if (m.get('parentIndexForRemove') === _this.parseF(model.goodsColl.index)) {
                                _this.updateContent(m.view.$el.find('b').data('model'), model.goodsColl.title);
                            }
                        });
                    }
                    //update title of the column in table
                    $activeTab.find('.tab-content__table .tab-pane-inner__menu-row-heading').data('model').get('components').each(function (m) {
                        if (m.get('indexForRemove') === _this.parseF(model.goodsColl.index)) {
                            _this.updateContent(m, model.goodsColl.title);
                        }
                    });
                }

                if (model.goodsColl.value) {
                    //update in hidden blocks
                    if ($hiddenProduct.length) {
                        $hiddenProduct.find('.kp10-goods-coll').data('model').get('components').each(function (m) {
                            if (m.get('parentIndexForRemove') === _this.parseF(model.goodsColl.index)) {
                                _this.updateContent(m.view.$el.find('span').data('model'), model.goodsColl.value);
                            }
                        });
                    }

                    //value update
                    $tableRow.data('model').get('components').each(function (m) {
                        if (m.get('type') === 'add-goods-coll' && m.get('parentIndexForRemove') === _this.parseF(model.goodsColl.index)) {
                            _this.updateContent(m, model.goodsColl.value);
                        }
                    });
                    //update prices
                    this.calculatePositionsPrices();
                }
            }

        }
    },
    /**
     * Parse model from rte:disable events
     * @param model
     * @returns {{}}
     */
    prepareModelRteProduct: function (model) {
        let parsedModel = {};
        if (model) {
            let $productRow = model.$el.closest('[data-src]');
            // imgModel = $offerRow.find('img.kp10-js-fancybox-product').data('model');
            if ($productRow.length) {
                parsedModel.relativeId = $productRow.attr('data-src');

                //get img attributes
                // parsedModel.imgAttributes = imgModel ? imgModel.attributes : {};
                //get name
                parsedModel.name = $productRow.find('.kp10-cell-name span').text().trim();
                //count
                parsedModel.count = $productRow.find('.kp10-cell-count').text().trim();
                //price
                parsedModel.price = $productRow.find('.kp10-cell-price').text().trim();
                //discount
                if ($productRow.find('.kp10-discount').length) {
                    parsedModel.discount = $productRow.find('.kp10-discount').text().trim();
                }
            }
        }

        return parsedModel;
    },

    /**
     * Helper function for update content in backbone model
     * @param model
     * @param content
     */
    updateContent: function (model, content) {
        let comps = model.get('components');
        comps.length && comps.reset();

        model.set('content', ' ')
            .set('content', content);

    },

    /**
     *
     * @param model
     */
    updateProductGoodsColl: function (model) {

        let _this = this,
            content = model.$el.text().trim();
        //For cell value(numbers)
        if (model.$el.attr('data-kp10-update-prices')) {
            content = this.parseF(content);
        }

        let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model');

        //from table edit
        if (!model.$el.closest('.kp10-goods-coll').length) {
            if (model.el.dataset.child) {
                let relativeId = model.$el.closest('[data-src]').data('src');

                if (relativeId) {
                    this.updateProductModel({
                        relativeId: relativeId,
                        goodsColl: {
                            value: content,
                            index: model.model.get('parentIndexForRemove')
                        }
                    });
                }

            } else {
                //update title
                if (mModel && mModel.collection && mModel.collection.models) {
                    //rows
                    for (let i in mModel.collection.models) {
                        //hidden extended products info
                        let relativeId = mModel.collection.models[i].view.$el.data('src');
                        if (relativeId) {
                            //update title for all products
                            _this.updateProductModel({
                                relativeId: relativeId,
                                goodsColl: {
                                    title: content,
                                    index: model.model.get('indexForRemove') || model.model.get('parentIndexForRemove')
                                }
                            });
                        }
                    }
                }
            }
        } else { //from popup
            if (mModel && mModel.collection && mModel.collection.models) {
                //title update
                if (!model.el.dataset.child) {
                    //update title for all products
                    if (mModel && mModel.collection && mModel.collection.models) {
                        //rows
                        for (let i in mModel.collection.models) {
                            //hidden extended offer info
                            let relativeId = mModel.collection.models[i].view.$el.data('src');
                            if (relativeId) {
                                //update title for all offers
                                _this.updateProductModel({
                                    relativeId: relativeId,
                                    goodsColl: {
                                        title: content,
                                        index: model.model.collection.parent.get('parentIndexForRemove')
                                    }
                                });
                            }
                        }
                    }
                } else {
                    //value data
                    this.updateProductModel({
                        relativeId: model.$el.closest('[data-src]').data('src'),
                        goodsColl: {
                            value: content,
                            index: model.model.collection.parent.get('parentIndexForRemove')
                        }
                    });
                }
            }
        }
    },

    /**
     * Create extended popup offer card
     */
    createHiddenProduct: function (params) {
        let config = this.config,
            id = params.id,
            cProductId = params.cProductId,
            fakeProductId = params.fakeProductId,
            type = params.type,
            product = params.product;

        if (!type || !config || !config.products) {
            return;
        }

        let productModel = this.wrapper.view.$el.children('#modal-product-empty').data('model');

        if (product) {
            let productId = cProductId + id,
                cost = product.cost ? this.parseF(product.cost + '') : 0,
                count = product.count ? product.count : 0;
            //update img
            productModel.view.$el.find('img.modal-product__preview-img').data('model').set('attributes', {
                src: product.photo,
                attributes: { src: product.photo }
            });

            //update name
            this.updateContent(productModel.view.$el.find('.kp10-cell-name > span').data('model'), product.name);
            //update price
            this.updateContent(productModel.view.$el.find('.kp10-cell-price').data('model'), cost);
            //update count
            this.updateContent(productModel.view.$el.find('.kp10-cell-count').data('model'), count);
            //update description
            this.updateContent(productModel.view.$el.find('.modal-product__info > div').data('model'), product.description);

            //copy model
            productModel = productModel.clone();

            if (!fakeProductId) {
                //update attributes
                productModel.set('attributes', {
                    id: 'modal-product-' + productId + '-' + type,
                    'data-src': '#modal-product-' + productId + '-' + type,
                });
            } else { //For custom product id. Using when add product from widget
                //update attributes
                productModel.set('attributes', {
                    id: fakeProductId.substr(1),
                    'data-src': fakeProductId,
                });
            }

            this.wrapper.get('components').add(productModel);

        } else {
            console.error('Product empty modal not found');
        }
    },

    addGalleryBtnToAsset: function (galleryId) {
        //add new button to assets manager
        let _this = this,
            $assetManager = $(this.editor.AssetManager.getContainer());
        $assetManager.find('.gjs-am-assets-header')
            .append(' <button class="gjs-btn-prim" id="add-to-gallery" data-gallery-id="' + galleryId + '"><i class="fa fa-plus" aria-hidden="true"></i> Добавить в галерею</button>');

        //create event for modal close
        $(document).on('click', '.gjs-mdl-dialog .gjs-mdl-btn-close, .gjs-mdl-backlayer', function () {
            $assetManager.find('#add-to-gallery').remove();
            $(this).unbind('click');

            //if slider is empty, remove it
            //TODO !!
            // let $gallery = _this.wrapper.view.$el.find('[data-gallery-id="' + galleryId + '"]');
            // if (!$gallery.find('ul li img').length && $gallery.data('model')) {
            //     $gallery.data('model').destroy();
            // }
        });

    },
    /**
     * Add images from assets or update current gallery
     */
    updateGallery: function (galleryId) {
        let $gallery = this.wrapper.view.$el.find('[data-gallery-id="' + galleryId + '"]');

        if ($gallery.length) {
            let galleryType = $gallery.data('model').get('type');

            let photos = [];
            if (galleryType === 'gallery') {
                //get exists photo in gallery
                $gallery.find('.csslider .carousel-item').each(function () {
                    let url = $(this).find('img').attr('src'),
                        caption = $(this).find('p').text().trim();
                    photos.push({ url: url, caption: caption });
                });
            } else {
                //Slider
                $gallery.find('ul li img').each(function () {
                    let url = $(this).attr('src');
                    photos.push({ url: url, caption: '' });
                });
            }

            let $assetManager = $(this.editor.AssetManager.getContainer());
            //images from asset
            $assetManager.find('.gjs-am-assets .gjs-am-asset-image.gjs-am-highlight').each(function () {
                let url = $(this).find('.gjs-am-preview').css('background-image');
                photos.push({ url: url.substring(5, url.length - 2), caption: ' ' });
                //deselect asset images
                $(this).removeClass('gjs-am-highlight');
            });

            if (photos.length) {
                //working with 2 view
                let ulModel = $gallery.find('ul').data('model');

                if (galleryType === 'gallery') {

                    if (ulModel) {
                        let htmlSlides = '',
                            htmlInputSlides = '',
                            htmlLabelSlides = '',
                            imagesGallery = this.arrayChunk(photos, 4); //get li (slides)

                        if (imagesGallery) {
                            for (let i in imagesGallery) {

                                let images = imagesGallery[i],
                                    slideIndex = parseInt(i) + 1,
                                    checked = i == 0 ? 'checked="checked"' : '';

                                //for navigation
                                htmlInputSlides += `<input type="radio" name="slides_${galleryId}" id="slides_${galleryId}_${slideIndex}" ${checked} >`;
                                htmlLabelSlides += `<label class="arrow corporate-bg-color" for="slides_${galleryId}_${slideIndex}" data-gjs-type="disabled"></label>`;

                                htmlSlides += '<li class="clearfix" data-gjs-type="disabled">';
                                for (let k in images) {
                                    let image = images[k];
                                    htmlSlides += `<div class="carousel-item" data-gjs-type="disabled">
                                        <img class="gallery-img" data-gallery-id="${galleryId}" src="${image.url}">
                                        <p class="carousel-big-item-caption" data-gjs-type="editable">${image.caption}</p>
                                        </div>`;
                                }
                                htmlSlides += '</li>';
                            }

                            let sliderComponent = $gallery.find('.csslider').data('model').get('components'),
                                arrowComponent = $gallery.find('.arrows').data('model').get('components');

                            //Remove previous added input's
                            for (let i = sliderComponent.models.length - 1; i >= 0; i--) {
                                const element = sliderComponent.models[i];
                                if (element.get('tagName') == 'input') {
                                    sliderComponent.remove(element);
                                }
                            }

                            //Remove previous added label's
                            for (let i = arrowComponent.models.length - 1; i >= 0; i--) {
                                const element = arrowComponent.models[i];
                                if (element.get('tagName') == 'label' && !element.view.$el.hasClass('goto-last') && !element.view.$el.hasClass('goto-first')) {
                                    arrowComponent.remove(element);
                                }
                            }

                            //add slides
                            ulModel.get('components').reset();
                            ulModel.get('components').add(htmlSlides);
                            //add input slides
                            sliderComponent.unshift(htmlInputSlides);
                            //add label slides
                            arrowComponent.unshift(htmlLabelSlides);
                            //update last label number for navigation
                            $gallery.find('.arrows .goto-first').data('model').set('attributes', { for: 'slides_' + galleryId + '_1' });
                            $gallery.find('.arrows .goto-last').data('model').set('attributes', { for: 'slides_' + galleryId + '_' + imagesGallery.length });

                            //close modal
                            $('.gjs-mdl-dialog .gjs-mdl-btn-close').click();
                        }
                    }
                }

                if (galleryType === 'slider') {
                    if (ulModel) {
                        let htmlSlides = '';

                        for (let i in photos) {
                            let image = photos[i];
                            htmlSlides += `<li data-gjs-type="disabled"><img class="slider-img" src="${image.url}" data-gallery-id="${galleryId}"></li>`;
                        }

                        //add slides
                        ulModel.get('components').reset();
                        ulModel.get('components').add(htmlSlides);
                        //
                        this.wrapper.view.$el.trigger('slider:add');

                        //close modal
                        $('.gjs-mdl-dialog .gjs-mdl-btn-close').click();
                    }
                }
                //Remove selection from added element
                this.editor.select(this.wrapper);

            } else {
                window.message('Неоходимо выбрать изображения')
            }

        } else {
            window.message('Галарея не найдена');
        }
    },

    arrayChunk: function (input, size) {	// Split an array into chunks
        //
        // +   original by: Carlos R. L. Rodrigues

        for (var x, i = 0, c = -1, l = input.length, n = []; i < l; i++) {
            (x = i % size) ? n[c][x] = input[i] : n[++c] = [input[i]];
        }

        return n;
    },

    updateAdvantageClasses: function (model, status) {
        let advantageModel = model.target.view.$el.closest('.advantage-block').data('model');
        if (status) {
            let advantageClassModel = advantageModel.attributes.classes.models.find(function (item) {
                return item.id === 'col-md-6';
            });
            // //remove class
            advantageModel.attributes.classes.remove(advantageClassModel);
            //add new class
            advantageModel.attributes.classes.add({ name: 'col-md-12' });
        } else {
            let advantageClassModel = advantageModel.attributes.classes.models.find(function (item) {
                return item.id === 'col-md-12';
            });
            // //remove class
            advantageModel.attributes.classes.remove(advantageClassModel);
            //add new class
            advantageModel.attributes.classes.add({ name: 'col-md-6' });
        }
    },

    showCropEditor: function (assets) {

        if (assets && assets.length) {

            let html = '';

            for (let i in assets) {
                let asset = assets[i];

                let index = parseInt(i) + 1;

                html += '<div class="cropper-container-img ' + (index === 1 ? 'active' : '') + '" data-index="' + index + '" data-template="0">' +
                    '<label>Введите название изображения</label>' +
                    '<div class="gjs-field cropper-name">' +
                    '<input type="text" value="' + asset.get('name') + '">' +
                    '</div>' +
                    '<div class="cropper-img">' +
                    '<img class="crop" src="' + asset.get('src') + '" data-file="' + asset.get('file') + '">' +
                    '</div>' +
                    '</div>';
            }

            let amConfig = this.editor.AssetManager.getConfig(),
                folderNameArr = amConfig.params.path.split('/'),
                folderName = folderNameArr[folderNameArr.length - 1],
                active_1 = active_2 = active_3 = active_4 = active_5 = '';

            //If root dir
            if (!folderName) {
                folderName = 'Дом';
            }

            //cropper-active
            switch (folderNameArr[2]) {
                case 'Логотипы':
                    active_1 = 'cropper-active';
                    break;
                case 'Обложки':
                    active_2 = 'cropper-active';
                    break;
                case 'Товары':
                    active_3 = 'cropper-active';
                    break;
                case 'Сотрудники':
                    active_4 = 'cropper-active';
                    break;
                case 'Галерея':
                    active_5 = 'cropper-active';
                    break;
                default:
                    active_1 = 'cropper-active';
                    break;
            }

            $('body').append(
                `<div id="cropper">
                    <div class="cropper-mdl-dialog">
                        <div class="cropper-mdl-header">
                            <div class="cropper-mdl-title">Редактор</div>
                            <div class="cropper-img-counter"><span>1</span>/${assets.length}</div>
                            <div class="cropper-mdl-p-right">
                                <button class="gjs-btn-prim" id="cropper-cancel" title="При отмене файл будет удален">Отмена</button>
                                <button class="gjs-btn-prim" id="cropper-crop">Сохранить</button>
                            </div>
                            <div class="cropper-mdl-button-group">
                                <button class="gjs-btn-prim cropper-template ${active_1}" data-template="1">Логотип</button>
                                <button class="gjs-btn-prim cropper-template ${active_2}" data-template="2">Обложка</button>
                                <button class="gjs-btn-prim cropper-template ${active_3}" data-template="3">Фото Товара</button>
                                <button class="gjs-btn-prim cropper-template ${active_4}" data-template="4">Фото Сотрудника</button>
                                <button class="gjs-btn-prim cropper-template ${active_5}" data-template="5">${this.config.storage.gallery}</button>
                                <button class="gjs-btn-prim cropper-template" data-template="6">Без изменений</button>
                                <button class="gjs-btn-prim cropper-upload-path cropper-active" title="Загрузка файла в папку согласно формату обрезки" data-path="0">По умолчанию</button>
                                <button class="gjs-btn-prim cropper-upload-path" title="Загрузка файла в текущую папку" data-path="1">В папку ${folderName}</button>
                            </div>
                        </div>
                        <div class="cropper-mdl-content">
                        ${html}
                        </div>
                    </div>
                </div>`
            );

            $('#cropper').data({ editor: this.editor }).find('img.crop').each(function () {
                $(this).data({
                    cropper: new Cropper($(this).get(0), {
                        movable: false,
                        rotatable: false,
                        scalable: false,
                        // zoomable: false,
                        // zoomOnTouch: false,
                        // zoomOnWheel: false,
                        aspectRatio: 17 / 4
                    })
                });
            });

            //Select first template
            $('#cropper').find('.cropper-template.cropper-active').click();

        }
    },
    //Add product
    addProduct: function (params) {
        let id = params.id,
            cProductId = params.cProductId,
            fakeProductId = params.fakeProductId,
            name = params.name,
            photo = params.file,
            count = params.count ? params.count : 1, //TODO: перевірити, чомусь завжди 0
            cost = !isNaN(this.parseF(params.cost)) ? this.parseF(params.cost) : 0,
            description = params.description,
            goodsColls = params.goodsColls,
            discountCools = params.discountCools,
            discount = params.discount,
            fake = params.fake ? params.fake : 0,
            _this = this,
            disableCalculate = !params.disableCalculate,
            index = params.index;

        let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active');

        if (params.$activeTab) {
            //If tab passed
            $activeTab = params.$activeTab;
        }

        let discountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index(),
            priceWithDiscountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price-with-discount').index(),
            type = $activeTab.attr('id'),
            productId = !fakeProductId ? '#modal-product-' + (cProductId + id) + '-' + type : fakeProductId,
            $navTabActive = this.wrapper.view.$el.find('ul.nav-tabs li.active'),
            mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
            $listRow = $activeTab.find('.tab-content__list.row'),
            // countUserGoodColl = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-good-coll').length,
            cellNameClass = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').attr('class').match(/col-md-\d/g)[0], //new width
            headerModel = $activeTab.find('.tab-pane-inner__menu-header-cell').data('model'),
            html =
                `<div class="row tab-pane-inner__menu-row js-fancybox-offer corporate-color-hover vertical-align" data-id="${id}" data-fake="${fake}" 
                    data-src="${productId}" 
                    data-gjs-draggable="div.tab-pane-inner__menu"
                    data-gjs-type="goods-variant">
                    <div class="${cellNameClass} col-xs-7 col-sm-7 tab-pane-inner__menu-cell kp10-cell-name" 
                        data-gjs-type="disabled">
                        <div class="row vertical-align" data-gjs-type="disabled">
                            <div class="col-xs-3" data-gjs-type="disabled">
                                <img class="standard-table-image kp10-js-fancybox-product"
                                    src="${photo}"
                                    data-src="${productId}"
                                    data-gjs-type="product-image">
                                </div>
                                <div class="col-xs-9" data-gjs-type="disabled">
                                    <span data-gjs-type="editable" data-kp10-update-prices="true">${name}</span>
                                </div>
                        </div>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-cell kp10-cell-count" 
                        data-gjs-type="editable"
                        data-kp10-update-prices="true">${count}</div>
                    <div class="col-xs-1 col-sm-1 col-md-1 tab-pane-inner__menu-cell nowrap" 
                        data-gjs-type="disabled">
                        <span class="kp10-cell-price"
                            data-gjs-type="editable"
                            data-kp10-update-prices="true">
                            ${cost}
                        </span>
                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-cell kp10-cell-cost" 
                        data-gjs-type="disabled">
                        <span class="kp10-cell-cost" data-gjs-type="disabled">
                            ${cost}
                        </span>
                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                    </div>
                </div>`;

        let model = '';
        //find collection and add row
        // if (index > 0) {
        //     //Add product at selected position
        //     model = mModel.collection.add(html, { at: index });
        // } else {
        //     model = mModel.collection.add(html);
        // }
        model = mModel.collection.add(html);

        //add model to product list view
        let $emptyProductList = this.wrapper.view.$el.children('#empty-product-list').children('div');
        $emptyProductList.find('a.js-fancybox-offer[data-src]').data('model').set('attributes', {
            'data-src': productId
        });
        $emptyProductList.find('img.card-offer__preview-img').data('model').set('attributes', {
            src: photo,
            'data-src': productId
        });
        this.updateContent($emptyProductList.find('.kp10-cell-name > span').data('model'), name);
        this.updateContent($emptyProductList.find('.kp10-cell-price').data('model'), cost);
        this.updateContent($emptyProductList.find('.kp10-cell-count').data('model'), count);
        $listRow.data('model').get('components').add($emptyProductList.data('model').clone());

        let product = {};
        if (!fake) {
            //Find product by id
            product = this.config.products.find(function (el) {
                return el.id === parseInt(id);
            });
        } else {
            //Create fake product
            product = {
                id: cProductId,
                photo: photo,
                name: name,
                cost: cost,
                count: count,
                description: description
            };
        }

        //add hidden product block
        this.createHiddenProduct({
            id: id,
            type: type,
            cProductId: cProductId,
            fakeProductId: fakeProductId,
            product: product
        });

        //add goods cols
        if (headerModel && headerModel.collection && headerModel.collection.models) {
            for (let m in headerModel.collection.models) {
                let hModel = headerModel.collection.models[m],
                    cell = '';
                //type - add-goods-coll
                if (hModel.get('type') === 'add-goods-coll') {
                    let value = 0,
                        goodsColl = goodsColls && goodsColls.filter(function (data) {
                            return data.index == m;
                        });

                    //Get value for goods coll
                    if (goodsColls && goodsColl.length) {
                        value = goodsColl[0].value;
                    }

                    cell = `<div class="hidden-xs hidden-sm col-md-1 tab-pane-inner__menu-cell kp10-good-coll" 
                        data-gjs-type="add-goods-coll"
                        data-child="true" 
                        data-gjs-editable="true" 
                        data-kp10-update-prices="true">${value}</div>`;

                    let goodsModel = model.get('components').add(cell, { at: m });

                    //Set default checked
                    goodsModel.set('valueInPrice', true);
                    goodsModel.set('parentIndexForRemove', parseInt(m));

                    //Add good coll for hidden block
                    let hiddenOfferRow = `<div class="modal-product__count" data-gjs-type="disabled">
                        <b data-gjs-type="disabled">Столбец</b>
                        <strong data-gjs-type="disabled">: </strong>
                        <span data-kp10-update-prices="true" data-child="true" data-gjs-type="add-goods-coll">${value}</span>
                        </div>`;

                    //update in hidden offer block
                    if (productId) {
                        let hiddenCol = this.wrapper.view.$el.children(productId).find('.kp10-goods-coll').data('model').get('components').add(hiddenOfferRow, { at: parseInt(m) });
                        hiddenCol.set('parentIndexForRemove', parseInt(m));
                    }
                }

                //type - add-goods-coll
                if (hModel.get('type') === 'discount') {
                    let dClass = hModel.view.$el.attr('class').match(/col-md-\d/g)[0];

                    let value = 0;
                    //Using when copy variant from another
                    if (discountCools) {
                        let discountColl = discountCools.filter(function (data) {
                            return data.index == m;
                        });

                        //Get value for goods coll
                        if (discountColl.length) {
                            value = discountColl[0].value;
                        }
                    }
                    //When added product from widget
                    if (discount) {
                        value = discount;
                    }

                    cell = `<div class="${dClass} hidden-xs hidden-sm tab-pane-inner__menu-cell kp10-discount"
                        data-gjs-type="discount" 
                        data-kp10-update-prices="true" 
                        data-gjs-editable="true">${value}</div>`; //(dClass === 'col-md-2' ? cost : '0')

                    model.get('components').add(cell, { at: m });

                    if (hModel.view.$el.hasClass('kp10-cell-discount')) {
                        //Create hidden product
                        let hiddenProductRow = `<div class="modal-product__count" data-gjs-type="disabled">
                            <b data-gjs-type="disabled">Скидка: </b> 
                            <span class="kp10-discount" data-gjs-type="discount" data-gjs-editable="true" data-kp10-update-prices="true">${value}</span>
                            <span data-gjs-type="disabled">%</span>
                            </div>`;

                        if (this.wrapper.view.$el.children(productId).find('.kp10-discount-container').length) {
                            this.wrapper.view.$el.children(productId).find('.kp10-discount-container').data('model').get('components').add(hiddenProductRow);
                        }
                    }
                }
            }
        }

        //refresh positions
        if (disableCalculate) {
            this.calculatePositionsPrices();
            //Update currency relative to current settings
            this.changeCurrencyFromSettings();
        }

        if (!fakeProductId) {
            //Add system product id
            product.fakeProductId = productId;
            //Add index for ordering
            product.index = model.view.$el.index();
            product['db-id'] = 0;
            product.image = photo;
            product.id = !fake ? product.id : 0;
            product.values = [];

            //Get field value for added product
            model.view.$el.children().each(function () {

                let $this = $(this),
                    type = _this.getElementType($this);

                product.values.push({
                    'db-id': 0,
                    index: $(this).index(),
                    value: $(this).text().trim(),
                    type: type
                });
            });

            this.updateCpSettingsVariant({ product: product });
            //Need update html
            this.editor.saveHtml = true;
        }
    },
    //Add tax to variant
    addTaxToVariant: function (value) {
        let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $navTabActive = this.wrapper.view.$el.find('ul.nav-tabs li.active'),
            taxModel = $activeTab.find('.total-sum-tax').data('model'),
            // totalSum = F.parseI($activeTab.find('.tab-pane-inner__price-cell_finally > span').text()),
            // tax = F.numberFormat(Math.round(totalSum / 1.18 * 0.18)),
            taxComp = taxModel.get('components'),
            html = '';

        if (value === 1) {
            html =
                `<div data-gjs-type="disabled">
                    включая НДС 
                    <span class="tax-value" data-gjs-type="disabled">0</span>
                    <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                </div>`;
        }
        if (value === 2) {

            html = '<div data-gjs-type="disabled">ндс не облагается</div>' +
                '<div data-gjs-type="disabled">(согласно п.2, ст.346.11 нк рф)</div>';
        }

        taxComp.reset();
        taxComp.add(html);
        //Calculate tax
        this.updateTax();
        //
        this.updateCpSettingsVariant({ tax: value });
        //Update traits for current variant
        let tabModel = $navTabActive.find('[data-gjs-type="variant"]').data('model');
        if (tabModel) {
            tabModel.get('traits').each(function (traitModel) {
                if (traitModel.get('name') === 'variant-tax') {
                    traitModel.set('selected', value);
                }
            });
        }
        //Update tax for button. Using in updateCpSettingsVariantProducts()
        let buttonModel = $activeTab.find('.price-decoration .button-choose').data('model');
        if (buttonModel) {
            buttonModel.get('traits').each(function (traitModel) {
                if (traitModel.get('name') === 'variant-tax') {
                    traitModel.set('selected', value);
                }
            });
        }
    },
    //Update tax if exists
    updateTax: function () {
        let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $tax = $activeTab.find('.total-sum-tax .tax-value');

        //If tax exists
        if ($tax.length) {
            let taxModel = $tax.data('model'),
                totalSum = this.parseI($activeTab.find('.tab-pane-inner__price-cell_finally > span').text()),
                tax = this.numberFormat(Math.round(totalSum / 1.18 * 0.18));

            this.updateContent(taxModel, tax);
        }
    },
    //Show assets when click on gallery/slider image toolbar
    showGalleryAssets: function () {
        let model = this.editor.getSelected();
        if (model) {

            let am = this.editor.AssetManager,
                amConfig = this.editor.AssetManager.getConfig(),
                path = this.config.path + '/' + this.config.storage.gallery,
                galleryId = model.get('attributes')['data-gallery-id'];
            if (galleryId) {

                this.editor.runCommand('open-assets');

                //Change path in config
                amConfig.params.path = path;

                //Show only gallery folder
                this.showAmFolder(path);
                // am.render(am.getAll().filter(
                //     asset => asset.get('folder') === path
                // ));

                //add new button to assets manager
                this.addGalleryBtnToAsset(galleryId);
            } else {
                console.warn('galleryId undefined');
            }
        }
    },
    //delay for keyup
    delay: (function () {
        let timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })(),
    //Open component settings panel
    openComponentSettings: function () {
        $('.gjs-pn-views').find('.gjs-pn-btn.fa-cog:not(.gjs-pn-active)').click();
    },
    openToolbar: function () {
        $('.gjs-pn-views').find('.gjs-pn-btn.fa-wrench:not(.gjs-pn-active)').click();
    },
    //Commercial proposal color
    getCpColor: function (color) {
        let cl = color.getAlpha() == 1 ? color.toHexString() : color.toRgbString();
        return cl.replace(/ /g, '');
    },
    //Commercial proposal color
    setCpColor: function (color, change) {

        let $wrapper = this.wrapper.view.$el,
            panelC = this.editor.Panels.getPanel('views-container'),
            $el = $(panelC.get('appendContent')).find('#gjs-sm-background-color .gjs-field-color-picker'),
            resColor = typeof color === 'object' ? this.getCpColor(color) : color;

        $el.css('background-color', resColor);
        $el.closest('.gjs-field-color').find('input').val(resColor);

        //update color on the page
        if (change) {
            //Create classes for corporate colors
            let c1 = this.editor.SelectorManager.add('corporate-color');
            let c2 = this.editor.SelectorManager.add('corporate-bg-color');
            let c3 = this.editor.SelectorManager.add('corporate-color-hover');
            let c4 = this.editor.SelectorManager.add('corporate-color-fill-path');

            let rule1 = this.editor.CssComposer.add(c1);
            let rule2 = this.editor.CssComposer.add(c2);
            let rule3 = this.editor.CssComposer.add(c3, 'hover');
            let rule4 = this.editor.CssComposer.add(c4);

            rule1.set('style', {
                color: resColor + ' !important'
            });

            rule2.set('style', {
                "background-color": resColor + ' !important'
            });

            rule3.set('style', {
                color: resColor + ' !important'
            });

            rule4.set('style', {
                fill: resColor + ' !important'
            });

        }
    },
    //Update global settings for template
    updateCpSettings: function (object) {

        let $cpSettings = this.wrapper.view.$el.find('#cp-settings'),
            cpSettingsModel = $cpSettings.length ? $cpSettings.data('model') : {},
            cpSettings = cpSettingsModel.get('cp-settings');

        if (!cpSettings) {
            cpSettingsModel.set({ "cp-settings": {} });
            cpSettings = cpSettingsModel.get('cp-settings');
        }
        //merge props
        $.extend(true, cpSettings, object);
    },

    getCpSettings: function (key) {
        let $cpSettings = this.wrapper.view.$el.find('#cp-settings'),
            cpSettings = '';

        //Check if element exists on the page
        if ($cpSettings.length) {
            let cpSettingsModel = $cpSettings.data('model');
            cpSettings = cpSettingsModel.get('cp-settings');
        }

        if (!cpSettings) {
            return '';
        }

        return cpSettings[key] ? cpSettings[key] : '';
    },
    //Update client of the template
    updateClient: function () {
        if (this.clientExist()) {
            let client = this.getCpSettings('client'),
                name = '';
            if (client && client.id) {
                name = client.name;
            }
            this.updateContent(this.wrapper.view.$el.find('.cp-details-about div.client > div:nth-child(2)').data('model'), name);
        }
    },
    //Update client of the template
    updateContactPerson: function () {
        if (this.contactPersonExist()) {
            let contactPerson = this.getCpSettings('contactPerson'),
                name = '';
            if (contactPerson && contactPerson.id) {
                name = contactPerson.name;
            }
            this.updateContent(this.wrapper.view.$el.find('.cp-details-about div.contact-person > div:nth-child(2)').data('model'), name);
        }
    },
    //Remove client from template
    removeClient: function () {
        if (this.wrapper.view.$el.find('.cp-details-about div.client').length) {
            let client = this.wrapper.view.$el.find('.cp-details-about div.client').data('model');
            if (client) {
                client.collection.remove(client);
            }
        }
    },
    //Add client to template
    addClient: function () {
        let clientBlock = this.editor.BlockManager.get('client');
        if (clientBlock) {
            if (!this.wrapper.view.$el.find('.cp-details-about div.client').length) {
                let modelDetail = this.wrapper.view.$el.find('.cp-details-about').data('model');
                if (modelDetail) {
                    modelDetail.get('components').add(clientBlock.get('content'));
                }
            }
        }
    },
    //Remove client from template
    removeContactPerson: function () {
        if (this.wrapper.view.$el.find('.cp-details-about div.contact-person').length) {
            let contactPerson = this.wrapper.view.$el.find('.cp-details-about div.contact-person').data('model');
            if (contactPerson) {
                contactPerson.collection.remove(contactPerson);
            }
        }
    },
    //Add client to template
    addContactPerson: function () {
        let contactPersonBlock = this.editor.BlockManager.get('contact-person');
        if (contactPersonBlock) {
            if (!this.wrapper.view.$el.find('.cp-details-about div.contact-person').length) {
                let modelDetail = this.wrapper.view.$el.find('.cp-details-about').data('model');
                if (modelDetail) {
                    modelDetail.get('components').add(contactPersonBlock.get('content'));
                }
            }
        }
    },
    //Check if client added to template
    clientExist: function () {
        return this.wrapper.view.$el.find('.cp-details-about div.client').length > 0;
    },
    //Check if client added to template
    contactPersonExist: function () {
        return this.wrapper.view.$el.find('.cp-details-about div.contact-person').length > 0;
    },
    //
    loadContactPerson: function (id) {
        let $el = $(this.editor.Panels.getPanel('views-container').get('appendContent')),
            contactPerson = this.getCpSettings('contactPerson');

        if (id) {
            window.axios.get('/client/' + id + '/json')
                .then((response) => {
                    let data = $.map(response.data.contact_person_relation, function (item) {
                        if (item.client_relation) {
                            return {
                                id: item.id,
                                text: item.client_relation.displayName
                            }
                        }
                    });
                    //init client select
                    $el.find('select#contact-person').html('').select2({
                        dropdownParent: $el.find('select#contact-person').parent(),
                        placeholder: 'Выберите контактное лицо',
                        data: data
                    }).on("change", e => {
                        this.updateCpSettings({ contactPerson: { id: $(e.target).val(), name: $(e.target).find('option:selected').text() } });
                        //Update contact person for template
                        this.updateContactPerson();
                    }).val(contactPerson.id).trigger('change');

                })
                .catch((error) => {
                    window.message({ text: error.response.data.errors, error: true });
                });
        }
        //init client select
        $el.find('select#contact-person').html('').select2({
            dropdownParent: $el.find('select#contact-person').parent(),
            placeholder: 'Выберите контактное лицо',
        });
    },
    //Update CP settings for variant
    updateCpSettingsVariant: function (params) {
        let wrapper = this.wrapper,
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $navTabActive = wrapper.view.$el.find('ul.nav-tabs li.active'),
            aModel = wrapper.view.$el.find('ul.nav-tabs li.active a').data('model'),
            traitActivity = false,
            product = [],
            field = [],
            tax = 0,
            specialDiscount = [],
            recommended = $navTabActive.find('span.label_top_recomended').length ? 1 : 0;

        if (params) {
            //If update product for variant
            if (params.product) {
                product = params.product;
            }
            //If update fields for variant
            if (params.field) {
                field = params.field;
            }
            //If update fields for variant
            if (params.tax) {
                tax = params.tax;
            }
            //If update special discount for variant
            if (params.specialDiscount) {
                specialDiscount = params.specialDiscount;
            }
            if (params.variantId) {
                $navTabActive = wrapper.view.$el.find('.nav-tabs a[data-db-id="' + params.variantId + '"]');
                $activeTab = wrapper.view.$el.find('.tab-content .tab-pane' + $navTabActive.attr('href'));
                aModel = $activeTab.find('a').data('model');
                $navTabActive = $navTabActive.parent();
            }
            if (params.recommended) {
                recommended = params.recommended;
            }
        }

        //Get traits model settings
        aModel.get('traits').length && aModel.get('traits').each(function (model) {
            if (model.get('name') === 'activity') {
                //fix for default property
                if (typeof model.get('checked') === 'undefined' && model.get('default')) {
                    model.set('checked', model.get('default'));
                }
                traitActivity = model.get('checked') && model.get('checked') == 'yes';

            }
        });
        //For first open template
        if (!aModel.get('traits').length) {
            traitActivity = true;
        }

        //get total cost
        let totalCost = this.parseI($activeTab.find('.price-decoration .tab-pane-inner__price-cell_finally span').data('model').get('content')),
            name = $navTabActive.find('.kp10-header-name').text(); //$navTabActive.find('.kp10-header-name').data('model').get('content');

        //Store settings variants
        let variant = {
            products: [],
            fields: []
        };
        //Find current variant by index
        this.config.offer.variants.forEach((v, index) => {
            if (index === $navTabActive.index()) {
                variant = v;
            }
        });
        //Add passed data
        variant.active = traitActivity ? 1 : 0;
        variant.tax = tax;
        variant.price = totalCost;
        variant.name = name;
        variant.recommended = recommended;

        let settingsVariant = this.getCpSettings('variants');

        if (settingsVariant) {

            let exist = false;
            settingsVariant.forEach((element) => {
                if (element.id === variant.id) {
                    element.price = variant.price;
                    element.name = variant.name;
                    element.selected = variant.selected;
                    element.active = variant.active;
                    element.tax = variant.tax;
                    element.recommended = variant.recommended;
                    exist = true;

                    if (!element.products) {
                        element.products = [];
                    }
                    if (!element.fields) {
                        element.fields = [];
                    }
                    if (!element.special_discounts) {
                        element.special_discounts = [];
                    }
                    //Fields
                    if (element.fields.length && field.length != 0) {
                        //Find stored field
                        let storedField = element.fields.filter((el) => {
                            return (field['db-id'] && el['db-id'] == field['db-id']) || el.index == field.index
                        });

                        if (storedField.length) {
                            storedField = storedField[0];

                            storedField.name = field.name;

                        } else {
                            //For new field
                            element.fields.push({
                                'db-id': 0,
                                name: field.name,
                                index: field.index,
                                type: field.type
                            })
                        }
                    }
                    //Products
                    if (element.products.length && product.length != 0) {
                        //Find stored product
                        let storedProduct = element.products.filter((el) => {
                            return (product['db-id'] && el['db-id'] == product['db-id']) || el.fakeProductId == product.fakeProductId
                        });

                        //If product saved
                        if (storedProduct.length) {
                            storedProduct = storedProduct[0];

                            if (product.image) {
                                storedProduct.image = product.image;
                            }
                            if (product.description) {
                                storedProduct.description = product.description;
                            }
                            if (product.index) {
                                storedProduct.index = product.index;
                            }

                            //Update field values
                            if (product.values) {

                                product.values.forEach(value => {
                                    let storedValue = storedProduct.values.find((el) => {
                                        return (value['db-id'] && el['db-id'] == value['db-id']) || el.index == value.index;
                                    });
                                    //Update existed
                                    if (storedValue) {
                                        // storedValue = storedValue[0];

                                        storedValue.value = value.value;
                                        storedValue.index = value.index;
                                        storedValue.valueInPrice = value.valueInPrice;
                                    } else { //Create
                                        storedProduct.values.push({
                                            'db-id': value['db-id'],
                                            index: value.index,
                                            value: value.value,
                                            valueInPrice: value.valueInPrice,
                                            type: value.type
                                        });
                                    }
                                });
                            }
                        } else {
                            element.products.push({
                                'db-id': 0,
                                id: product.id || 0,
                                group: product.group || 0,
                                description: product.description || '',
                                fakeProductId: product.fakeProductId || '',
                                image: product.image || '',
                                index: product.index || 0,
                                values: product.values || [],
                            });
                        }
                    }//products

                    //Special discount
                    if (element.special_discounts.length && specialDiscount.length != 0) {
                        //Find stored special discount
                        let storedSDiscount = element.special_discounts.filter((el) => {
                            return (specialDiscount['db-id'] && el['db-id'] == specialDiscount['db-id']) || el.index == specialDiscount.index
                        });

                        if (storedSDiscount.length) {
                            storedSDiscount = storedSDiscount[0];

                            storedSDiscount.index = specialDiscount.index;
                            storedSDiscount.name = specialDiscount.name;
                            storedSDiscount.value = specialDiscount.value;
                        }
                    }
                } else { //Other variants
                    if (recommended === true || recommended === false) {
                        //Reset recommended
                        element.recommended = false;
                    }

                }
            });

            if (!exist) {
                //Add new one
                settingsVariant.push(variant);
            }

            //Update settings
            this.updateCpSettings({
                variants: settingsVariant
            });

        } else {

            variant.products = [product];
            variant.fields = [field];

            //Update settings
            this.updateCpSettings({
                variants: [variant]
            });
        }
    },
    //Update CRM field
    updateCpSettingsIntegrationField: function (params) {
        let integration = this.getCpSettings('integration') || { dealFields: [] };

        //Integration fields
        if (config.integration && config.integration.fields) {
            let dealFields = integration.dealFields || [];

            //Megaplan
            if (config.integration.system_crm_id === 1) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.field_id == params.type);
                if (field) {
                    let exist = dealFields.find(d => d.id == field.id);
                    if (exist) {
                        exist.value = params.value;
                    } else {
                        dealFields.push({ id: field.id, value: params.value });
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }
            //Amocrm
            if (config.integration.system_crm_id === 2) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.amocrm_field_id == params.type);
                if (field) {
                    let exist = dealFields.find(d => d.id == field.id);
                    if (exist) {
                        exist.value = params.value;
                    } else {
                        dealFields.push({ id: field.id, value: params.value });
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }
            //Bitrix24
            if (config.integration.system_crm_id === 3) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.bitrix24_field_id == params.type);
                if (field) {
                    let exist = dealFields.find(d => d.id == field.id);
                    if (exist) {
                        exist.value = params.value;
                    } else {
                        dealFields.push({ id: field.id, value: params.value });
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }

            //Update settings
            this.updateCpSettings({
                integration: {
                    dealFields: dealFields
                }
            });

        }
    },
    //Remove CRM field
    removeCpSettingsIntegrationField: function (fieldId) {
        let integration = this.getCpSettings('integration') || { dealFields: [] };

        //Integration fields
        let dealFields = integration.dealFields || [];

        if (dealFields.length) {
            for (let i = dealFields.length - 1; i >= 0; i--) {
                if (dealFields[i].id == fieldId) {
                    //Remove field
                    dealFields.splice(i, 1);
                }
            }
            // Update settings
            this.updateCpSettings({
                integration: {
                    dealFields: dealFields
                }
            });
        }
    },
    parseIntegrationFieldValue: function (type, value) {
        //Integration fields
        if (config.integration && config.integration.fields) {
            //Megaplan
            if (config.integration.system_crm_id === 1) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.field_id == type);
                if (field) {
                    //Numeric
                    if (field.content_type == 'MoneyField' || field.content_type == 'FloatField') {
                        value = this.parseF(value);
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }
            //Amocrm
            if (config.integration.system_crm_id === 2) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.amocrm_field_id == type);
                if (field) {
                    //Numeric
                    if (field.amocrm_field_type_id == 2) {
                        value = this.parseF(value);
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }
            if (config.integration.system_crm_id === 3) {
                let field = config.integration.fields.find(f => 'integration-field-' + f.bitrix24_field_id == type);
                if (field) {
                    //Numeric
                    if (field.bitrix24_field_type_id == 'double' || field.bitrix24_field_type_id == 'money') {
                        value = this.parseF(value);
                    }
                } else {
                    console.error('Integration field not found: ' + params.type);
                }
            }

            return value;
        }
    },
    //If variant selected, disable other
    updateVariants: function () {
        let wrapper = this.wrapper,
            $navTab = wrapper.view.$el.find('ul.nav-tabs li'),
            index = -1,
            classDisabled = 'disabled-variant-selected',
            $a = wrapper.view.$el.find('ul.nav-tabs li.active a');

        if (!$a.length) {
            console.warn('Application does not initialized!');
            return;
        }

        let aModel = $a.data('model');

        this.config.offer.variants.forEach((variant, i) => {
            if (variant.selected === 1) {
                index = i;
            }
        });

        let selected = index !== -1,
            traitRecommended = false,
            traitActivity = false;

        //Get traits model settings
        aModel.get('traits').each(function (model) {
            if (model.get('name') === 'recommended') {
                traitRecommended = model.get('checked');
            }
            if (model.get('name') === 'activity') {
                traitActivity = model.get('checked');
            }
        });

        if (index !== -1) {

            //Set label, remove recommended
            if ($navTab.find('.label_top_recomended').length) {
                $navTab.find('.label_top_recomended').data('model').destroy();
            }

            //Add disabled class for variants
            $navTab.data('model').collection.models.forEach((liModel, i) => {

                if (i !== index) {
                    let exist = liModel.attributes.classes.models.filter(function (cls) {
                        return cls.get('name') === classDisabled
                    }).length;

                    if (!exist) {
                        //Disable variant
                        liModel.attributes.classes.add({ name: classDisabled });
                    }
                } else {//For selected variant

                    let aModel = liModel.view.$el.children('a');
                    //Add selected label
                    aModel.data('model').get('components').add('<span data-gjs-type="fake" class="label_top corporate-bg-color">Выбран</span>');
                    //Set active
                    aModel.trigger('click');
                }

                //Disable button
                let href = liModel.view.$el.children('a').attr('href');

                wrapper.view.$el.find('.tab-content ' + href + ' button.button-choose').data('model').attributes.classes.add({ name: 'disabled' });

            });
        }

    },
    //Check if variant selected
    isVariantSelected: function () {
        return this.config.offer.variants.filter((variant, i) => {
            return variant.selected === 1;
        }).length > 0;
    },
    //Modal
    showVariantSelectedModal: function () {
        let modal = this.editor.Modal;

        //show message
        modal.setTitle('Редактирование заблокировано');
        modal.setContent(
            '<p>В данном КП вариант уже выбран менеджером или клиентом</p>' +
            '<p>Для внесения изменений в КП необходимо снять выбор варианта</p>' +
            '<p>После внесенных изменений в КП, выбор варианта будет доступен менеджеру и клиенту</p>' +
            '<button class="gjs-btn-prim" id="cancel-variant-selection"><i class="fa fa-undo"></i> Снять выбор варианта</button>'
        );
        modal.open();
    },
    //Set variant selected
    setVariantSelected: function (trait, selected) {
        let wrapper = this.wrapper,
            $navTab = wrapper.view.$el.find('ul.nav-tabs li'),
            $navTabActive = wrapper.view.$el.find('ul.nav-tabs li.active'),
            classDisabled = 'disabled-variant-selected',
            index = $navTabActive.index(),
            text = selected ? 'Вы уверены что хотите выбрать вариант коммерческого предложения' : 'Вы уверены что хотите отменить выбор варианта коммерческого предложения';

        window.confirm(text, ($modal) => {
            //hide modal
            $modal.modal('hide');

            let url = '';

            if (selected) {
                url = '/' + this.config.offer.url + '/variant';
            } else {
                url = location.href + '/cancel-variant-selection';
            }

            axios.post(url, { index: index })
                .then((response) => {
                    //For cancel selected variant
                    if (!selected) {
                        //Clear data
                        editor.UndoManager.clear();
                        // //reload page
                        location.reload();
                        return;
                    }

                    //Change variant status
                    this.config.offer.variants.forEach((variant, i) => {
                        if (i === index) {
                            variant.selected = selected ? 1 : 0;
                        }
                    });

                    //Trying disable selected variant
                    if (!selected) {

                        //Add disabled class for variants
                        $navTab.data('model').collection.models.forEach((liModel, i) => {
                            //Remove disabled class
                            liModel.attributes.classes.each(function (cls) {
                                if (cls.get('name') === classDisabled) {
                                    cls.collection.remove(cls);
                                }
                            });
                            //Remove selected label block
                            let aModel = liModel.view.$el.children('a').data('model');
                            aModel.get('components').each(function (model) {
                                if (model.get('type') === 'fake') {
                                    model.destroy();
                                }
                            });

                            //Enable button
                            let href = liModel.view.$el.children('a').attr('href');

                            wrapper.view.$el.find('.tab-content ' + href + ' button.button-choose').data('model')
                                .attributes.classes.each(function (cls) {
                                    if (cls.get('name') === 'disabled') {
                                        //Remove disabled class
                                        cls.collection.remove(cls);
                                    }
                                });

                        });
                    }
                    //update view
                    this.updateVariants();
                })
                .catch(function (error) {
                    window.message({ text: error.response.data.errors, error: true });
                });
        }, () => { //cancel
            //Cancel click
            trait.setValue('no');
            trait.model.set('checked', 'no');
        });
    },
    //Update template number
    updateTemplateNumber: function () {
        let number = this.config.offer.number,
            create = this.config.offer.created_at_formatted,
            update = this.config.offer.updated_at_formatted.split(' '),
            $cpNumber = this.wrapper.view.$el.find('.cp-details__number');

        if (!$cpNumber.length) {
            console.warn('Application does not initialized!');
            return;
        }

        this.updateContent($cpNumber.data('model'),
            '№' + number + ' от ' + create + ' (обновлено ' + update[1] + ' в ' + update[0] + ')');
    },
    //Remove products from active variants
    removeProducts: function () {
        let wrapper = this.wrapper,
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active');

        window.confirm('Вы уверены что хотите удалить все товары и группы из выбранного варианта?', ($modal) => {
            //hide modal
            $modal.modal('hide');
            if ($activeTab.find('.tab-pane-inner__menu .row').length) {
                let rowModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
                    removeModel = [];

                rowModel.collection.each((model, i) => {
                    if (model && !model.view.$el.hasClass('tab-pane-inner__menu-row-heading')) {
                        //Marker for massive remove
                        // model.set('removeProducts', true);
                        //Create array for removing model
                        removeModel.push(model);
                    }
                });

                for (let i in removeModel) {
                    //Remove models
                    removeModel[i].collection.remove(removeModel[i]);

                    // this.calculatePositionsPrices();
                    //Mark product for remove from DB
                    // this.updateCpSettingsVariantProducts({ delete: { product: { 'db-id': removeModel[i].view.$el.attr('data-db-id') } } });
                }

                // // this.calculatePositionsPrices();
            }
        });
    },
    //Show file in Assets with sorting
    showAmFolder: function (path) {
        // Get the Asset Manager module first
        let am = this.editor.AssetManager;
        let files = this.editor.AssetManager.getAll().filter((asset) => {
            //For images
            if (asset.get('type') === 'image') {
                return asset.get('folder') === path && asset.get('cropped') == 1;
            }
            return asset.get('folder') === path;
        });

        let fixedFolder = [
            "Галерея",
            "Товары",
            "Логотипы",
            "Обложки",
            "Сотрудники"
        ];

        //Sort files with rules
        files.sort(function (a, b) {
            //Exclude system folders
            if (
                fixedFolder.indexOf(a.get('name')) === -1 ||
                fixedFolder.indexOf(b.get('name')) === -1
            ) {
                if (a.get('name') > b.get('name')) return 1;
                if (a.get('name') < b.get('name')) return -1;
                return 0;
            }
        });

        am.render(files);
    },
    //Replace new line with br tag
    nlToBr(str) {
        return str && str.replace(/(?:\r\n|\r|\n)/g, '<br>');
    },

    //Update employee from settings
    updateEmployee: function () {

        let $wrapper = this.wrapper.view.$el,
            $personMessage = $wrapper.find('.person.message__person'),
            $personContainer = $personMessage.children('.person-container');

        if (!$personContainer.length) {
            console.warn('Application does not initialized!');
            return;
        }

        let employeeId = parseInt($personContainer.data('id'));

        //If user not same as in offer
        if (this.config.offer.employee.user_id !== employeeId) {
            let employee = this.editor.BlockManager.getAll().filter((block) => {
                return block.id === 'employee' + employeeId
            });

            if (employee.length) {
                //update in config
                this.config.offer.employee.user_id = employeeId;

                employee = employee[0];
                //Add new model on the page. Afte that grapesjs trigger component:add look at index.js type = employee-signature
                $personMessage.data('model').get('components').add(employee.get('content'));

                //If this is NOT system offer template
                // if (!this.config.offer.system) {
                //     //Show message after save html
                //     this.editor.saveHtmlshowMessage = false;
                //     //Save template
                //     this.editor.Commands.get('storeData').run(this.editor);
                // }
            }
        }
    },
    //Save html of the template
    storeHtml: function (callback, id) {
        //Save offer html template 
        let $wrapper = this.editor.DomComponents.getWrapper().view.$el,
            offerId = id ? id : this.config.offer.id;

        if (offerId) {
            //set active first visible tab
            //fix for disabled variant
            $wrapper.find('.nav-tabs > li:not(.disabled-variant) > a')[0].click();

            let html = $wrapper.closest('html').clone();
            //clean up from gjs classes
            $(html).find('.gjs-comp-selected').removeClass('gjs-comp-selected');
            //fix for shooth loading
            $(html).find('#wrapper').addClass('display-none production'); //
            //Add html
            $(html).find('head').append(`
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
                <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
                <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
                <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
                <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
                <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
                <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
                <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
                <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
                <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
                <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
                <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
                <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
                <link rel="manifest" href="/manifest.json">
                <meta name="msapplication-TileColor" content="#ffffff">
                <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
                <meta name="theme-color" content="#ffffff">

                `); //TODO: РЕАЛІЗУВАТИ ТЕГИ og ДЛЯ ТЕЛЕГРАМ

            let blob = new Blob(['<html>' + $(html).html() + '</html>'], { type: "text/plain;charset=utf-8" });

            var data = new FormData();
            data.append('_token', _csrf);
            data.append('id', offerId);
            data.append('blob', blob);

            $.ajax({
                url: "/editor/html",
                data: data,
                type: "POST",
                processData: false,
                contentType: false,
                success: (response) => {
                    //Clear data//prevent show confim dialogue
                    this.editor.UndoManager.clear();

                    if (callback) {
                        callback(response);
                    }
                },
                error: function (response) {
                    window.message({ text: response.responseJSON.errors, error: true });
                }
            });
        }
    },
    //Validate email
    validateEmail: function (email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },
    //Trim string
    trimStr: function (str) {
        if (str == null) return str;
        return str.replace(/^\s+|\s+$/g, '');
    },
    //Update products attr id after save in db
    updateVariantsProducts: function (params) {
        let updateId = params && params.updateId,
            needUpdate = false;
        if (this.config) {
            if (this.config.offer.variants) {
                let $wrapper = this.wrapper.view.$el;
                this.config.offer.variants.forEach((variant, index) => {
                    //nav-tabs
                    let $tab = $wrapper.find('.nav-tabs li:nth-child(' + (index + 1) + ') a');
                    if ($tab.length) {
                        let tabModel = $tab.data('model'),
                            $tabContent = $wrapper.find('.tab-content .tab-pane' + $tab.attr('href'));

                        if (tabModel) {
                            //Update id
                            if ($tab.attr('data-db-id') != variant.id) {
                                tabModel.addAttributes({ 'data-db-id': variant.id });
                                needUpdate = true;
                            }
                        } else {
                            console.error('Model not found');
                        }

                        if (variant.fields.length) {
                            variant.fields.forEach((field, fIndex) => {
                                //Find field
                                let $field = $tabContent.find('.row.tab-pane-inner__menu-row-heading > div:nth-child(' + (field.index + 1) + ')');
                                if ($field.length) {
                                    //Exclude field with id
                                    if (!$field.attr('data-db-id') || updateId || needUpdate) {
                                        let fieldModel = $field.data('model');
                                        if (fieldModel) {
                                            fieldModel.addAttributes({ 'data-db-id': field.id });
                                        } else {
                                            console.error('Model not found');
                                        }
                                    }
                                }
                            });
                        }

                        if (variant.products) {
                            variant.products.forEach((product, pIndex) => {
                                //If index not isset. For deleted products
                                if (!product.index) {
                                    return;
                                }
                                //Find product
                                let $product = $tabContent.find('.row.tab-pane-inner__menu-row:nth-child(' + (product.index + 1) + ')');
                                if ($product.length) {
                                    //Exclude product with id
                                    let check = !$product.attr('data-db-id') || updateId || ($product.attr('data-db-id') && $product.attr('data-db-id') == '0') || needUpdate;
                                    if (check) {
                                        let productModel = $product.data('model');
                                        if (productModel) {
                                            productModel.addAttributes({ 'data-db-id': product.id });
                                        } else {
                                            console.error('Model not found');
                                        }
                                    }

                                    //Update db-id for field values
                                    if (product.values) {

                                        product.values.forEach((value, vIndex) => {
                                            let $value = $product.children().eq(value.index);
                                            let check = !$value.attr('data-db-id') || updateId || ($value.attr('data-db-id') && $value.attr('data-db-id') == '0') || needUpdate;
                                            if (check) {
                                                if ($value.length) {
                                                    let valueModel = $value.data('model');
                                                    if (valueModel) {
                                                        valueModel.addAttributes({ 'data-db-id': value.id });
                                                    } else {
                                                        console.error('Model not found');
                                                    }
                                                } else {
                                                    console.error('Value not found', value.id);
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }

                        if (variant.special_discounts) {
                            variant.special_discounts.forEach((sDiscount, sIndex) => {
                                //Find special discount
                                let $sDiscount = $tabContent.find('.kp10-special-discount > div:nth-child(' + (sDiscount.index + 1) + ')');

                                if ($sDiscount.length) {
                                    let check = !$sDiscount.attr('data-db-id') || updateId || ($sDiscount.attr('data-db-id') && $sDiscount.attr('data-db-id') == '0') || needUpdate;
                                    if (check) {
                                        let sDiscountModel = $sDiscount.data('model');
                                        if (sDiscountModel) {
                                            sDiscountModel.addAttributes({ 'data-db-id': sDiscount.id });
                                        } else {
                                            console.error('Model not found');
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }

        return needUpdate;
    },
    //Update cp settings for variant products
    //Get current values from page
    updateCpSettingsVariantProducts: function (params) {
        let settingsVariants = [],
            variants = this.config.offer.variants,
            wrapper = this.wrapper,
            $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
            // discountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index(),
            // priceWithDiscountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price-with-discount').index(),
            _this = this;

        //Find all tab contents
        wrapper.view.$el.find('.tab-content > .tab-pane[role="tabpanel"]').each(function () {

            let $currentTab = wrapper.view.$el.find('#' + $(this).attr('id') + '.tab-pane[role="tabpanel"]'),
                tabIndex = $(this).index(),
                fields = [],
                products = [],
                tax = 0,
                specialDiscount = [];

            //Find all columns
            $(this).find('.tab-pane-inner__menu-header-cell').each(function () {
                let name = $(this).text().trim(),
                    index = $(this).index(),
                    id = $(this).attr('data-db-id'),
                    type = _this.getElementType($(this));

                fields.push({
                    name: name,
                    index: index,
                    'db-id': id ? id : 0,
                    type: type
                });
            });

            //Find all products
            $(this).find('.row.tab-pane-inner__menu-row').each(function () {
                //First row with column names
                if (!$(this).hasClass('tab-pane-inner__menu-row-heading')) {
                    let $this = $(this);

                    let fakeProductId = $this.data('src'),
                        product = {};

                    //Add index for ordering
                    product.index = $this.index();
                    product['db-id'] = $this.attr('data-db-id') ? $this.attr('data-db-id') : 0;
                    product.id = $this.attr('data-id') ? $this.attr('data-id') : 0;
                    //Product
                    if (!$this.find('.pane-title').length) {
                        product.fakeProductId = fakeProductId;

                        //Find product modal
                        let $hiddenProduct = wrapper.view.$el.children(fakeProductId);
                        if ($hiddenProduct.length) {
                            product.description = $hiddenProduct.find('.modal-product__info > div').text().trim();
                        }

                        //Init
                        if (!product.values) {
                            product.values = [];
                        }

                        //Get column data
                        $this.children().each(function (i, t) {

                            let $this = $(this),
                                id = $this.attr('data-db-id'),
                                type = _this.getElementType($this),
                                valueInPrice = false;

                            //Only for good coll
                            if (type == 'good-coll') {
                                let $goodColl = $currentTab.find('.tab-pane-inner__menu-row.js-fancybox-offer[data-src="' + fakeProductId + '"] > div:nth-child(' + (i + 1) + ')');
                                if ($goodColl.length) {
                                    let goodModel = $goodColl.data('model');
                                    if (goodModel) {
                                        valueInPrice = goodModel.get('valueInPrice') ? 1 : 0;
                                    }
                                }
                            }

                            let fieldValue = {
                                'db-id': id ? id : 0,
                                index: i,
                                value: $(this).text().trim(),
                                type: type,
                                valueInPrice: valueInPrice
                            };

                            //If this is name row
                            if ($(this).hasClass('kp10-cell-name')) {
                                //Add image for product
                                product.image = $(this).find('img').attr('src');
                            }

                            product.values.push(fieldValue);
                        });

                        //For remove field
                        if (params && params.delete && params.delete.values) {
                            params.delete.values.forEach((el) => {
                                if (product['db-id'] == el['product-id']) {
                                    if (el['db-id']) {
                                        product.values.push({
                                            'db-id': el['db-id'],
                                            delete: true
                                        });
                                    }
                                }
                            });
                        }

                    } else { //Group
                        let $clone = $($this.find('.pane-title').clone());
                        $clone.find('.kp10-group-cost').remove();

                        let $name = $(this).children('div').first(),
                            id = $name.attr('data-db-id'),
                            type = _this.getElementType($name),
                            name = $clone.text().trim();

                        product.group = 1;
                        // product.name = _this.trimStr(name.substr(0, name.length - 2));
                        //Init
                        if (!product.values) {
                            product.values = [];
                        }

                        product.values.push({
                            'db-id': id ? id : 0,
                            index: 0,
                            value: _this.trimStr(name.substr(0, name.length - 2)),
                            type: type,
                            valueInPrice: false
                        });
                    }

                    products.push(product);
                }
            }); //end products

            //Find tax
            let $button = $currentTab.find('.price-decoration .button-choose');
            if ($button.length) {
                let buttonModel = $button.data('model');

                if (buttonModel) {
                    buttonModel.get('traits').each(function (traitModel) {
                        if (traitModel.get('name') === 'variant-tax') {
                            tax = traitModel.get('selected') || 0;
                        }
                    });
                }
            }

            //Find special discount
            $(this).find('.kp10-special-discount').children().each(function () {
                let $this = $(this),
                    id = $this.attr('data-db-id'),
                    index = $this.index(),
                    name = _this.trimStr($this.children().eq(0).text()),
                    value = _this.parseF($this.children().eq(1).text());

                let data = {
                    'db-id': id ? id : 0,
                    index: index,
                    name: name,
                    value: value
                };

                specialDiscount.push(data);
            });

            //Find same variant by index
            variants.filter((variant, index) => {
                if (index === tabIndex) {

                    //For remove field
                    if (params && params.delete && params.delete.fields) {
                        params.delete.fields.forEach((el) => {
                            if (el['db-id']) {
                                fields.push({
                                    'db-id': el['db-id'],
                                    delete: true
                                });
                            }
                        });
                    }

                    //For remove product
                    if (params && params.delete && params.delete.product) {
                        if (params.delete.product['db-id']) {
                            products.push({
                                'db-id': params.delete.product['db-id'],
                                delete: true
                            });
                        }
                    }

                    //For remove elements
                    if ($activeTab.index() === index) {
                        //For remove special discount
                        if (params && params.delete && params.delete.specialDiscount) {
                            if (params.delete.specialDiscount['db-id']) {
                                specialDiscount.push({
                                    'db-id': params.delete.specialDiscount['db-id'],
                                    delete: true
                                });
                            }
                        }
                    }

                    //Add previous deleted products
                    if (variant.products) {
                        variant.products.forEach((product) => {
                            let productExist = products.find((p) => p['db-id'] == product['db-id']);
                            if (product.delete && !productExist) {
                                products.push(product);
                            }
                            //Check product value. Example: column discount
                            if (product.values && productExist) {
                                product.values.forEach((value) => {
                                    if (value.delete) {
                                        let valueExist = productExist.values.find(pv => pv['db-id'] == value['dp-id']);
                                        if (!valueExist) {
                                            productExist.values.push(value);
                                        }
                                    }
                                });
                            }
                        });
                    }


                    //Add previous deleted fields
                    if (variant.fields) {
                        variant.fields.forEach((field) => {
                            if (field.delete && !fields.find((f) => f['db-id'] == field['db-id'])) {
                                fields.push(field);
                            }
                        });
                    }
                    //Add previous deleted special discount
                    if (variant.special_discounts) {
                        variant.special_discounts.forEach((sDiscount) => {
                            //
                            if (sDiscount.delete && !specialDiscount.find((d) => d['db-id'] == sDiscount['db-id'])) {
                                specialDiscount.push(sDiscount);
                            }
                        });
                    }

                    //Update data for config offer
                    variant.fields = fields;
                    variant.products = products;
                    variant.tax = tax;
                    variant.special_discounts = specialDiscount;
                    //Fill for cpSettings
                    settingsVariants.push(variant);
                }
            });
        }); //end wrapper

        //Store for update
        let cpSettingsModel = this.wrapper.view.$el.find('#cp-settings').data('model'),
            cpSettings = cpSettingsModel.get('cp-settings');

        if (!cpSettings) {
            cpSettingsModel.set({ "cp-settings": {} });
            cpSettings = cpSettingsModel.get('cp-settings');
        }
        //Update only fields and products
        if (cpSettings.variants) {
            settingsVariants.forEach((sVariant) => {
                //Find same variant
                let variant = cpSettings.variants.filter((el) => {
                    return el.id == sVariant.id
                })[0];

                if (variant) {
                    variant.fields = sVariant.fields;
                    variant.products = sVariant.products;
                    variant.tax = sVariant.tax;
                    variant.special_discounts = sVariant.special_discounts;
                } else {
                    cpSettings.variants.push(sVariant);
                }
            });

        } else {
            cpSettings.variants = settingsVariants;
        }
    },

    /**
     * Add to cp settings variant updating column, products names
     */
    addValueToCpVariant: function (model) {
        let $el = model.$el;

        //Update column names
        if ($el.hasClass('tab-pane-inner__menu-header-cell')) {
            let id = $el.attr('data-db-id');

            let name = this.trimStr($el.text()),
                field = { 'db-id': id, name: name, index: $el.index(), type: this.getElementType($el) };

            this.updateCpSettingsVariant({ field: field });

        }

        let $tableView = $el.closest('.row.tab-pane-inner__menu-row'),
            $listView = $el.closest('.tab-content__list');

        //When update in table or list view
        if ($tableView.length || $listView.length) {
            //js-fancybox-offer
            let $row = $el.closest('.row.tab-pane-inner__menu-row'),
                $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
                discountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index(),
                priceWithDiscountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price-with-discount').index(),
                fakeProductId = $row.attr('data-src') || '';

            //For second product view
            if ($listView.length) {
                $row = $activeTab.find('.tab-content__table .js-fancybox-offer[data-src="' + $el.closest('a.js-fancybox-offer').attr('data-src') + '"]');
            }

            let product = {
                'db-id': $row.attr('data-db-id') || 0,
                fakeProductId: fakeProductId,
                values: []
            };

            //Walk on field values
            $row.children().each((el, i) => {

                let $this = $(el),
                    id = $this.attr('data-db-id') || 0,
                    index = $this.index(),
                    value = this.trimStr($this.text()),
                    type = this.getElementType($this),
                    valueInPrice = false;

                //Only for good coll
                if (type == 'good-coll') {
                    let $goodColl = $activeTab.find('.tab-pane-inner__menu-row.js-fancybox-offer[data-src="' + fakeProductId + '"] > div:nth-child(' + (i + 1) + ')');
                    if ($goodColl) {
                        let goodModel = $goodColl.data('model');
                        if (goodModel) {
                            valueInPrice = goodModel.get('valueInPrice') ? 1 : 0;
                        }
                    }
                }

                //Update product name
                if ($this.hasClass('kp10-cell-name')) {
                    value = this.trimStr($this.find('span').text());
                }

                product.values.push({
                    'db-id': id,
                    index: index,
                    value: value,
                    type: type,
                    valueInPrice: valueInPrice
                });

            });
            //Update for update
            this.updateCpSettingsVariant({ product: product });
        }

        if ($el.closest('.kp10-row-special-discount').length) {
            let $parent = $el.closest('.kp10-row-special-discount'),
                id = $parent.attr('data-db-id'),
                index = $parent.index(),
                name = this.trimStr($parent.find('span:first-child').text()),
                value = this.parseF($parent.find('span:nth-child(2)').text()),
                specialDiscount = {
                    'db-id': id,
                    index: index,
                    name: name,
                    value: value
                };

            //Update for update
            this.updateCpSettingsVariant({ specialDiscount: specialDiscount });
        }

    },
    //Get type of the elements by class or type attribute
    getElementType: function ($el) {

        let cl = $el && $el.attr('class').match(/(kp10-[a-z-]+)/g),
            type = 'default',
            $currentTab = $el.closest('.tab-content.cp-options__content').find('.tab-pane'),
            discountIndex = $currentTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index(),
            priceWithDiscountIndex = $currentTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price-with-discount').index();

        if (cl) {
            switch (cl[0]) {
                //Columns
                case 'kp10-cell-name':
                    type = 'name';
                    break;
                case 'kp10-cell-count':
                    type = 'count';
                    break;
                case 'kp10-cell-price':
                    type = 'price';
                    break;
                case 'kp10-cell-discount':
                    type = 'discount';
                    break;
                case 'kp10-cell-price-with-discount':
                    type = 'price-with-discount';
                    break;
                case 'kp10-good-coll':
                    type = 'good-coll';
                    break;
                case 'kp10-cell-cost':
                    type = 'cost';
                    break;
            }
        }

        if (type === 'default') {
            if ($el.find('.kp10-cell-price').length) {
                type = this.getElementType($el.find('.kp10-cell-price'));
            }
            //Fix for type
            if ($el.data('gjs-type') === 'discount') {
                if ($el.index() == discountIndex) {
                    type = 'discount';
                }
                if ($el.index() == priceWithDiscountIndex) {
                    type = 'price-with-discount';
                }
            }
            if ($el.find('.kp10-cell-cost').length) {
                type = this.getElementType($el.find('.kp10-cell-cost'));
            }
        }

        return type;
    },
    //Show modal product select
    showProductSelectModal: function (index) {
        let $navTabActive = this.wrapper.view.$el.find('ul.nav-tabs li.active');

        editor.Modal.setTitle(
            '<input type="text" id="product-search" placeholder="Поиск"> ' +
            '<button class="gjs-btn-prim" id="add-new-product"><i class="fa fa-plus" aria-hidden="true"></i> Новый товар</button>' +
            '<span id="added-products" class="gjs-four-color">Добавлено товаров: <span>0</span></span>' +
            '<div class="product-variant-name">Добавление товара в вариант <span class="gjs-four-color">"' + $navTabActive.find('.kp10-header-name').text() + '"</span></div>');

        editor.Modal.setContent('<p>Получение списка товаров...</p>');
        editor.Modal.open();

        //First load run ajax
        $('#product-search').val('').trigger('keyup').attr('data-index', index);
    },
    //Convert currency of the offer
    convertCurrency: function (currency, prevCurrency, baseCurrency) {

        let $wrapper = this.wrapper.view.$el,
            prevRate = prevCurrency.basic == 1 ? 1 : prevCurrency.syncRate,
            rate = baseCurrency.syncRate / (currency.basic == 1 ? 1 : currency.syncRate);

        $wrapper.find('.row.tab-pane-inner__menu-row.js-fancybox-offer').each((el, index) => {
            let $el = $(el),
                fakeProductId = $el.attr('data-src');

            if (!fakeProductId) {
                console.error('FakeProductId not found');
                return;
            }
            let $price = $wrapper.find('.row.tab-pane-inner__menu-row.js-fancybox-offer[data-src="' + fakeProductId + '"] span.kp10-cell-price'),
                priceModel = $price.data('model'),
                price = this.parseF($price.text());

            //Convert to base by prev currency rate
            price = price * prevRate;
            //Convert for selected
            price = price * rate;
            //Round
            price = Math.round(price * 100) / 100;

            this.updateContent(priceModel, price);
            //Update all relative models
            this.updateProductModel(this.prepareModelRteProduct(priceModel.view));
        });

        //Update price for each variant
        $wrapper.find('ul.nav-tabs li > a').each((el) => {
            this.calculatePositionsPrices($(el).data('db-id'));
        });

        this.updateCpSettingsVariantProducts();
    },
    //Change currency on the page
    changeCurrency: function (currency) {

        if (!currency) {
            console.error('Currency undefined');
            return;
        }

        let currencies = this.wrapper.find('i.currency');

        currencies.forEach((model, index) => {
            //Remove old class
            for (let i = model.get('classes').length - 1; i >= 0; i--) {
                let classModel = model.get('classes').at(i);
                if (classModel.get('name').indexOf('fa') !== -1 || classModel.get('name').indexOf('icon') !== -1) {
                    model.get('classes').remove(classModel);
                }
            }
            //Add new class
            model.get('classes').add({ name: 'icon' });
            model.get('classes').add({ name: 'icon-' + currency.charCode.toLowerCase() });
        });
    },
    //Get currency currency and update it on the page
    changeCurrencyFromSettings: function () {
        //TODO: реалізувати розрахунок валюти тільки для вибраного варіанту
        //Update currency
        if (config.offer.currency) {
            let currencySeetings = this.getCpSettings('currency'),
                currency = this.config.currencies.find((item) => {
                    return currencySeetings && currencySeetings.id ? //If default currency not 0
                        item.id == currencySeetings.id : //By currency id
                        item.basic == 1 //Basic currency
                });

            //Update sign
            this.changeCurrency(currency);
        }
    },
    //Remove goods coll
    removeGoodColl: function (model) {
        let $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
            mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
            cellNameWidth = this.parseI($activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').attr('class').match(/col-md-\d/g)[0]) + 1,
            indexForRemove = this.parseF(model.get('indexForRemove')),
            values = [];

        //control for remove child item
        if (!model.get('indexForRemove')) {
            return;
        }

        if (mModel && mModel.collection && mModel.collection.models) {
            //rows
            for (let i in mModel.collection.models) {

                let mComps = mModel.collection.models[i].get('components'),
                    relativeId = mModel.collection.models[i].view.$el.data('src');//hidden extended offer info

                //don't use group title
                if (!mComps.models[0].view.$el.hasClass('pane-title')) {

                    for (let m in mComps.models) {
                        let cellModel = mComps.models[m];

                        let $el = cellModel.view.$el;
                        //update cell-name class
                        if ($el.hasClass('kp10-cell-name')) {
                            //find class
                            let cModel = cellModel.attributes.classes.models.find(function (item) {
                                return item.id.match(/col-md-\d/g);
                            });
                            //remove class
                            cellModel.attributes.classes.remove(cModel);
                            //add class
                            cellModel.attributes.classes.add({ name: 'col-md-' + cellNameWidth });

                        }

                        //remove element
                        if (i != 0 && cellModel.get('type') === 'add-goods-coll' && cellModel.get('parentIndexForRemove') === indexForRemove) {
                            //For remove from DB
                            values.push({
                                'product-id': mModel.collection.models[i].view.$el.attr('data-db-id'),
                                'db-id': cellModel.view.$el.attr('data-db-id'),
                                delete: true
                            });

                            cellModel.destroy();
                        }
                    }

                    //remove element from hidden block
                    if (relativeId) {
                        let $goodsCell = this.wrapper.view.$el.children(relativeId).find('.kp10-goods-coll');

                        //if cell exists
                        if ($goodsCell.children('div').length) {
                            $goodsCell.data('model').get('components').each(function (m) {
                                if (m && m.get('parentIndexForRemove') === indexForRemove) {
                                    m.destroy();
                                }
                            })
                        }
                    }
                }
            }

            //refresh positions prices
            this.calculatePositionsPrices();

            //Mark product for remove from DB
            this.updateCpSettingsVariantProducts({ delete: { fields: [{ 'db-id': model.view.$el.attr('data-db-id'), delete: true }], values: values } });
        }
    },
    //Remove gallery image
    removeGalleryImg: function (model) {
        let galleryId = model.get('attributes')['data-gallery-id'],
            $gallery = this.wrapper.view.$el.find('section[data-gallery-id="' + galleryId + '"]');

        if ($gallery.length) {

            let $galleryItem = $gallery.find('.csslider ul li div.carousel-item'),
                galleryModel = $galleryItem.data('model');

            galleryModel.collection.each(function (model) {
                //find where photo was deleted
                if (model && !model.view.$el.find('img').length) {
                    model.destroy();
                }
            });

            //If image exist in gallery
            if ($galleryItem.find('.csslider ul li div.carousel-item img').length) {
                //update gallery
                setTimeout(() => {
                    this.updateGallery(galleryId);
                }, 100);
            } else {
                window.confirm('Вы уверены что хотите удалить галерею?', ($modal) => {
                    //hide modal
                    $modal.modal('hide');

                    editor.select(this.wrapper);

                    $gallery.data('model').destroy();
                }, () => {
                    //Undo changes
                    editor.UndoManager.undo();
                });
            }
        }
    },
    //Remove slider image
    removeSliderImg: function (model) {
        let galleryId = model.get('attributes')['data-gallery-id'],
            $gallery = this.wrapper.view.$el.find('[data-gallery-id="' + galleryId + '"]');

        if ($gallery.length) {

            let $galleryItem = $gallery.find('ul li'),
                galleryModel = $galleryItem.data('model');

            galleryModel.collection.each(function (model) {
                //find where photo was deleted
                if (model && !model.view.$el.find('img').length) {
                    model.destroy();
                }
            });

            //If image exist in gallery
            if ($gallery.find('ul li').length) {
                //update gallery
                setTimeout(() => {
                    this.updateGallery(galleryId);
                }, 100);
            } else {
                window.confirm('Вы уверены что хотите удалить слайдер?', ($modal) => {
                    //hide modal
                    $modal.modal('hide');

                    editor.select(this.wrapper);

                    $gallery.data('model').destroy();
                }, () => {
                    //Undo changes
                    editor.UndoManager.undo();
                });
            }
        }
    },
    //Remove product component
    removeProduct: function (model) {
        setTimeout(() => {
            if (model.view.attr && model.view.attr['data-src']) {
                let $hiddenOffer = this.wrapper.view.$el.children(model.view.attr['data-src']),
                    $activeTab = this.wrapper.view.$el.find('.tab-content .tab-pane.active'),
                    $tableRow = $activeTab.find('.tab-content__table .js-fancybox-offer[data-src="' + model.view.attr['data-src'] + '"]'),
                    $listRow = $activeTab.find('.tab-content__list .js-fancybox-offer[data-src="' + model.view.attr['data-src'] + '"]');

                //Check if component was realy deleted
                if (!$tableRow.length) {

                    if ($hiddenOffer.length) {
                        $hiddenOffer.data('model').destroy();
                    }
                    if ($listRow.length) {
                        $listRow.parent().data('model').destroy();
                    }

                    this.calculatePositionsPrices();
                    //Mark product for remove from DB
                    this.updateCpSettingsVariantProducts({ delete: { product: { 'db-id': model.view.$el.attr('data-db-id') } } });

                } else { //Component was moved
                    //Check if user trying to add group before
                    if ($tableRow.index() == 0) {
                        editor.UndoManager.undo();
                        return;
                    }
                    this.calculatePositionsPrices();
                    //Fix if component was moved
                    this.updateCpSettingsVariantProducts();
                }
            }
        }, 50);
    },
    //Format date from integration
    formatIntegrationFieldDate: function (field) {
        let formatted = '';
        if (field) {

            let value = '';

            if (field.megaplan_field_values) { //Megaplan
                value = field.megaplan_field_values;
            }
            if (field.amocrm_field_value) { //amoCRM
                value = field.amocrm_field_value;
                //If this is timestamp
                if (value.indexOf('-') === -1) {
                    value *= 1000;
                }
            }
            if (field.bitrix24_field_value) { //bitrix24
                value = field.bitrix24_field_value;
            }

            let date = new Date(value),
                curr_date = date.getDate() + '',
                curr_month = date.getMonth() + 1 + '',
                curr_year = date.getFullYear();

            //Fix for date
            if (curr_date.length == 1) {
                curr_date = '0' + curr_date;
            }
            //Fix for month
            if (curr_month.length == 1) {
                curr_month = '0' + curr_month;
            }

            formatted = curr_date + '.' + curr_month + '.' + curr_year;

            //For Megaplan add time
            if (field.megaplan_field_values && field.field.content_type == 'DateTimeField') {
                let curr_hours = date.getHours() + '',
                    curr_minutes = date.getMinutes() + '';
                //Fix for date
                if (curr_hours.length == 1) {
                    curr_hours = '0' + curr_hours;
                }
                //Fix for month
                if (curr_minutes.length == 1) {
                    curr_minutes = '0' + curr_minutes;
                }

                formatted += ' ' + curr_hours + ':' + curr_minutes;
            }
        }
        return formatted;
    },
    removeTagsFromEditable: function ($el) {
        let $clone = $el.clone();

        $clone.find('*').each(function () {
            if (['BR', 'DIV'].indexOf($(this).prop("tagName")) !== -1) {
                let $this = $(this),
                    dataAttrsToDelete = [],
                    dataAttrs = $(this).get(0).attributes,
                    dataAttrsLen = dataAttrs.length;

                for (i = 0; i < dataAttrsLen; i++) {
                    dataAttrsToDelete.push(dataAttrs[i].name);
                }

                $.each(dataAttrsToDelete, function (index, attrName) {
                    $this.removeAttr(attrName);
                });

            } else {
                $(this).replaceWith($(this).text());
            }
        });

        return $clone.html();
    }

};