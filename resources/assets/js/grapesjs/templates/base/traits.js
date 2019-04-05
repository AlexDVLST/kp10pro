export default (editor, config = {}, F) => {
    let wrapper = editor.DomComponents.getWrapper();

    //Change traits label
    editor.TraitManager.getTraitsViewer().config.labelContainer = 'Настройки элементов';

    // Each new type extends the default Trait
    editor.TraitManager.addType('discount', {
        events: {
            'change': 'onChange',  // trigger parent onChange method on keyup
        },

        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            md.off('change:value', this.onValueChange);
            this.listenTo(md, 'change:value', this.onValueChange);

            this.className = 'gjs-sm-property gjs-sm-composite';
            this.inputhClass = 'kp10-trt-container';
            this.tmpl = '<div class="gjs-sm-properties">' +
                '<div class="gjs-sm-label"><span class="gjs-sm-icon">' + this.getLabel() + '</span></div>' +
                '<div class="gjs-fields">' +
                '<div class="gjs-sm-field">' +
                '<div class="gjs-field gjs-field-integer">' +
                '<span class="gjs-input-holder  ' + this.inputhClass + '"></span>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
        },

        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {
            if (!this.inputEl) {
                let value = this.model.get('value') ? this.model.get('value') : 0;

                this.inputEl = $('<input type="text" value="' + (value) + '" >');
            }
            return this.inputEl.get(0);
        },

        renderLabel() {
        },

        /**
         * Triggered when the value of the model is changed
         */
        onValueChange: function () {

            let discount = F.numberFormat(F.parseI(this.model.get('value')));
            //find model
            let $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
                positions = $activeTab.find('.tab-pane-inner__menu-row').data('model'),
                discountIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-discount').index();
            //Change value for discount on each position
            positions.collection.each(function (m, i) {
                if (i !== 0 && !m.view.$el.find('.pane-title').length) {
                    F.updateProductModel({
                        relativeId: m.view.$el.data('src'),
                        discount: discount
                    })
                }
            });
            //update position discount
            F.calculatePositionsPrices();
        }
    });
    //
    editor.TraitManager.addType('checkbox', {
        events: {
            'change': 'onChange',  // trigger parent onChange
        },
        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.className = 'gjs-sm-property gjs-sm-composite';
            this.inputhClass = 'gjs-input-holder';

            //copy value only if property not exists
            if (typeof this.model.get('checked') === 'undefined' && md.get('default')) {
                this.model.set('checked', md.get('default'));
            }

            this.tmpl = '<div class="gjs-sm-properties">' +
                '<div class="gjs-sm-label"><span class="gjs-sm-icon">' + this.getLabel() + '</span></div>' +
                '<div class="gjs-field gjs-field-checkbox"><label class="' + this.inputhClass + '"><i class="gjs-chk-icon"></i></label></div>' +
                '</div>';
        },

        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {

            if (!this.inputEl) {
                //get status from target model
                let checked = this.model.get('checked') ? 'checked' : '';
                //check if component added to variant
                if (this.target.get('type') === 'variant' && this.model.get('attributes').id === 'recommended') {
                    checked = this.target.view.$el.find('span.label_top_recomended').length ? 'checked' : '';
                }
                //advantage type
                if (this.target.get('type') === 'advantage') {
                    checked = this.target.view.$el.closest('.advantage-block').hasClass('col-md-12') ? 'checked' : '';
                }

                //Variant
                if (this.target.get('type') === 'variant' && this.model.get('name') === 'selected') {
                    let variant = config.offer.variants.filter((model, i) => {
                        //Find variant by index
                        return i === this.target.view.$el.parent().index()
                    });

                    if (variant.length) {
                        checked = variant[0].selected === 1 ? 'checked' : '';
                    }
                }

                this.inputEl = $('<input type="checkbox" placeholder="" ' + checked + '>');
            }
            return this.inputEl.get(0);
        },

        renderLabel() {
        },
        onChange() {
            let checked = this.getInputEl().checked,
                type = this.model.target.get('type');
            //save status to target model
            this.model.set('checked', checked);

            //Advantage type
            if (type === 'advantage') {
                F.updateAdvantageClasses(this.model, checked);
            }
        },
    });
    //
    editor.TraitManager.addType('number', {
        events: {
            'keyup': 'onChange',  // trigger parent onChange
        },
        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.className = 'gjs-sm-property gjs-sm-composite';
            this.inputhClass = 'gjs-input-holder';

            this.tmpl = '<div class="gjs-sm-properties">' +
                '<div class="gjs-sm-label"><span class="gjs-sm-icon">' + this.getLabel() + '</span></div>' +
                '<div class="gjs-fields">' +
                '<div class="gjs-sm-field">' +
                '<div class="gjs-field gjs-field-integer">' +
                '<span class="gjs-input-holder  ' + this.inputhClass + '"></span>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
        },

        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {

            if (!this.inputEl) {
                let value = this.model.get('value') ? this.model.get('value') : 0;

                this.inputEl = $('<input type="text" value="' + (value) + '" >');
            }
            return this.inputEl.get(0);
        },

        renderLabel() {
        },
        onChange() {
            let value = F.parseI(this.getInputEl().value);

            this.model.set('value', value);

            if (this.target.get('type') === 'add-special-discount') {
                this.target.view.$el.find('.kp10-special-discount-value').data('model').set('content', F.numberFormat(value));
                //update position discount
                F.calculatePositionsPrices();
                //Update value in cpSettings
                F.addValueToCpVariant({ $el: this.target.view.$el });
            }
        },
    });
    //
    editor.TraitManager.addType('button', {
        events: {
            'click': 'onChange',  // trigger parent onChange method on keyup
        },

        initialize(o) {

            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.pfx = this.config.stylePrefix || '';
            this.ppfx = this.config.pStylePrefix || '';
            this.className = this.pfx + 'trait';
            this.labelClass = this.ppfx + 'label ' + this.ppfx + 'four-color';
            this.fieldClass =
                this.ppfx + 'field ' + this.ppfx + 'field-' + md.get('type');
            this.inputhClass = this.ppfx + 'input-holder';

            this.tmpl =
                `<div class="${this.ppfx}fields">
                    <div class="${this.fieldClass}">
                        <div class="${this.inputhClass}"></div>
                    </div>
                 </div>`;
        },
        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {
            if (!this.inputEl) {
                let name = this.model.get('value') || 'Кнопка';
                this.inputEl = $(`<input type="button" value="${name}">`);
            }
            return this.inputEl.get(0);
        },
        /**
         * Triggered when the value of the model is changed
         */
        onChange: function () {
            let type = this.target.get('type');

            if (type === 'discount') {
                F.removeDiscount();
            }

            if (type === 'variant' && this.model.get('name') === 'remove-products') {
                F.removeProducts();
            }

            if (type === 'gallery' || type === 'gallery-img' || type === 'slider' || type === 'slider-img') {
                //add photo to gallery
                if (this.model.get('attributes').id === 'add-photo') {
                    let galleryId = this.target.view.$el.closest('[data-gallery-id]').attr('data-gallery-id');

                    editor.runCommand('open-assets');

                    //add new button to assets manager
                    F.addGalleryBtnToAsset(galleryId);
                }
                if (this.model.get('attributes').id === 'remove-gallery') {
                    window.confirm('Вы уверены что хотите удалить галерею?', ($modal) => {
                        //hide modal
                        $modal.modal('hide');

                        editor.select(wrapper);
                        this.target.view.$el.closest('.carousel').data('model').destroy();
                    });
                }
                if (this.model.get('attributes').id === 'remove-slider') {
                    window.confirm('Вы уверены что хотите удалить слайдер?', ($modal) => {
                        //hide modal
                        $modal.modal('hide');

                        editor.select(wrapper);
                        this.target.view.$el.closest('.slider').data('model').destroy();
                    });
                }
            }

            if (type === 'advantage') {
                editor.select(wrapper);
                this.target.view.$el.closest('.advantage-block').data('model').destroy();
            }
        }
    });

    editor.TraitManager.addType('radio', {
        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.pfx = this.config.stylePrefix || '';
            this.ppfx = this.config.pStylePrefix || '';
            this.className = this.pfx + 'trait';
            this.labelClass = this.ppfx + 'label ' + this.ppfx + 'four-color';
            this.fieldClass =
                this.ppfx + 'field ' + this.ppfx + 'field-' + md.get('type');
            this.inputhClass = this.ppfx + 'input-holder';

            this.tmpl =
                `<div class="${this.ppfx}fields">
                    <div class="${this.fieldClass}">
                        <div class="${this.inputhClass}"></div>
                    </div>
                 </div>`;
        },
        getInputEl() {

            const model = this.model;
            const pfx = this.pfx;
            const ppfx = this.ppfx;
            const itemCls = `${ppfx}radio-item-label`;
            const prop = model.get('property');
            const options = model.get('list') || model.get('options') || [];

            if (!this.$input) {
                if (options && options.length) {
                    let inputStr = '';

                    options.forEach(el => {
                        let cl = el.className ? `${el.className} ${this.ppfx}icon ${itemCls}` : '';
                        let id = `${prop}-${el.value}`;
                        let labelTxt = el.name || el.value;
                        let titleAttr = el.title ? `title="${el.title}"` : '';

                        inputStr += `
                            <div class="${ppfx}radio-item">
                            <input type="radio" class="${ppfx}sm-radio" id="${id}" name="${prop}" value="${el.value}"/>
                            <label class="${cl || itemCls}" ${titleAttr} for="${id}">${cl ? '' : labelTxt}</label>
                            </div>
                        `;
                    });

                    this.$input = $(`<div class="${ppfx}radio-items">${inputStr}</div>`);

                    let checked = model.get('checked');
                    //Check default prop
                    if (model.get('default') && !checked) {
                        this.setValue(model.get('default'));
                    }
                    //Restore saved settings
                    if (checked) {
                        this.setValue(checked);
                    }

                    //check if component added to variant
                    if (this.target.get('type') === 'variant' && this.model.get('attributes').id === 'recommended') {
                        let checked = wrapper.view.$el.find('ul.nav-tabs li.active a span.label_top_recomended').length > 0;
                        this.setValue(checked ? 'yes' : 'no');
                        this.model.set('checked', checked ? 'yes' : 'no');
                    }

                    //advantage type
                    if (this.target.get('type') === 'advantage') {
                        let checked = this.target.view.$el.closest('.advantage-block').hasClass('col-md-12');
                        this.setValue(checked ? 'yes' : 'no');
                        this.model.set('checked', checked ? 'yes' : 'no');
                    }

                    //Variant
                    if (this.target.get('type') === 'variant' && this.model.get('name') === 'selected') {
                        let checked = false;
                        let variant = config.offer.variants.filter((model, i) => {
                            //Find variant by index
                            return i === this.target.view.$el.parent().index()
                        });

                        if (variant.length) {
                            checked = variant[0].selected === 1 ? 'checked' : '';
                        }
                        this.setValue(checked ? 'yes' : 'no');
                        this.model.set('checked', checked ? 'yes' : 'no');
                    }

                }
            }

            return this.$input.get(0);
        },
        /**
         * Triggered when the value of the model is changed
         */
        onChange(e) {

            let type = this.model.target.get('type'),
                value = this.getInputValue(),
                checked = value === 'yes';

            //Save value in model
            this.model.set('checked', value);

            //Variant type
            if (type === 'variant') {
                let name = this.model.get('name'),
                    recommended = false,
                    activity = false,
                    selected = false;

                //If variant disabled in trait
                this.model.collection.each(function (model) {
                    if (model.get('name') === 'recommended') {
                        recommended = model.get('checked') === 'yes';
                    }
                    if (model.get('name') === 'activity') {
                        activity = model.get('checked') === 'yes' || (!model.get('checked') && model.get('default') === 'yes');
                    }
                    if (model.get('name') === 'selected') {
                        selected = model.get('checked') === 'yes';
                    }
                });

                if (name === 'recommended') {
                    F.setVariantRecommended(checked);
                    return;
                }

                if (name === 'activity') {
                    F.setVariantActivity(this, checked, selected);
                }

                if (name === 'selected') {
                    if (!activity && checked) {
                        this.setValue('no');
                        window.message(config.messages.offer.variant.disabled, 3000);
                        return;
                    }
                    //Check if offer is user template or system
                    if( (config.offer.user_template && config.offer.user_template.is_template) || config.offer.system){
                        this.setValue('no');
                        window.message(config.messages.offer.template.error, 3000);
                        return;
                    }

                    F.setVariantSelected(this, checked);
                }

                //Updatea variant settings
                F.updateCpSettingsVariant();
            }

            if (type === 'add-goods-coll') {
                //for model on page
                this.target.set('valueInPrice', checked);
                //refresh
                F.calculatePositionsPrices();
                //
                F.updateCpSettingsVariantProducts();
            }

            //Advantage type
            if (type === 'advantage') {
                F.updateAdvantageClasses(this.model, checked);
            }
            //Using with integration 
            if (this.model.get('changeTarget')) {
                let active = this.model.get('list').find((item) => {
                    return item.value == this.model.get('checked')
                });
                F.updateContent(this.model.target, active.name);
            }
            //Integration fields
            if (type.indexOf('integration-field') !== -1) {
                //Store for update in CRM
                F.updateCpSettingsIntegrationField({ type: type, value: checked });
            }
        },

        getInputValue() {
            const inputChk = this.getCheckedEl();
            return inputChk ? inputChk.value : '';
        },

        getCheckedEl() {
            const input = this.getInputEl();
            return input ? input.querySelector('input:checked') : '';
        },

        setValue(value) {
            const model = this.model;
            let val = value || model.get('value') || model.getDefaultValue();
            const input = this.getInputEl();
            const inputIn = input ? input.querySelector(`[value="${val}"]`) : '';

            if (inputIn) {
                inputIn.checked = true;
            } else {
                const inputChk = this.getCheckedEl();
                inputChk && (inputChk.checked = false);
            }
        }
    });

    editor.TraitManager.addType('select', {
        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.pfx = this.config.stylePrefix || '';
            this.ppfx = this.config.pStylePrefix || '';
            this.className = this.pfx + 'trait';
            this.labelClass = this.ppfx + 'label ' + this.ppfx + 'four-color';
            this.fieldClass =
                this.ppfx + 'field ' + this.ppfx + 'field-' + md.get('type');

            this.tmpl = `
                <div class="${this.ppfx}field ${this.ppfx}select">
                    <div class="${this.fieldClass}">
                        <div class="${this.inputhClass}"></div>
                    </div>
                    <div class="${this.ppfx}sel-arrow"><div class="${this.ppfx}d-s-arrow"></div></div>
                </div>`;
        },
        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {

            const model = this.model;
            let options = model.get('list') || model.get('options') || [];

            //Fix
            if (options === 'copy-from-variant') {

                options = editor.DomComponents.getWrapper().view.$el.find('ul.nav-tabs .kp10-header-name').map(function (el) {
                    // console.log(e);
                    return {
                        name: $(el).text().trim(),
                        value: $(el).parent().attr('href')
                    }
                });

                options.unshift({
                    name: '',
                    value: ''
                });

                //Reset
                model.set('selected', '');
            }

            if (!this.$input) {
                let optionsStr = '',
                    value = model.get('selected') || model.get('default');

                //Fix for multiple type='variants'
                if (model.get('name') == 'variant-tax') {
                    let selected = 0,
                        $navTabActive = wrapper.view.$el.find('ul.nav-tabs li.active [data-gjs-type="variant"]');

                    if ($navTabActive.length) {
                        let tabModel = $navTabActive.data('model');
                        if (tabModel) {
                            tabModel.get('traits').each(function (traitModel) {
                                if (traitModel.get('name') === 'variant-tax') {
                                    selected = traitModel.get('selected') || '0';
                                }
                            });
                        }
                    }
                    value = selected;
                    model.set('selected', value);
                }

                options.forEach(option => {
                    let name = option.name || option.value;
                    let style = option.style ? option.style.replace(/"/g, '&quot;') : '';
                    let styleAttr = style ? `style="${style}"` : '';
                    let value = option.value.replace(/"/g, '&quot;');
                    optionsStr += `<option value="${value}" ${styleAttr}>${name}</option>`;
                });

                this.$input = $(`<select>${optionsStr}</select>`);

                //set selected
                this.$input.get(0).value = value;
            }

            return this.$input.get(0);
        },
        /**
         * Triggered when the value of the model is changed
         */
        onChange: function () {

            let type = this.model.target.get('type'),
                name = this.model.get('name'),
                value = this.getInputValue();

            //Save value in model   
            this.model.set('selected', value);

            if (type === 'variant') {
                if (name === 'copy-from-variant') {
                    F.copyProductsFromVariant(value);
                    //reset to default
                    this.$input.get(0).value = '';
                }

                if (name === 'variant-tax') {
                    F.addTaxToVariant(parseInt(value));
                }
            }
            //Using with integration 
            if (this.model.get('changeTarget')) {
                F.updateContent(this.model.target, this.$input.find('option:selected').text().trim());
            }
            //Integration fields
            if (type.indexOf('integration-field') !== -1) {
                //Store for update in CRM
                F.updateCpSettingsIntegrationField({ type: type, value: value });
            }
        },
        getInputValue() {
            const input = this.getInputEl();
            return input ? input.value : '';
        },
    });

    editor.TraitManager.addType('datepicker', {
        events: {
            'change': 'onChange',  // trigger parent onChange method
        },

        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.pfx = this.config.stylePrefix || '';
            this.ppfx = this.config.pStylePrefix || '';
            this.className = this.pfx + 'trait';
            this.labelClass = this.ppfx + 'label ' + this.ppfx + 'four-color';
            this.fieldClass =
                this.ppfx + 'field ' + this.ppfx + 'field-' + md.get('type');
            this.inputhClass = this.ppfx + 'input-holder';

            this.tmpl =
                `<div class="${this.ppfx}fields">
                    <div class="${this.fieldClass}">
                        <div class="${this.inputhClass}"></div>
                    </div>
                 </div>`;
        },
        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {
            if (!this.inputEl) {
                let name = this.model.get('value') || '';
                this.inputEl = $(`<input type="text" value="${name}">`);
            }
            return this.inputEl.get(0);
        },
        /**
         * Triggered when the value of the model is changed
         */
        onChange: function () {
            let type = this.model.target.get('type'),
                value = this.model.get('value');

            F.updateContent(this.target, value);

            //Integration fields
            if (type.indexOf('integration-field') !== -1) {
                //Store for update in CRM
                F.updateCpSettingsIntegrationField({ type: type, value: value });
            }
        },
        /**
         * Renders input
         * @private
         * */
        renderField() {
            if (!this.$input) {
                this.$el.append(this.tmpl);
                const el = this.getInputEl();
                // I use prepand expecially for checkbox traits
                const inputWrap = this.el.querySelector(`.${this.inputhClass}`);
                inputWrap.insertBefore(el, inputWrap.childNodes[0]);

                //Init datepicker
                $(el).datetimepicker({
                    theme: 'dark',
                    format: 'd.m.Y',
                    timepicker: false,
                    onChangeDateTime: () => {
                        let value = $(el).val();
                        this.model.set('value', value);
                        this.$el.trigger('change');
                    }
                });
            }
        }
    });

    editor.TraitManager.addType('datetimepicker', {
        events: {
            'change': 'onChange',  // trigger parent onChange method
        },

        initialize(o) {
            let md = this.model;
            this.config = o.config || {};
            this.target = md.target;
            this.pfx = this.config.stylePrefix || '';
            this.ppfx = this.config.pStylePrefix || '';
            this.className = this.pfx + 'trait';
            this.labelClass = this.ppfx + 'label ' + this.ppfx + 'four-color';
            this.fieldClass =
                this.ppfx + 'field ' + this.ppfx + 'field-' + md.get('type');
            this.inputhClass = this.ppfx + 'input-holder';

            this.tmpl =
                `<div class="${this.ppfx}fields">
                    <div class="${this.fieldClass}">
                        <div class="${this.inputhClass}"></div>
                    </div>
                 </div>`;
        },
        /**
         * Returns the input element
         * @return {HTMLElement}
         */
        getInputEl: function () {
            if (!this.inputEl) {
                let name = this.model.get('value') || '';
                this.inputEl = $(`<input type="text" value="${name}">`);
            }
            return this.inputEl.get(0);
        },
        /**
         * Triggered when the value of the model is changed
         */
        onChange: function () {
            let type = this.model.target.get('type'),
                value = this.model.get('value');

            F.updateContent(this.target, value);

            //Integration fields
            if (type.indexOf('integration-field') !== -1) {
                //Store for update in CRM
                F.updateCpSettingsIntegrationField({ type: type, value: value });
            }
        },
        /**
         * Renders input
         * @private
         * */
        renderField() {
            if (!this.$input) {
                this.$el.append(this.tmpl);
                const el = this.getInputEl();
                // I use prepand expecially for checkbox traits
                const inputWrap = this.el.querySelector(`.${this.inputhClass}`);
                inputWrap.insertBefore(el, inputWrap.childNodes[0]);

                //Init datepicker
                $(el).datetimepicker({
                    theme: 'dark',
                    format: 'd.m.Y H:i',
                    onChangeDateTime: () => {
                        let value = $(el).val();
                        this.model.set('value', value);
                        this.$el.trigger('change');
                    }
                });

            }
        }
    });
};