/**
 * При оновленні Trait необхідно вимкнути завантаження шаблону із БД
 */
export default (editor, config = {}, F) => {
    // Get DomComponents module
    let comps = editor.DomComponents,
        wrapper = editor.DomComponents.getWrapper();

    //Initialize functions
    // F.init(editor, config);

    let textType = comps.getType('text');
    // Get the model and the view from the default Component type
    let defaultType = comps.getType('default'),
        defaultModel = defaultType.model,
        defaultView = defaultType.view,
        originalText = comps.getType('text'),
        originalLink = comps.getType('link'),
        originalImage = comps.getType('image'),
        originalSvg = comps.getType('svg');

    //Reset trait for default type
    comps.addType('text', {
        model: originalText.model.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-clone' },
                        //     command: 'tlb-clone',
                        // });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                   
                    model.set('toolbar', tb);
                }
            },
            // Extend default properties
            defaults: Object.assign({}, originalText.model.prototype.defaults, {
                badgable: false,
                copyable: false,
                editable: true,
                removable: true,
                draggable: true,
                selectable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    let result = '';

                    if (el.nodeType === 3) {
                        result = {
                            type: 'text',
                            content: el.textContent
                        };
                    }
                    return result;
                },
            }),
        view: originalText.view.extend({
            render: function () {
                // Extend the original render method
                defaultType.view.prototype.render.apply(this, arguments);
                //TODO: спробувати створити подію onchange і тільки потім додавати підпис
                if (this.model.get('editable') && this.model.get('selectable')) {
                    this.$el.attr('data-tooltip', config.messages.editable);
                    this.$el.attr('data-tooltip-pos', 'top');
                }

                return this;
            }
        })
    });
    //
    comps.addType('default', {
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                copyable: false,
                editable: true,
                removable: true,
                draggable: true,
                selectable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    return { tagName: el.tagName ? el.tagName.toLowerCase() : '' };
                },
            }),
        view: defaultType.view
    });

    //Add new component type
    editor.DomComponents.addType('discount', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                copyable: false,
                editable: false,
                removable: false,
                draggable: false,
                selectable: true,
                // Traits (Settings)
                traits: [{
                    type: 'discount',
                    label: 'Изменить для всех товаров',
                    // changeProp: 1
                }, {
                    type: 'button',
                    label: 'Удалить скидку для всех товаров',
                    name: 'Удалить',
                    value: 'Удалить',
                    // changeProp: 1
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') == 'discount') {
                        return { type: 'discount' };
                    }
                },
            }),

        // Define the View
        view: textType.view.extend({
            render: function () {
                // Extend the original render method
                defaultType.view.prototype.render.apply(this, arguments);

                if (this.model.get('editable')) {
                    this.$el.attr('data-tooltip', config.messages.editable);
                    this.$el.attr('data-tooltip-pos', 'top');
                }
                return this;
            }
        })
    });

    //Add special discount
    editor.DomComponents.addType('add-special-discount', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                draggable: "div.kp10-special-discount",
                droppable: false,
                copyable: false,
                editable: false,
                selectable: true,
                // Traits (Settings)
                traits: [{
                    type: 'number',
                    label: 'Изменить специальную скидку',
                    // changeProp: 1
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'add-special-discount') {
                        return { type: 'add-special-discount' };
                    }
                },
            }),

        // Define the View
        view: textType.view
    });

    //Add type for add-goods-coll
    //Using for remove listener element
    editor.DomComponents.addType('add-goods-coll', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                copyable: false,
                badgable: false,
                editable: true,
                draggable: false,
                draggable: false,
                removable: false,
                selectable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'add-goods-coll') {
                        return { type: 'add-goods-coll' };
                    }
                },
            }),
        // Define the View
        // view: textType.view //Need for editableContent!
        view: textType.view.extend({
            // The render() should return 'this'
            render: function () {
                // Extend the original render method
                defaultType.view.prototype.render.apply(this, arguments);

                if (!this.model.opt.temporary) {

                    //if this is not table value cell
                    if (!this.model.view.el.dataset.child) {
                        this.model.set({
                            draggable: 0, //disabled editable for added component
                            toolbar: [{ //init new toolbar buttons
                                attributes: { class: 'fa fa-arrow-up' },
                                command: 'select-parent',
                            }, {
                                attributes: { class: 'fa fa-trash-o' },
                                command: 'tlb-delete',
                            }]
                        });
                    } else {
                        //show for value table
                        this.model.set('traits', [{
                            type: 'radio',
                            label: 'Учитывать при расчете стоимости',
                            default: 'yes',
                            name: 'add-goods-coll',
                            property: 'add-goods-coll',
                            list: [{
                                value: 'yes',
                                name: 'Да'
                            }, {
                                value: 'no',
                                name: 'Нет'
                            }],
                        }]);
                    }
                }

                return this;
            }
        })
    });
    //Add group
    editor.DomComponents.addType('add-goods-group', {
        // Define the Model
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                draggable: 'div.tab-pane-inner__menu',
                copyable: false,
                badgable: false,
                editable: false,
                removable: true,
                selectable: true,
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'add-goods-group') {
                        return { type: 'add-goods-group' };
                    }
                },
            }),
        // Define the View
        // view: textType.view //Need for editableContent!
        view: defaultType.view.extend({
            init() {
                //Fix https://github.com/artf/grapesjs/pull/202
                if (this.model.opt.temporary) {
                    return;
                }

                this.listenTo(this.model, 'destroy remove', this.delete);
            },
            delete: function (model) {
                //Some wait while comp will be deleted
                setTimeout(function () {
                    let $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
                        $tableRow = $activeTab.find('.tab-content__table .tab-pane-inner__menu-row[data-db-id="' + model.view.$el.attr('data-db-id') + '"]');

                    //Check if component was realy deleted
                    if (!$tableRow.length && model.view.$el.attr('data-db-id')) {
                        F.calculatePositionsPrices();
                        //Mark product for remove from DB
                        F.updateCpSettingsVariantProducts({ delete: { product: { 'db-id': model.view.$el.attr('data-db-id') } } });
                    } else { //Component was moved
                        //Check if user trying to add group before
                        if (model.view.$el.index() == 0 || $tableRow.index() == 0) {
                            editor.UndoManager.undo();
                            return;
                        }

                        F.calculatePositionsPrices();
                        //Fix if component was moved
                        F.updateCpSettingsVariantProducts();
                    }
                }, 100);
            }
        })
    });

    //Add Variant
    editor.DomComponents.addType('add-variant', {
        // Define the Model
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                draggable: false, //'ul.nav-tabs'
                copyable: false,
                badgable: false,
                editable: false,
                removable: false,
                selectable: false,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'add-variant') {
                        return { type: 'add-variant' };
                    }
                },
            }),

        // Define the View
        view: defaultType.view
    });

    //Goods variant
    editor.DomComponents.addType('goods-variant', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];

                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-plus' },
                        command: function () {
                            F.showProductSelectModal();
                        },
                    });

                    if (model.collection) {
                        tb.push({
                            attributes: { class: 'fa fa-arrow-up' },
                            command: 'select-parent',
                        });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }

                    model.set('toolbar', tb);
                }
            },
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                draggable: 'div.tab-pane-inner__menu',
                copyable: false,
                droppable: false,
                badgable: false,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'goods-variant') {
                        return { type: 'goods-variant' };
                    }
                },
            }),
        // Define the View
        view: defaultType.view
    });

    // update Image type
    comps.addType('image', {
        model: originalImage.model.extend({}, {
            isComponent: function (el) {
                var result = '';
                if (el.tagName == 'IMG') {
                    result = { type: 'image' };
                }
                return result;
            },
        }),
        view: originalImage.view.extend({
            init() {
                this.listenTo(this.model, 'change:attributes', this.change);
            },
            change() {
                //Fix for update in view            
                if (this.model.changed && this.model.changed.attributes && this.model.changed.attributes.src) {
                    this.model.view.$el.attr('src', this.model.changed.attributes.src);
                }
            },
            // Bind events
            events: {
                dblclick: function (e) {
                    //Show block after hiding in advantage component
                    let $assetManager = $(editor.AssetManager.getContainer());

                    $assetManager.removeClass('advantage-list');

                    editor.runCommand('open-assets', {
                        target: this.model
                    });

                    let amConfig = editor.AssetManager.getConfig(),
                        path = config.path;
                    //
                    if (this.model.view.$el.hasClass('type-cover')) {
                        //update button to the parent
                        path += '/Обложки';
                    }
                    //Logo
                    if (this.model.view.$el.hasClass('type-logo')) {
                        //update button to the parent
                        path += '/Логотипы';
                    }
                    //Product detail(popup)
                    if (this.model.view.$el.hasClass('modal-product__preview-img')) {
                        //update button to the parent
                        path += '/Товары';
                    }
                    //Gallery
                    if (this.model.get('type') == 'gallery-img') {
                        //update button to the parent
                        path += '/Галерея';
                    }

                    //for level up button
                    amConfig.params.path = path;

                    F.showAmFolder(path);
                }
            },
        })
    });

    comps.addType('variant', {
        model: originalLink.model.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        tb.push({
                            attributes: { class: 'fa fa-arrow-up' },
                            command: 'select-parent',
                        });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, originalLink.model.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                copyable: false,
                editable: false,
                // Traits (Settings)
                traits: [{
                    type: 'radio',
                    label: 'Рекомендуемый',
                    changeProp: 1,
                    name: 'recommended',
                    attributes: { id: 'recommended' },
                    property: 'recommended',
                    list: [{
                        value: 'yes',
                        name: 'Да'
                    }, {
                        value: 'no',
                        name: 'Нет'
                    }],
                }, {
                    type: 'radio',
                    label: 'Активен',
                    changeProp: 1,
                    default: 'yes',
                    name: 'activity',
                    attributes: { id: 'activity' },
                    property: 'activity',
                    list: [{
                        value: 'yes',
                        name: 'Да'
                    }, {
                        value: 'no',
                        name: 'Нет'
                    }],
                }, {
                    type: 'radio',
                    label: 'Выбран',
                    changeProp: 1,
                    name: 'selected',
                    attributes: { id: 'selected' },
                    property: 'selected',
                    list: [{
                        value: 'yes',
                        name: 'Да'
                    }, {
                        value: 'no',
                        name: 'Нет'
                    }],
                }, {
                    type: 'select',//copy-from-variant
                    label: 'Скопировать из варианта',
                    // changeProp: 1,
                    name: 'copy-from-variant',
                    list: 'copy-from-variant' //fix
                }, {
                    type: 'select',
                    label: 'Налоги',
                    changeProp: 1,
                    name: 'variant-tax',
                    default: '0',
                    list: [{
                        value: '0',
                        name: 'Не указано'
                    }, {
                        value: '1',
                        name: 'С НДС'
                    }, {
                        value: '2',
                        name: 'Без НДС'
                    }],
                }, {
                    type: 'button',
                    name: 'remove-products',
                    value: 'Удалить',
                    label: 'Удалить товары и группы'
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'variant') {
                        return { type: 'variant' };
                    }
                },
            }),
        view: originalLink.view
    });

    //Add new component type
    editor.DomComponents.addType('variant-name', {
        // Define the Model
        model: originalText.model.extend({
            // Extend default properties
            defaults: Object.assign({}, originalText.model.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: true,
                layerable: false,
                selectable: false,
                hoverable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'variant-name') {
                        return { type: 'variant-name' };
                    }
                },
            }),

        // Define the View
        view: textType.view.extend({
            render: function () {
                // Extend the original render method
                defaultType.view.prototype.render.apply(this, arguments);
                //
                this.$el.attr('data-tooltip', config.messages.editable);
                this.$el.attr('data-tooltip-pos', 'top');

                return this;
            }
        })
    });

    //Gallery
    editor.DomComponents.addType('gallery', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrows' },
                        //     command: 'tlb-move',
                        // });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: '.main',
                removable: false,
                copyable: false,
                editable: false,
                // Traits (Settings)
                traits: [{
                    type: 'button',
                    label: 'Добавить фото',
                    name: 'Добавить',
                    value: 'Добавить',
                    attributes: { id: 'add-photo' }
                }, {
                    type: 'button',
                    label: 'Удалить галерею',
                    name: 'Удалить',
                    value: 'Удалить',
                    attributes: { id: 'remove-gallery' }
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('carousel')) {
                        return { type: 'gallery' };
                    }
                },
            }),
        // Define the View
        view: defaultType.view.extend({
            init() {
                //Fix https://github.com/artf/grapesjs/pull/202
                if (this.model.opt.temporary) {
                    return;
                }

                //disabled draggable after add element
                //todo виправити відображення іконки переміщення при виборі елемента
                this.model.set('draggable', false);
            }
        })
    });

    editor.DomComponents.addType('gallery-img', {
        // Define the Model
        model: originalImage.model.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    //Add custom button only for gallery images
                    tb.push({
                        attributes: { class: 'fa fa-plus' },
                        command: function () {
                            F.showGalleryAssets();
                        },
                    });

                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });
                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, originalImage.model.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: false,
                removable: true,
                copyable: false,
                editable: false,
                resizable: false,
                // Traits (Settings)
                traits: [{
                    type: 'button',
                    label: 'Добавить фото',
                    name: 'Добавить',
                    value: 'Добавить',
                    attributes: { id: 'add-photo' }
                }, {
                    type: 'button',
                    label: 'Удалить галерею',
                    name: 'Удалить',
                    value: 'Удалить',
                    attributes: { id: 'remove-gallery' }
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('gallery-img')) {
                        return { type: 'gallery-img' };
                    }
                },
            }),
        // Define the View
        view: originalImage.view.extend({
            init() {
                //Fix https://github.com/artf/grapesjs/pull/202
                if (this.model.opt.temporary) {
                    return;
                }

                this.listenTo(this.model, 'change:src', this.changeSrc);
            },
            changeSrc: function (model) {
                let galleryId = model.view.$el.attr('data-gallery-id');
                //need for delete event
                model.set('attributes', {
                    'data-gallery-id': galleryId
                });
            }
        })
    });

    //Slider
    editor.DomComponents.addType('slider', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrows' },
                        //     command: 'tlb-move',
                        // });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: '.main',
                removable: false,
                copyable: false,
                editable: false,
                // Traits (Settings)
                traits: [{
                    type: 'button',
                    label: 'Добавить фото',
                    name: 'Добавить',
                    value: 'Добавить',
                    changeProp: 1,
                    attributes: { id: 'add-photo' }
                }, {
                    type: 'button',
                    label: 'Удалить слайдер',
                    name: 'Удалить',
                    value: 'Удалить',
                    changeProp: 1,
                    attributes: { id: 'remove-slider' }
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('slider')) {
                        return { type: 'slider' };
                    }
                },
            }),
        // Define the View
        view: defaultType.view.extend({
            init() {
                //Fix https://github.com/artf/grapesjs/pull/202
                if (this.model.opt.temporary) {
                    return;
                }

                //disabled draggable after add element
                //todo виправити відображення іконки переміщення при виборі елемента
                this.model.set('draggable', false);
            }
        })
    });
    //Slider image type
    editor.DomComponents.addType('slider-img', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    //Add custom button only for gallery images
                    tb.push({
                        attributes: { class: 'fa fa-plus' },
                        command: function () {
                            F.showGalleryAssets();
                        },
                    });

                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });
                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: false,
                removable: true,
                copyable: false,
                editable: false,
                resizable: false,
                // Traits (Settings)
                traits: [{
                    type: 'button',
                    label: 'Добавить фото',
                    name: 'Добавить',
                    value: 'Добавить',
                    attributes: { id: 'add-photo' }
                }, {
                    type: 'button',
                    label: 'Удалить слайдер',
                    name: 'Удалить',
                    value: 'Удалить',
                    attributes: { id: 'remove-slider' }
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('slider-img')) {
                        return { type: 'slider-img' };
                    }
                },
            }),
        // Define the View
        view: defaultType.view.extend({
            init() {
                //Fix https://github.com/artf/grapesjs/pull/202
                if (this.model.opt.temporary) {
                    return;
                }

                this.listenTo(this.model, 'change:src', this.changeSrc);
            },
            changeSrc: function (model) {
                let galleryId = model.view.$el.attr('data-gallery-id');
                //need for delete event
                model.set('attributes', {
                    'data-gallery-id': galleryId
                });
            }
        })
    });

    //Advantage block
    editor.DomComponents.addType('advantage', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        // tb.push({
                        //     attributes: { class: 'fa fa-arrow-up' },
                        //     command: 'select-parent',
                        // });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            //Open Component settings
                            F.openComponentSettings();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: '.advantages-row',
                removable: true,
                copyable: false,
                editable: false,
                // Traits (Settings)
                traits: [{
                    type: 'radio',
                    label: 'Во всю ширину',
                    changeProp: 1,
                    attributes: { id: 'advantage-full-width' },
                    property: 'advantage-full-width',
                    list: [{
                        value: 'yes',
                        name: 'Да'
                    }, {
                        value: 'no',
                        name: 'Нет'
                    }],
                }, {
                    type: 'button',
                    label: 'Удалить приемущество',
                    value: 'Удалить',
                    attributes: { id: 'remove-advantage' }
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('advantage')) {
                        return { type: 'advantage' };
                    }
                },
            }),
        // Define the View
        view: textType.view
    });

    //Advantage image
    editor.DomComponents.addType('advantage-image', {
        // Define the Model
        model: defaultModel.extend({
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                stylable: false,
                droppable: false,
                draggable: false,
                removable: true,
                copyable: false,
                editable: false,
                selectable: false,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('advantage-image')) {
                        return { type: 'advantage-image' };
                    }
                },
            }),
        // Define the View
        view: defaultView.extend({
            // Bind events
            events: {
                dblclick: function () {
                    let $assetManager = $(editor.AssetManager.getContainer());
                    $assetManager.addClass('advantage-list');

                    editor.runCommand('open-assets', {
                        target: this.model
                    });

                    //Show only svg icons
                    editor.AssetManager.render(editor.AssetManager.getAll().filter(
                        asset => asset.get('type') === 'advantage-image'
                    ));

                }
            }
        }),
    });
    //Color of the company
    editor.DomComponents.addType('corporate-color', {
        // Define the Model
        model: defaultModel.extend({
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                // Traits (Settings)
                traits: [{
                    type: 'color',
                    label: 'Добавить фото',
                    name: 'Добавить',
                }],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).attr('id') === 'corporate-color') {
                        return { type: 'corporate-color' };
                    }
                },
            }),
        // Define the View
        view: defaultType.view
    });

    //Client
    editor.DomComponents.addType('client', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        tb.push({
                            attributes: { class: 'fa fa-arrow-up' },
                            command: 'select-parent',
                        });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            $('.gjs-pn-views').find('.gjs-pn-btn.fa-wrench:not(.gjs-pn-active)').click();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                draggable: 'div.cp-details-about',
                copyable: false,
                badgable: false,
                // Traits (Settings)
                traits: []
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('client')) {
                        return { type: 'client' };
                    }
                },
            }),
        // Define the View
        view: textType.view
    });

    //Integration fields
    if (config.integration && config.integration.fields) {
        //Megaplan
        if (config.integration.system_crm_id === 1) {
            //
            config.integration.fields.forEach((field) => {

                let traits = [],
                    showElementSettings = false,
                    value = [];

                //Find field and get values
                if (config.offer.megaplan_deal && config.offer.megaplan_deal.values) {
                    let dataField = config.offer.megaplan_deal.values.find((el) => {
                        return el.field_id == field.id;
                    });
                    value = dataField;
                }

                if (field.content_type == 'EnumField') { //Список
                    if (field.enums) {
                        let trait = {
                            type: 'select',
                            label: field.field_name,
                            changeProp: 1,
                            name: 'integration-field-' + field.field_id,
                            changeTarget: 1, //Update target element
                            default: value && value.megaplan_field_values ? value.megaplan_field_values : '0',
                            list: [{
                                value: '0',
                                name: 'Не выбран'
                            }]
                        };

                        field.enums.forEach((el) => {
                            trait.list.push({
                                value: el.megaplan_enum_values,
                                name: el.megaplan_enum_values
                            });
                        });

                        traits.push(trait);
                    }

                    showElementSettings = true;
                }

                if (field.content_type == 'DateField' || field.content_type == 'DateTimeField') { //Дата, Дата и время
                    //
                    let def = value && value.megaplan_field_values ? value.megaplan_field_values : '';

                    if (value && value.megaplan_field_values) {
                        def = F.formatIntegrationFieldDate(value);
                    }

                    if (field.enums) {
                        let type = 'datepicker';
                        if (field.content_type == 'DateTimeField') {
                            type = 'datetimepicker';
                        }

                        let trait = {
                            type: type,
                            label: field.field_name,
                            value: def,
                            changeProp: 1,
                            name: 'integration-field-' + field.field_id,
                        };

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                if (field.content_type == 'BoolField') { //Bool
                    let trait = {
                        type: 'radio',
                        label: field.field_name,
                        default: value && value.megaplan_field_values == '1' ? 'yes' : 'no',
                        name: 'integration-field-' + field.field_id,
                        property: 'integration-field-' + field.field_id,
                        changeTarget: 1, //Update target element
                        list: [{
                            value: 'yes',
                            name: 'Да'
                        }, {
                            value: 'no',
                            name: 'Нет'
                        }],
                    };

                    traits.push(trait);

                    showElementSettings = true;
                }

                editor.DomComponents.addType('integration-field-' + field.field_id, {
                    // Define the Model
                    model: defaultModel.extend({
                        initToolbar() {
                            let model = this;
                            if (!model.get('toolbar')) {
                                let tb = [];
                                if (model.collection) {
                                    // tb.push({
                                    //     attributes: { class: 'fa fa-arrow-up' },
                                    //     command: 'select-parent',
                                    // });
                                }
                                if (model.get('draggable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-arrows' },
                                        command: 'tlb-move',
                                    });
                                }
                                if (model.get('copyable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-clone' },
                                        command: 'tlb-clone',
                                    });
                                }
                                if (model.get('removable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-trash-o' },
                                        command: 'tlb-delete',
                                    });
                                }
                                if (showElementSettings) {
                                    //Add custom button
                                    tb.push({
                                        attributes: { class: 'fa fa-cog' },
                                        command: function () {
                                            //Open Component settings
                                            F.openComponentSettings();
                                        },
                                    });
                                }

                                model.set('toolbar', tb);
                            }
                        },
                        defaults: Object.assign({}, defaultModel.prototype.defaults, {
                            // Traits (Settings)
                            traits: traits
                        }),
                    }, {
                            isComponent: function (el) {
                                if ($(el).data('gjs-type') === 'integration-field-' + field.field_id) {
                                    return { type: 'integration-field-' + field.field_id };
                                }
                            },
                        }),
                    // Define the View
                    view: textType.view
                });

            });
        }
        //Amocrm 
        if (config.integration.system_crm_id === 2) {
            //
            config.integration.fields.forEach((field) => {

                let traits = [],
                    showElementSettings = false,
                    value = [];

                //Find field and get values
                if (config.offer.amocrm_deal && config.offer.amocrm_deal.data && config.offer.amocrm_deal.data.fields) {
                    let dataField = config.offer.amocrm_deal.data.fields.find((el) => {
                        return el.amocrm_field_id == field.amocrm_field_id;
                    });
                    value = dataField && dataField.values && dataField.values[0] && dataField.values[0];
                }

                if (field.amocrm_field_type_id == 3) { //Флаг
                    if (field.enums) {
                        let trait = {
                            type: 'radio',
                            label: field.amocrm_field_name,
                            default: value && value.amocrm_field_value ? 'yes' : 'no',
                            name: 'integration-field-' + field.amocrm_field_type_id,
                            property: 'integration-field-' + field.amocrm_field_type_id,
                            changeTarget: 1, //Update target element
                            list: [{
                                value: 'yes',
                                name: 'Да'
                            }, {
                                value: 'no',
                                name: 'Нет'
                            }],
                        };

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                if (field.amocrm_field_type_id == 4 || field.amocrm_field_type_id == 10) { //Список | Переключатель
                    if (field.enums) {
                        let trait = {
                            type: 'select',
                            label: field.amocrm_field_name,
                            changeProp: 1,
                            changeTarget: 1, //Update target element
                            name: 'integration-field-' + field.amocrm_field_type_id,
                            default: value && value.amocrm_field_enum_id ? value.amocrm_field_enum_id : '0',
                            list: [{
                                value: '0',
                                name: 'Не выбран'
                            }]
                        };

                        field.enums.forEach((el) => {
                            trait.list.push({
                                value: el.amocrm_enum_id + '',
                                name: el.amocrm_enum_value
                            });
                        });

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                if (field.amocrm_field_type_id == 6 || field.amocrm_field_type_id == 14) { //Дата | День рождения
                    //
                    let def = value && value.amocrm_field_value ? value.amocrm_field_value : '';
                    //For type 6
                    if (value && [6, 14].indexOf(field.amocrm_field_type_id) !== -1 && value.amocrm_field_value) {
                        def = F.formatIntegrationFieldDate(value);
                    }

                    if (field.enums) {
                        let trait = {
                            type: 'datepicker',
                            label: field.amocrm_field_name,
                            value: def,
                            changeProp: 1,
                            name: 'integration-field-' + field.amocrm_field_type_id,
                        };

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                editor.DomComponents.addType('integration-field-' + field.amocrm_field_id, {
                    // Define the Model
                    model: defaultModel.extend({
                        initToolbar() {
                            let model = this;
                            if (!model.get('toolbar')) {
                                let tb = [];
                                if (model.collection) {
                                    // tb.push({
                                    //     attributes: { class: 'fa fa-arrow-up' },
                                    //     command: 'select-parent',
                                    // });
                                }
                                if (model.get('draggable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-arrows' },
                                        command: 'tlb-move',
                                    });
                                }
                                if (model.get('copyable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-clone' },
                                        command: 'tlb-clone',
                                    });
                                }
                                if (model.get('removable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-trash-o' },
                                        command: 'tlb-delete',
                                    });
                                }
                                if (showElementSettings) {
                                    //Add custom button
                                    tb.push({
                                        attributes: { class: 'fa fa-cog' },
                                        command: function () {
                                            //Open Component settings
                                            F.openComponentSettings();
                                        },
                                    });
                                }

                                model.set('toolbar', tb);
                            }
                        },
                        defaults: Object.assign({}, defaultModel.prototype.defaults, {
                            badgable: false,
                            draggable: false,
                            copyable: false,
                            editable: false,
                            removable: false,
                            // Traits (Settings)
                            traits: traits
                        }),
                    }, {
                            isComponent: function (el) {
                                if ($(el).data('gjs-type') === 'integration-field-' + field.amocrm_field_id) {
                                    return { type: 'integration-field-' + field.amocrm_field_id };
                                }
                            },
                        }),
                    // Define the View
                    view: textType.view
                });

            });
        }
        //Bitrix24
        if (config.integration.system_crm_id === 3) {

            config.integration.fields.forEach((field) => {

                let traits = [],
                    showElementSettings = false,
                    value = [];

                //Find field and get values
                if (config.offer.bitrix24_deal && config.offer.bitrix24_deal.data && config.offer.bitrix24_deal.data.fields) {
                    let dataField = config.offer.bitrix24_deal.data.fields.find((el) => {
                        return el.bitrix24_field_id == field.bitrix24_field_id;
                    });
                    value = dataField && dataField.values && dataField.values[0] && dataField.values[0];
                }

                if (field.bitrix24_field_type_id == 'boolean') { //boolean
                    if (field.enums) {
                        let trait = {
                            type: 'radio',
                            label: field.bitrix24_field_name,
                            default: value && value.bitrix24_field_value ? 'yes' : 'no',
                            name: 'integration-field-' + field.bitrix24_field_type_id,
                            property: 'integration-field-' + field.bitrix24_field_type_id,
                            changeTarget: 1, //Update target element
                            list: [{
                                value: 'yes',
                                name: 'Да'
                            }, {
                                value: 'no',
                                name: 'Нет'
                            }],
                        };

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                if (field.bitrix24_field_type_id == 'enumeration' ) { //enumeration
                    if (field.enums) {
                        let trait = {
                            type: 'select',
                            label: field.bitrix24_field_name,
                            changeProp: 1,
                            changeTarget: 1, //Update target element
                            name: 'integration-field-' + field.bitrix24_field_type_id,
                            default: value && value.bitrix24_field_enum_id ? value.bitrix24_field_enum_id : '0',
                            list: [{
                                value: '0',
                                name: 'Не выбран'
                            }]
                        };

                        field.enums.forEach((el) => {
                            trait.list.push({
                                value: el.bitrix24_enum_id + '',
                                name: el.bitrix24_enum_value
                            });
                        });

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                if (field.bitrix24_field_type_id == 'date') { //date
                    //
                    let def = value && value.bitrix24_field_value ? F.formatIntegrationFieldDate(value) : '';

                    if (field.enums) {
                        let trait = {
                            type: 'datepicker',
                            label: field.bitrix24_field_name,
                            value: def,
                            changeProp: 1,
                            name: 'integration-field-' + field.bitrix24_field_type_id,
                        };

                        traits.push(trait);
                    }
                    showElementSettings = true;
                }

                editor.DomComponents.addType('integration-field-' + field.bitrix24_field_id, {
                    // Define the Model
                    model: defaultModel.extend({
                        initToolbar() {
                            let model = this;
                            if (!model.get('toolbar')) {
                                let tb = [];
                                if (model.collection) {
                                    // tb.push({
                                    //     attributes: { class: 'fa fa-arrow-up' },
                                    //     command: 'select-parent',
                                    // });
                                }
                                if (model.get('draggable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-arrows' },
                                        command: 'tlb-move',
                                    });
                                }
                                if (model.get('copyable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-clone' },
                                        command: 'tlb-clone',
                                    });
                                }
                                if (model.get('removable')) {
                                    tb.push({
                                        attributes: { class: 'fa fa-trash-o' },
                                        command: 'tlb-delete',
                                    });
                                }
                                if (showElementSettings) {
                                    //Add custom button
                                    tb.push({
                                        attributes: { class: 'fa fa-cog' },
                                        command: function () {
                                            //Open Component settings
                                            F.openComponentSettings();
                                        },
                                    });
                                }

                                model.set('toolbar', tb);
                            }
                        },
                        defaults: Object.assign({}, defaultModel.prototype.defaults, {
                            badgable: false,
                            draggable: false,
                            copyable: false,
                            editable: false,
                            removable: false,
                            // Traits (Settings)
                            traits: traits
                        }),
                    }, {
                            isComponent: function (el) {
                                if ($(el).data('gjs-type') === 'integration-field-' + field.bitrix24_field_id) {
                                    return { type: 'integration-field-' + field.bitrix24_field_id };
                                }
                            },
                        }),
                    // Define the View
                    view: textType.view
                });
            });
        }
    }

    //Contact person
    editor.DomComponents.addType('contact-person', {
        // Define the Model
        model: defaultModel.extend({
            initToolbar() {
                let model = this;
                if (!model.get('toolbar')) {
                    let tb = [];
                    if (model.collection) {
                        tb.push({
                            attributes: { class: 'fa fa-arrow-up' },
                            command: 'select-parent',
                        });
                    }
                    if (model.get('draggable')) {
                        tb.push({
                            attributes: { class: 'fa fa-arrows' },
                            command: 'tlb-move',
                        });
                    }
                    if (model.get('copyable')) {
                        tb.push({
                            attributes: { class: 'fa fa-clone' },
                            command: 'tlb-clone',
                        });
                    }
                    if (model.get('removable')) {
                        tb.push({
                            attributes: { class: 'fa fa-trash-o' },
                            command: 'tlb-delete',
                        });
                    }
                    //Add custom button
                    tb.push({
                        attributes: { class: 'fa fa-cog' },
                        command: function () {
                            $('.gjs-pn-views').find('.gjs-pn-btn.fa-wrench:not(.gjs-pn-active)').click();
                        },
                    });

                    model.set('toolbar', tb);
                }
            },
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                draggable: 'div.cp-details-about',
                copyable: false,
                badgable: false,
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).hasClass('contact-person')) {
                        return { type: 'contact-person' };
                    }
                },
            }),
        // Define the View
        view: textType.view
    });

    //Add download excel
    editor.DomComponents.addType('download-excel', {
        // Define the Model
        model: originalLink.model.extend({
            // Extend default properties
            defaults: Object.assign({}, originalLink.model.prototype.defaults, {
                draggable: 'div.cp-details__buttons-col',
                copyable: false,
                badgable: false,
                editable: false,
                removable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'download-excel') {
                        return { type: 'download-excel' };
                    }
                },
            }),

        // Define the View
        view: originalLink.view
    });

    //Add download pdf
    editor.DomComponents.addType('download-pdf', {
        // Define the Model
        model: originalLink.model.extend({
            // Extend default properties
            defaults: Object.assign({}, originalLink.model.prototype.defaults, {
                draggable: 'div.cp-details__buttons-col',
                copyable: false,
                badgable: false,
                editable: false,
                removable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'download-pdf') {
                        return { type: 'download-pdf' };
                    }
                },
            }),

        // Define the View
        view: originalLink.view
    });

    //Add download pdf full
    editor.DomComponents.addType('download-pdf-full', {
        // Define the Model
        model: originalLink.model.extend({
            // Extend default properties
            defaults: Object.assign({}, originalLink.model.prototype.defaults, {
                draggable: 'div.cp-details__buttons-col',
                copyable: false,
                badgable: false,
                editable: false,
                removable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'download-pdf-full') {
                        return { type: 'download-pdf-full' };
                    }
                },
            }),

        // Define the View
        view: originalLink.view
    });

    editor.DomComponents.addType('employee-avatar', {
        // Define the Model
        model: originalImage.model.extend({
            defaults: Object.assign({}, originalImage.model.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: false,
                layerable: false,
                selectable: false,
                hoverable: false,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'employee-avatar') {
                        return { type: 'employee-avatar' };
                    }
                },
            }),
        // Define the View
        view: originalImage.view.extend({
            // Bind events
            events: {
                dblclick: function (e) {
                }
            }
        })
    });

    editor.DomComponents.addType('product-image', {
        // Define the Model
        model: originalImage.model.extend({
            defaults: Object.assign({}, originalImage.model.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: true,
                copyable: false,
                resizable: false,
                editable: false,
                layerable: false,
                selectable: false,
                hoverable: true,
                // Traits (Settings)
                traits: [],
            }),
        }, {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'product-image') {
                        return { type: 'product-image' };
                    }
                },
            }),
        // Define the View
        view: originalImage.view.extend({
            // Bind events
            events: {
                dblclick: function (e) {
                }
            }
        })
    });

    //Disabled type
    comps.addType('disabled', {
        // Define the Model
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: false,
                layerable: false,
                selectable: false,
                hoverable: false,
                // Traits (Settings)
                traits: [],
            }),
        },
            {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'disabled') {
                        return { type: 'disabled' };
                    }
                },
            }),

        // Define the View
        view: defaultType.view,
    });
    //Disabled type
    comps.addType('editable', {
        // Define the Model
        model: textType.model.extend({
            // Extend default properties
            defaults: Object.assign({}, textType.model.prototype.defaults, {
                removable: false,
                draggable: false,
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: true,
                layerable: false,
                selectable: false,
                hoverable: true,
                // Traits (Settings)
                traits: [],
            }),
        },
            {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'editable') {
                        return { type: 'editable' };
                    }
                },
            }),

        // Define the View
        view: textType.view.extend({
            render: function () {
                // Extend the original render method
                defaultType.view.prototype.render.apply(this, arguments);
                //
                // if (!this.$el.closest('.message__tooltip').length) {
                this.$el.attr('data-tooltip', config.messages.editable);
                this.$el.attr('data-tooltip-pos', 'top');

                return this;
            }
        }),
    });
    //Manager
    comps.addType('employee-signature', {
        // Define the Model
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                badgable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: false,
                layerable: false,
                selectable: false,
                hoverable: false,
                // Traits (Settings)
                traits: [],
            }),
        },
            {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'employee-signature') {
                        return { type: 'employee-signature' };
                    }
                },
            }),

        // Define the View
        view: defaultType.view,
    });
    comps.addType('integration-field', {
        // Define the Model
        model: defaultModel.extend({
            // Extend default properties
            defaults: Object.assign({}, defaultModel.prototype.defaults, {
                removable: true,
                draggable: 'div.cp-details-about',
                droppable: false,
                badgable: false,
                stylable: false,
                highlightable: false,
                copyable: false,
                resizable: false,
                editable: false,
                layerable: false,
                selectable: true,
                hoverable: false,
                // Traits (Settings)
                traits: [],
            }),
        },
            {
                isComponent: function (el) {
                    if ($(el).data('gjs-type') === 'integration-field') {
                        return { type: 'integration-field' };
                    }
                },
            }),

        // Define the View
        view: defaultType.view,
    });

}