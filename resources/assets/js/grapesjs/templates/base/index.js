try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
    require('jquery-datetimepicker');
    require('summernote');
    require('summernote/lang/summernote-ru-RU');
    require('icheck');
    require('select2');
    require('select2/dist/js/i18n/ru');
    // require('suggestions-jquery');
    // require('../../../../../../public/plugins/dadata/jquery.suggestions.min.js');
} catch (e) { }

import Cropper from 'cropperjs';
require('../../src/utils/ColorPicker');
import messages from './messages';
window.F = require('./functions');
import loadComponents from './components';
import loadBlocks from './blocks';
import loadTraits from './traits';

window.Cropper = Cropper;
require('./cropper-helper');

window.grapesjs.plugins.add('grapesjs-plugin-kp10-base', (editor, options) => {
    window.config = options;

    window._ = require('lodash');

    /**
     * We'll load the axios HTTP library which allows us to easily issue requests
     * to our Laravel back-end. This library automatically handles sending the
     * CSRF token as a header based on the value of the "XSRF" token cookie.
     */

    window.axios = require('axios');
    var axios = window.axios,
        $ = window.$,
        config = window.config,
        F = window.F;

    //TODO: Переписати з використанням socket.io
    //Перевірка авторизації
    location.href.indexOf('login') === -1 && location.href.indexOf('register') === -1 && setInterval(() => {
        axios.get('/auth/check').then((response) => { if (!response.data.auth) { location.reload() } }).catch((error) => { console.log(error) });
    }, 5000);

    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    /**
     * Next we will register the CSRF Token as a common header with Axios so that
     * all outgoing HTTP requests automatically have it attached. This is just
     * a simple convenience so we don't have to attach every token manually.
     */

    if (config.token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = config.token;
    } else {
        console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    }

    //Default language for select2
    $.fn.select2.defaults.set('language', 'ru');
    //Default language for datepicker
    // $.datepicker.setDefaults($.datepicker.regional['ru']);
    //Default language for datetimepicker
    $.datetimepicker.setLocale('ru');

    //
    require('./utilities');

    // Get DomComponents module
    let comps = editor.DomComponents;
    // Get the model and the view from the default Component type
    let defaultType = comps.getType('default');
    //Save modal to variable
    let modal = editor.Modal;
    //Fix for link RTE
    const action = editor.RichTextEditor.get('link');
    action.result = function (rte) {
        rte.insertHTML(`<a href="${rte.selection()}" target="_blank">${rte.selection()}</a>`)
    };
    //Event modal open
    //Remove previous class
    modal.getModel().on('change:open', () => {
        let $modal = $('.gjs-mdl-container');
        $modal.find('.gjs-mdl-dialogue-medium').removeClass('gjs-mdl-dialogue-medium');
    });

    //fix for tab
    let wrapper = editor.DomComponents.getWrapper();

    window.$wrapper = wrapper.view.$el;

    //Commands
    let commands = editor.Commands;

    //Add messages to config
    config.messages = messages;

    //Initialize functions
    F.init(editor, config);

    // Add blocks
    loadBlocks(editor, config, F);

    // Add components
    loadComponents(editor, config, F);

    //Add fВы уверены что хотите выбрать вариант коммерческого предложения
    loadTraits(editor, config, F);

    //Assets permission
    let amConfig = editor.AssetManager.getConfig();

    // Get the Asset Manager module first
    let am = editor.AssetManager;
    // let amConfig = editor.AssetManager.getConfig();
    am.render(); //Don't remove

    // Add new type
    am.addType('folder', {
        view: {
            getPreview() {
                return '<i class="fa fa-folder" aria-hidden="true"></i>';
            },
            getInfo() {
                return '<div>' + this.model.get('name') + '</div>';
            },
            onClick(e) {
                e.stopPropagation();

                //Multi selection
                if (e.ctrlKey) {
                    return;
                }

                this.collection.trigger('deselectAll');
                this.$el.addClass(this.pfx + 'highlight');
            },
            onDblClick(e) {
                e.stopPropagation();

                //save level path for level up click and assets
                amConfig.params.path = this.model.get('path');

                //show only selected folder
                F.showAmFolder(this.model.get('path'));
            },
            onRemove(e) {
                e.stopPropagation();
                let model = this.model,
                    path = this.model.get('path');

                window.confirm('Вы уверены что хотите удалить папку со всем содержимым?', ($modal) => {

                    axios.delete('/file-manager/folder', { data: { path: path } })
                        .then(function (response) {
                            if (response) {
                                model.collection.remove(model);
                            }
                        })
                        .catch(function (error) {
                            window.message({ text: error.response.data.errors, error: true });
                        });
                });
            }
        },
        isType(value) {
            if (typeof value == 'object' && value.type == 'folder') {
                return value;
            }
        }
    });

    am.addType('image', {
        view: {
            init(o) {
                let className = this.pfx + 'highlight' + ' ' + this.pfx + 'asset-image';
                //Fix for Mac OS
                o.model.view.$el.on('mousedown', (e) => {
                    //Multi selection
                    if (e.ctrlKey) {
                        //if folder was selected
                        if (this.$el.closest('.gjs-am-assets').find('.gjs-am-highlight i.fa-folder').length) {
                            this.collection.trigger('deselectAll');
                        }

                        this.$el.addClass(className);
                        return;
                    }
                    if (e.shiftKey) {
                        //if folder was selected
                        if (this.$el.closest('.gjs-am-assets').find('.gjs-am-highlight i.fa-folder').length) {
                            this.collection.trigger('deselectAll');
                        }

                        this.$el.addClass(className);

                        let firstIndex = -1,
                            lastIndex = this.$el.index();

                        this.$el.closest('.gjs-am-assets').find('.gjs-am-asset').each(function () {
                            if (firstIndex === -1 && $(this).hasClass('gjs-am-highlight')) {
                                firstIndex = $(this).index();
                            }

                            if (firstIndex !== -1 && $(this).index() > firstIndex && $(this).index() < lastIndex) {
                                $(this).addClass(className);
                            }

                        });

                        return;
                    }

                    this.collection.trigger('deselectAll');
                    this.$el.addClass(className);
                });
            },
            onClick(e) {
                e.stopPropagation();

                // Multi selection
                // if (e.ctrlKey) {
                //     //if folder was selected
                //     if (this.$el.closest('.gjs-am-assets').find('.gjs-am-highlight i.fa-folder').length) {
                //         this.collection.trigger('deselectAll');
                //     }
                //
                //     this.$el.addClass(this.pfx + 'highlight');
                //     return
                // }
                //
                // this.collection.trigger('deselectAll');
                // this.$el.addClass(this.pfx + 'highlight');

            },
            onDblClick(e) {
                e.stopPropagation();

                const em = this.em;
                // this.updateTarget(this.collection.target);
                //fix for img
                if (this.collection.target) {
                    this.collection.target.set('attributes', { src: this.model.get('src') });
                    this.collection.target.set('src', this.model.get('src'));
                }

                //If galley exists add image
                let $addToGallery = $assetManager.find('#add-to-gallery');
                if ($addToGallery.length) {
                    //Add to gallery image
                    F.updateGallery($addToGallery.data('gallery-id'));

                    //fix for gallery
                    //Remove button
                    $addToGallery.remove();
                }

                this.collection.trigger('deselectAll');
                em && em.get('Modal').close();

            },
            onRemove(e) {
                e.stopPropagation();
                let model = this.model,
                    file = this.model.get('file'),
                    folder = this.model.get('folder');

                window.confirm('Вы уверены что хотите удалить файл?', ($modal) => {

                    axios.delete('/file-manager/file', { data: { file: file, folder: folder } })
                        .then(function (response) {
                            if (response) {
                                model.collection.remove(model);
                            }
                        })
                        .catch(function (error) {
                            window.message({ text: error.response.data.errors, error: true });
                        });
                });
            }
        },
        isType(value) {
            if (typeof value == 'object' && value.type == 'image') {
                return value;
            }
        }
    });

    am.addType('advantage-image', {
        view: {
            getPreview() {
                return '<i class="fa ' + this.model.get('cls') + '"></i>';
            },
            getInfo() {
                return '<div>' + this.model.get('cls') + '</div>';
            },
            onDblClick(e) {
                e.stopPropagation();

                const em = this.em;

                //Find and remove current icon
                let currentClassModel = this.collection.target.get('classes').find(function (model) {
                    return /fa-\w+/.test(model.id)
                });
                this.collection.target.get('classes').remove(currentClassModel);

                //Add new icon
                this.collection.target.get('classes').add({ name: this.model.get('cls') });

                this.collection.trigger('deselectAll');
                em && em.get('Modal').close();
            },
        },
        isType(value) {
            if (typeof value === 'object' && value.type === 'advantage-image') {
                return value;
            }
        }
    });

    //Change style for asset manager
    let $assetManager = $(am.getContainer());
    $assetManager.find('.gjs-am-assets-header').empty()
        .html(
            '<button class="gjs-btn-prim" id="level-up"><i class="fa fa-level-up" aria-hidden="true"></i> Назад</button>' +
            '<div class="gjs-field"><input placeholder="Для поиска введите текст" id="advantage-search"></div>'
            /*' <button class="gjs-btn-prim" id="create-folder"><i class="fa fa-plus" aria-hidden="true"></i> Создать папку</button>'*/
        );

    // The upload is started
    editor.on('asset:upload:start', () => {
        startAnimation();
    });

    // The upload is ended (completed or not)
    editor.on('asset:upload:end', () => {
        endAnimation();
        // console.log(editor.getSelected());
        F.showCropEditor(am.getAll().filter(asset => asset.get('cropped') === 0));
    });

    // Error handling
    editor.on('asset:upload:error', (err) => {
        let response = jQuery.parseJSON(err);
        window.message({ text: response.errors, error: true });
    });

    // Do something on response
    editor.on('asset:upload:response', (response) => {
        if (response.files) {
            for (let f in response.files) {
                let file = response.files[f];
                //If file doesn't exist
                if (!am.get(file.file)) {
                    am.add({
                        folder: response.folder, //using for filtering
                        src: file.src,
                        file: file.file,
                        name: file.name,
                        cropped: file.cropped
                    });
                }
            }
            //show only selected folder
            am.render(am.getAll().filter(
                asset => asset.get('folder') == response.folder
            ));
        }
    });

    // editor.on('run:open-assets', () => {
    //     let model = editor.getSelected();
    //     if (model && model.get('type')) {
    //
    //         console.log(model.get('type'));
    //     }
    // });

    //fix for smooth template loading
    editor.on('storage:end', (model) => {
        //Check if variant selected
        if (config.offer.variants.find(variant => variant.selected)) {

            let message =
                '<p>В данном КП вариант уже выбран менеджером или клиентом</p>' +
                '<p>Для внесения изменений в КП необходимо снять выбор варианта</p>' +
                '<p>После внесенных изменений в КП, выбор варианта будет доступен менеджеру и клиенту</p>' +
                '<button class="btn btn-default" id="cancel-variant-selection"><i class="fa fa-undo"></i> Снять выбор варианта</button>';

            window.message({
                text: message,
                backdrop: 'static',
                keyboard: false,
                show: true
            });

            console.warn('Variant selected');
            return;
        }

        setTimeout(function () {
            //Check config
            if (editor.checkConfig) {
                F.checkConfig();
            }
            //When configuration done
            if (!editor.configuring) {
                $('#gjs').fadeIn();
                //Show help block
                window.utilities.showHelp(true);

                if (!editor.showStoreReponse) {
                    window.hideMessage();
                }

                //Show message helpers
                wrapper.view.$el.find('img, .advantage-image').each((el) => {
                    let $this = $(el),
                        type = $this.attr('data-gjs-type'),
                        message = config.messages.editable,
                        position = 'top',
                        $el = $this.parent();

                    if (type == 'image' && $this.hasClass('cover')) {
                        position = 'inset-right';
                    }
                    if (type == 'image' && $this.hasClass('logo')) {
                        position = 'bottom';
                    }
                    if (type == 'employee-avatar') {
                        //Show only on header
                        if (!$el.hasClass('person-container')) {
                            return;
                        }
                        message = config.messages.employee.edit;
                        $el = $el.parent();
                        $el.attr('data-tooltip-width', 'full');
                    }
                    if ($this.hasClass('advantage-image')) {
                        position = 'left';
                    }

                    $el.attr('data-tooltip', message);
                    $el.attr('data-tooltip-pos', position);
                });
            }

            //Custom settings for different components
            let color = F.getCpSettings('color');

            //Color of the company
            if (color) {
                F.setCpColor(color, 1);
            }

            //Update variants
            F.updateVariants();

            //Update template number
            F.updateTemplateNumber();

            //Update employee
            F.updateEmployee();

            //Generate event for init slider
            wrapper.view.$el.trigger('slider:add');

            //TODO: не певен що вчасно виконається
            setTimeout(function () {
                //Clear history
                editor.getModel().set('changesCount', 0);
            }, 400);

        }, 800);

        // Show logo
        let $logoPanel = $('.gjs-pn-commands');
        $logoPanel.append('<div class="gjs-logo-cont">' +
            '<a href="/offers"><span title="Вернутся к списку КП" class="gjs-pn-btn fa fa-home"></span></a>' +
            '</div>');
    });

    //Triggered when something is stored to the storage, stored object passed as an argumnet
    editor.on('storage:store', () => {
        //Save html of the template
        F.storeHtml((response) => {
            if (editor.showStoreReponse) {
                window.message(response.message, 2000);
            }
            //Update employee
            config.offer.variants = response.offer.variants;
            config.offer.employee = response.offer.employee;
            config.offer.client_relation = response.offer.client_relation;
            config.offer.contact_person_relation = response.offer.contact_person_relation;
            config.offer.created_at_formatted = response.offer.created_at_formatted;
            config.offer.updated_at_formatted = response.offer.updated_at_formatted;
            //
            if (response.offer && editor.saveHtml) {

                //Set db-id for variant data
                F.updateVariantsProducts();
                //Update cp settings
                F.updateCpSettingsVariantProducts();

                //Update cp settings. Fix when copy from base template
                let cpSettingsModel = editor.DomComponents.getWrapper().view.$el.find('#cp-settings').data('model'),
                    cpSettings = cpSettingsModel.get('cp-settings');

                //Update   
                cpSettings.variants = response.offer.variants;

                //Add custom settings
                editor.StorageManager.get('remote').set('params', { settings: cpSettings });

                //Disable update html
                editor.saveHtml = false;
                //Disable check template
                editor.checkConfig = false;
                //Store
                editor.store();
                //Enable editor
                editor.configuring = false;
                //Disable response messages
                editor.showStoreReponse = false;
            } else {
                editor.showStoreReponse = true;
            }

            if (!editor.saveHtml) {
                //Clean up cp settings
                F.updateCpSettingsVariantProducts();
            }

            //Clear history
            editor.getModel().set('changesCount', 0);
        });
    })

    //On any error on storage request, passes the error as an argument
    editor.on('storage:error', (response) => {
        // 
        let data = jQuery.parseJSON(response);
        window.message({ error: true, text: data.errors ? data.errors : 'Сервер вернул недопустимый ответ. Проверьте правильность заполнения данных' });
    })
    // ---- Commands ----

    //
    // editor.on('run:open-toolbar', function () {
    //
    // });

    editor.on('run:open-toolbar', function () {
        // let config = editor.Config;
        let pfx = editor.Config.stylePrefix;
        let panelC;
        if (!this.obj) {
            this.obj = $('<div></div>')
                .append('<div class="' + pfx + 'toolbar-label">Настройка коммерческого предложения</div>')
                .get(0);

            let color = F.getCpSettings('color'),
                client = F.getCpSettings('client'),
                contactPerson = F.getCpSettings('contactPerson'),
                currency = F.getCpSettings('currency'),
                clientOption = '',
                currencyOptions = '';

            if (!color) {
                color = '#00a65a';
            }
            //Show selected client
            if (client) {
                clientOption = '<option value="' + client.id + '">' + client.name + '</option>';
            }

            if (config.currencies) {
                config.currencies.forEach((el) => {
                    let selected = '';
                    if (currency && el.id == currency.id) {
                        selected = 'selected';
                    } else if (el.code == 643) { //Get default currency not basic
                        selected = 'selected';
                    }
                    currencyOptions += `<option value="${el.id}" ${selected}>${el.name}</option>`;
                });
            }

            let properties = `
                <div id="gjs-sm-background-color" class="gjs-trt-trait gjs-sm-color" style="display: block;">
                    <div class="gjs-sm-label gjs-four-color">
                        <span class="gjs-sm-icon " title="">
                            Корпоративный цвет
                        </span>
                        <b class="gjs-sm-clear" data-clear-style="" style="display: none;">⨯</b>
                    </div>
                    <div class="gjs-fields">
                        <div class="gjs-field gjs-field-color">
                            <div class="gjs-input-holder"><input type="text" placeholder="none" value="`+ color + `"></div>
                            <div class="gjs-field-colorp">
                                <div class="gjs-field-colorp-c" data-colorp-c="">
                                    <div class="gjs-checker-bg"></div>
                                    <div class="gjs-field-color-picker" style="background-color: `+ color + `"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gjs-trt-trait gjs-sm-color" style="display: block;">
                    <div class="gjs-sm-label gjs-four-color clearfix">
                        <span class="gjs-sm-icon " title="">
                            Клиент
                        </span>
                        <div class="gjs-field gjs-field-checkbox" title="Отображать в КП"><label class="gjs-input-holder"><input type="checkbox" id="add-client"><i class="gjs-chk-icon"></i></label></div>
                    </div>
                    <div class="gjs-fields">
                        <div class="gjs-field gjs-field-color">
                            <div class="gjs-input-holder">
                                <select id="client">`+ clientOption + `</select> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gjs-trt-trait" style="display: block;">
                    <div class="gjs-fields">
                        <div class="gjs-field gjs-field-button">
                            <div class="gjs-input-holder">
                                <input type="button" onclick="window.utilities.$refs.client.showModal()" value="Создать клиента">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="gjs-trt-trait gjs-sm-color" style="display: block;">
                    <div class="gjs-sm-label gjs-four-color clearfix">
                        <span class="gjs-sm-icon " title="">
                            Контактное лицо
                        </span>
                        <div class="gjs-field gjs-field-checkbox" title="Отображать в КП"><label class="gjs-input-holder"><input type="checkbox" id="add-contact-person"><i class="gjs-chk-icon"></i></label></div>
                    </div>
                    <div class="gjs-fields">
                        <div class="gjs-field gjs-field-color">
                            <div class="gjs-input-holder">
                                <select id="contact-person"></select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gjs-trt-trait gjs-sm-color" style="display: block;">
                    <div class="gjs-sm-label gjs-four-color clearfix">
                        <span class="gjs-sm-icon " title="">
                            Валюта
                        </span>
                    </div>
                    <div class="gjs-fields">
                        <div class="gjs-field gjs-field-color">
                            <div class="gjs-input-holder">
                                <select id="currencies">`+ currencyOptions + `</select> 
                            </div>
                        </div>
                    </div>
                </div>
            `;

            if (config.offer.dealCardLink) {
                properties += `
                    <div class="gjs-trt-trait" style="display: block;">
                        <div class="gjs-sm-label gjs-four-color clearfix">
                            <span class="gjs-sm-icon " title="">
                                Ссылка в CRM
                            </span>
                        </div>
                        <div class="gjs-fields">
                            <div class="gjs-field gjs-field-input" style="display: block;">
                                <div class="gjs-input-holder">
                                    <input type="text" value="${config.offer.dealCardLink}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gjs-trt-trait" style="display: block;">
                        <div class="gjs-fields">
                            <div class="gjs-field gjs-field-button">
                                <div class="gjs-input-holder">
                                    <input id="reset-deal-relation" type="button" value="Отвязать">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            //Convert to object
            let html = $(`
                <div id="gjs-sm-decorations" class="gjs-sm-sector no-select gjs-sm-open toolbar" style="display: block;">
                    <!--<div class="gjs-sm-title" data-sector-title="">Decorations</div>-->
                    <div class="gjs-trt-traits" style="">
                        ${properties}
                    </div>
                </div>`).get(0);

            this.obj.appendChild(html);

            let panels = editor.Panels;
            if (!panels.getPanel('views-container')) {
                panelC = panels.addPanel({ id: 'views-container' });
            } else {
                panelC = panels.getPanel('views-container');
            }
            panelC.set('appendContent', this.obj).trigger('change:appendContent');

            /* configure spectrum color picker*/
            let $el = $(panelC.get('appendContent'));

            let changed = 0,
                previousColor = '';

            //init color picker
            $el.find('#gjs-sm-background-color .gjs-field-color-picker').spectrum({
                containerClassName: 'gjs-one-bg gjs-two-color',
                appendTo: '#gjs',
                maxSelectionSize: 8,
                showPalette: true,
                showAlpha: true,
                chooseText: 'Ok',
                cancelText: '⨯',
                palette: [],

                move(color) {
                    F.setCpColor(color);
                },
                change(color) {
                    changed = 1;
                    F.setCpColor(color, 1);
                    //Save color for current CP
                    F.updateCpSettings({ color: F.getCpColor(color) });
                },
                show(color) {
                    changed = 0;
                    previousColor = F.getCpColor(color);
                },
                hide(color) {
                    if (!changed && previousColor) {
                        F.setCpColor(previousColor);
                    }
                }
            });

            //Init change event for corporate color
            $el.find('#gjs-sm-background-color input').on('change', function () {
                let color = $(this).val();
                F.setCpColor(color, 1);
                //Save color for current CP
                F.updateCpSettings({ color: color });
            });

            //init client select
            $el.find('select#client').select2({
                placeholder: 'Начните ввод для поиска',
                dropdownParent: $el.find('select#client').parent(),
                // templateSelection: function (state) {
                //     if (!state.id) {
                //         return state.text;
                //     }
                //     var $state = $(
                //         "<span><a href='/client/" +
                //         state.id +
                //         "/edit' target='_blank'>" +
                //         state.text +
                //         "</a></span>"
                //     );
                //     return $state;
                // },
                minimumInputLength: 0,
                ajax: {
                    url: '/client/json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: $.map(data.data, function (item) {
                                if (item.typeId == 1 || item.typeId == 2) {
                                    return {
                                        id: item.id,
                                        text: item.displayName
                                    };
                                }
                            }),
                            pagination: {
                                more: (params.page * 10) < data.total
                            }
                        };
                    }
                }
            }).on("change", e => {
                F.updateCpSettings({ client: { id: $(e.target).val(), name: $(e.target).find('option:selected').text() } });
                //Update client name for template
                F.updateClient();
                //Update contact person
                F.loadContactPerson($(e.target).val());
            });

            //Update contact person
            if (client) {
                F.loadContactPerson(client.id);
            }
            if (contactPerson) {
                $el.find('select#contact-person').html('').select2({
                    dropdownParent: $el.find('select#contact-person').parent(),
                    placeholder: 'Выберите контактное лицо',
                    data: [{
                        id: contactPerson.id,
                        text: contactPerson.name
                    }]
                }).val(contactPerson.id).trigger('change');
            }
            //Add/remove client from template
            $el.find('#add-client').prop('checked', F.clientExist()).on('change', function (e) {
                if (e.target.checked) {
                    F.addClient();
                } else {
                    F.removeClient();
                }
            });

            //Add/remove contact person from template
            $el.find('#add-contact-person').prop('checked', F.contactPersonExist()).on('change', function (e) {
                if (e.target.checked) {
                    F.addContactPerson();
                } else {
                    F.removeContactPerson();
                }
            });

            let prevCurrencyId = 1;
            //Init currencies select
            $el.find('select#currencies').select2({
                dropdownParent: $el.find('select#currencies').parent(),
            })
                .on("select2:selecting", (e) => { prevCurrencyId = $(e.target).val(); })
                .on("change", e => {
                    let $el = $(e.target),
                        currency = config.currencies.find((item) => { return item.id == $el.val() }),
                        prevCurrency = config.currencies.find((item) => { return item.id == prevCurrencyId }),
                        baseCurrency = config.currencies.find((item) => { return item.basic == 1 });
                    if (currency) {
                        F.convertCurrency(currency, prevCurrency, baseCurrency);
                        F.updateCpSettings({ currency: currency });
                        F.changeCurrency(currency);
                    }
                });

            //Init reset deal relation
            $el.find('input#reset-deal-relation').on('click', function () {
                window.confirm('Вы уверены что хотите удалить связь со сделкой?', ($modal) => {
                    //hide modal
                    $modal.modal('hide');

                    window.axios.delete('/offers/' + config.offer.id + '/integration/deal')
                        .then((response) => {
                            //Clear data
                            editor.UndoManager.clear();
                            //Reload
                            location.reload();
                        })
                        .catch((error) => {
                            window.message({ text: error.response.data.errors, error: true });
                        });

                });
            });
        }

        this.obj.style.display = 'block';
    });

    editor.on('stop:open-toolbar', function () {
        if (this.obj)
            this.obj.style.display = 'none';
    });

    //When component dropped into page
    editor.on('component:add', (model) => {
        //Fix https://github.com/artf/grapesjs/pull/202
        if (model.opt.temporary) {
            return;
        }

        //check if variant selected         
        if (F.isVariantSelected() && model.get('type') && model.get('type') != 'fake') {
            F.showVariantSelectedModal();
            //remove fake block
            model.collection.remove(model);
            return;
        }

        if (model.get('type') === 'add-goods-variant') {
            //Get index for product
            let index = model.view.$el.index();
            //remove fake block
            model.get('components').parent.collection.remove(model);

            F.showProductSelectModal(index);
        }

        if (model.get('type') === 'employee-signature') {
            let clone = model.clone();

            //Fix when add model
            if ($wrapper.find('.person-container').length == 1) {
                return;
            }

            //Remove current model
            model.collection.remove(model);

            //Disable draggable,selectable
            clone.set('draggable', false);
            clone.set('selectable', false);

            //Set new model
            $wrapper.find('div.person.message__person').data('model').get('components').set(clone);

            //update footer
            let $personFooter = $wrapper.find('.person.footer__person'),
                signature = model.view.$el.find('.person__info-text').html(),
                employeeId = model.view.$el.data('id'),
                imgModel = model.view.$el.find('img').data('model').clone(),
                $economyImg = $wrapper.find('.tab-content #economy img.tab-pane-inner__description-photo'),
                $standardImg = $wrapper.find('.tab-content #standard img.tab-pane-inner__description-photo'),
                $premiumImg = $wrapper.find('.tab-content #premium img.tab-pane-inner__description-photo');

            //update small img for each variant
            if ($economyImg.length) {
                $economyImg.data('model').set('attributes', imgModel.get('attributes')).trigger('change:selectedComponent');
            }

            if ($standardImg.length) {
                $standardImg.data('model').set('attributes', imgModel.get('attributes')).trigger('change:selectedComponent');
            }

            if ($premiumImg.length) {
                $premiumImg.data('model').set('attributes', imgModel.get('attributes')).trigger('change:selectedComponent');
            }

            //Update img in footer
            $personFooter.find('img.photo_big').data('model').set('attributes', imgModel.get('attributes')).trigger('change:selectedComponent');

            $personFooter.find('.person__info-text-bottom').data('model').get('components').reset();
            $personFooter.find('.person__info-text-bottom').data('model').get('components').add(signature);

            if (employeeId) {
                //Update employee
                F.updateCpSettings({ employee: { id: employeeId } });
            }
        }
        //Add discount
        if (model.get('type') === 'add-discount') {

            let $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active');

            //control discount add
            if ($activeTab.find('.kp10-cell-discount').length > 0) {
                //remove fake block
                model.collection.remove(model);
                return;
            }

            let goodsTitle = `<div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-header-cell kp10-cell-discount" data-gjs-type="discount">Скидка</div>
                    <div class="col-md-2 hidden-xs hidden-sm tab-pane-inner__menu-header-cell kp10-cell-price-with-discount" data-gjs-type="discount">Цена со скидкой</div>`,
                hiddenOfferRow = `<div class="modal-product__count" data-gjs-type="disabled">
                    <b data-gjs-type="disabled">Скидка:</b>
                    <span class="kp10-discount" data-gjs-type="discount" data-kp10-update-prices="true" data-gjs-editable="true">0</span>
                    <span data-gjs-editable="true">%</span></div>`;

            let priceIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-price').index(),
                countIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-count').index(),
                nameIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').index();

            let cellNameWidthCurrent = F.parseI($activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').attr('class').match(/col-md-\d/g)[0]),
                cellNameWidth = cellNameWidthCurrent - 3; //3 - width of the discount

            model.collection.each(function (m, i) {
                let mComps = m.get('components');
                //exclude last
                if (m.get('type') !== 'add-discount' && !mComps.models[0].view.$el.hasClass('pane-title')) {

                    if (!m.view.$el.hasClass('tab-pane-inner__menu-row-heading')) {

                        let price = F.parseI(mComps.at(priceIndex).view.$el.find('.kp10-cell-price').text()),
                            goodsBody =
                                `<div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-cell kp10-discount" 
                                    data-gjs-type="discount" 
                                    data-kp10-update-prices="true" 
                                    data-gjs-editable="true">0</div>
                                <div class="col-md-2 hidden-xs hidden-sm tab-pane-inner__menu-cell" data-gjs-droppable="false" 
                                    data-gjs-type="discount">${F.numberFormat(price)}</div>`;

                        mComps.add(goodsBody, { at: priceIndex + 1 });

                        //update cost in hidden offer block
                        let relativeId = m.view.$el.data('src'); //get offer id
                        if (relativeId) {
                            wrapper.view.$el.children(relativeId).find('.kp10-discount-container').data('model').get('components').add(hiddenOfferRow);
                        }
                    } else {
                        mComps.add(goodsTitle, { at: priceIndex + 1 });
                    }

                    //change with of the first element
                    //find class
                    let cModel = mComps.models[nameIndex].attributes.classes.models.find(function (item) {
                        return item.id.match(/col-md-\d/g);
                    });
                    //remove class
                    mComps.models[nameIndex].attributes.classes.remove(cModel);
                    //add class
                    mComps.models[nameIndex].attributes.classes.add({ name: 'col-md-' + cellNameWidth });

                }
            });

            //add discount
            F.addSpecialDiscount();

            //remove fake block
            model.collection.remove(model);

            //Save new updates in db
            F.updateCpSettingsVariantProducts();
        }
        /**
         * Add custom coll
         */
        if (model.get('type') === 'add-goods-coll' && !model.view.el.dataset.child) {

            //if model was added from product popup
            if (model.get('attributes') && model.get('attributes')['data-parent-cid']) {
                return;
            }

            let $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
                countUserGoodColl = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-good-coll').length,
                cellNameWidthCurrent = F.parseI($activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').attr('class').match(/col-md-\d/g)[0]),
                cellNameWidth = cellNameWidthCurrent - 1, //new width
                mModel = $activeTab.find('.tab-pane-inner__menu .row').data('model'),
                goodsIndex = model.view.$el.index(),
                nameIndex = $activeTab.find('.tab-pane-inner__menu-header-cell.kp10-cell-name').index(),
                html = `<div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-cell kp10-good-coll" data-gjs-type="add-goods-coll"
                    data-child="true" data-gjs-editable="true" data-kp10-update-prices="true">0</div>`,
                hiddenOfferRow = `<div class="modal-product__count" data-gjs-type="disabled">
                    <b data-gjs-type="disabled">Столбец</b>
                    <strong data-gjs-type="disabled">: </strong>
                    <span data-kp10-update-prices="true" data-child="true" data-gjs-type="add-goods-coll" data-gjs-editable="true">0</span></div>`;

            if (mModel && mModel.collection && mModel.collection.models) {
                //save current model index for future delete relative coll
                model.set('indexForRemove', goodsIndex);

                //rows
                for (let i in mModel.collection.models) {

                    let mComps = mModel.collection.models[i].get('components');

                    //don't use group title
                    if (!mComps.models[0].view.$el.hasClass('pane-title')) {

                        //add cell except header row
                        if (i != 0) {
                            let goodsModel = mComps.add(html, { at: goodsIndex });
                            goodsModel.set('parentIndexForRemove', goodsIndex);

                            //Set default checked
                            goodsModel.set('valueInPrice', true);

                            //update cost in hidden offer block
                            let relativeId = mModel.collection.models[i].view.$el.data('src'); //get offer id

                            if (relativeId) {
                                let hiddenCol = wrapper.view.$el.children(relativeId).find('.kp10-goods-coll').data('model').get('components').add(hiddenOfferRow, { at: goodsIndex });
                                hiddenCol.set('parentIndexForRemove', goodsIndex);
                            }
                        }

                        //find class
                        let cModel = mComps.models[nameIndex].attributes.classes.models.find(function (item) {
                            return item.id.match(/col-md-\d/g);
                        });
                        //remove class
                        mComps.models[nameIndex].attributes.classes.remove(cModel);
                        //add class
                        mComps.models[nameIndex].attributes.classes.add({ name: 'col-md-' + cellNameWidth });

                    }
                }

                //refresh
                F.calculatePositionsPrices();

                editor.saveHtml = true;
                // editor.saveHtmlshowMessage = false;
                //Save new updates in db
                F.updateCpSettingsVariantProducts();
            }
        }

        // if (model.get('type') === 'add-variant') {
        //     let variantType = model.view.$el.data('type'),
        //         $navTabs = wrapper.view.$el.find('ul.nav-tabs');

        //     //check if variant already exists
        //     if ($navTabs.find('a[href^="#' + variantType + '"]').length > 1) {
        //         //remove fake block
        //         model.collection.remove(model);
        //     }
        //     if ($navTabs.find('a[href^="#' + variantType + '"]').length === 1) {
        //         F.addVariantBody(model);
        //     }
        // }

        //
        if (model.get('type') === 'add-special-discount') {
            F.addSpecialDiscount();

            //Save new updates in db
            editor.saveHtml = true;
            F.updateCpSettingsVariantProducts();
            //Change currency
            F.changeCurrencyFromSettings();
        }

        //Gallery
        if (model.get('type') === 'gallery') {

            //generate unique id
            let d = new Date(),
                galleryId = d.getTime(),
                path = config.path + '/' + config.storage.gallery;

            //add gallery attribute
            model.set('attributes', {
                'data-gallery-id': galleryId
            });

            editor.runCommand('open-assets');

            //Change path in config
            amConfig.params.path = path;
            //Show only gallery folder
            am.render(am.getAll().filter(
                asset => asset.get('folder') === path
            ));

            //add new button to assets manager
            F.addGalleryBtnToAsset(galleryId);
        }

        //Slider
        if (model.get('type') === 'slider') {

            //generate unique id
            let d = new Date(),
                galleryId = d.getTime(),
                path = config.path + '/' + config.storage.gallery;

            //add gallery attribute
            model.set('attributes', {
                'data-gallery-id': galleryId
            });

            editor.runCommand('open-assets');

            //Change path in config
            amConfig.params.path = path;
            //Show only gallery folder
            am.render(am.getAll().filter(
                asset => asset.get('folder') === path
            ));

            //add new button to assets manager
            F.addGalleryBtnToAsset(galleryId);
        }

        //Add products group
        if (model.get('type') === 'add-goods-group') {
            //When using copyProducts function
            if (model.get('data-copied')) {
                return;
            }
            setTimeout(function () {
                if (model.view.$el.index() == 0) {
                    model.collection.add(model.clone(), { at: 2 });
                    //remove from wrong place
                    model.collection.remove(model);
                    return;
                }
                //Update group sum
                F.calculatePositionsPrices();

                //Update for save in DB
                F.updateCpSettingsVariantProducts();
                editor.saveHtml = true;
            }, 100);
            //Update currency relative to current settings
            F.changeCurrencyFromSettings();
        }

        //Controll add client
        if (model.get('type') === 'client') {
            //Controll client count
            if (model.view.$el.closest('.cp-details-about').find('div.row.client').length > 1) {
                //remove duplicate
                model.collection.remove(model);
                return;
            }
            //Update client for template
            F.updateClient();

        }
        //Controll add contact person
        if (model.get('type') === 'contact-person') {
            //Controll client count
            if (model.view.$el.closest('.cp-details-about').find('div.row.contact-person').length > 1) {
                //remove duplicate
                model.collection.remove(model);
                return;
            }
            //Update contact person for template
            F.updateContactPerson();
        }
        //Controll add download pdf
        if (model.get('type') === 'download-pdf') {
            if (model.view.$el.closest('.cp-details__buttons-col').find('.download-pdf').length > 1
                || !model.view.$el.parent().hasClass('cp-details__buttons-col')) { //Fix for drag element
                //remove duplicate
                model.collection.remove(model);
                return;
            }
        }
        //Controll add download excel
        if (model.get('type') === 'download-excel') {
            if (model.view.$el.closest('.cp-details__buttons-col').find('.download-excel').length > 1
                || !model.view.$el.parent().hasClass('cp-details__buttons-col')) { //Fix for drag element
                //remove duplicate
                model.collection.remove(model);
                return;
            }
        }
        //Controll add download pdf full
        if (model.get('type') === 'download-pdf-full') {
            if (model.view.$el.closest('.cp-details__buttons-col').find('.download-pdf-full').length > 1
                || !model.view.$el.parent().hasClass('cp-details__buttons-col')) { //Fix for drag element
                //remove duplicate
                model.collection.remove(model);
                return;
            }
        }
        //Controll add download pdf full
        if (model.get('type') === 'variant') { //Fix for drag element
            //remove duplicate
            model.collection.remove(model);
            return;
        }
        //Controll add integration field
        if (model.get('type') == 'integration-field') {
            //Find field type
            let $integration = model.view.$el.find('[data-gjs-type^=integration]');

            if ($integration.length && $wrapper.find('[data-gjs-type="' + $integration.attr('data-gjs-type') + '"]').length > 1) {
                //remove duplicate
                model.collection.remove(model);
                return;
            }
            //After field added. Update field content
            //Megaplan
            if (config.offer.megaplan_deal && config.offer.megaplan_deal.values) {
                //Find field
                let dataField = config.offer.megaplan_deal.values.find((value) => {
                    return model.view.$el.attr('data-field-id') == value.field.field_id;
                });
                //
                if (dataField) {
                    let value = dataField.megaplan_field_values,
                        fieldIntegration = config.integration.fields.find((el) => { return el.id == dataField.field_id });
                    //Change value by type
                    if (fieldIntegration) {
                        //Bool
                        if (fieldIntegration.content_type == 'BoolField') {
                            value = value == 1 ? 'Да' : 'Нет';
                        }
                        //Дата
                        if (fieldIntegration.content_type == 'DateField' || fieldIntegration.content_type == 'DateTimeField') {
                            value = F.formatIntegrationFieldDate(dataField);
                        }
                    }

                    F.updateContent(model.view.$el.children('div').eq(1).data('model'), value);
                }
            }
            //Amocrm
            if (config.offer.amocrm_deal && config.offer.amocrm_deal.data && config.offer.amocrm_deal.data.fields) {
                //Find field
                let dataField = config.offer.amocrm_deal.data.fields.find((field) => {
                    return model.view.$el.attr('data-field-id') == field.amocrm_field_id;
                });
                //
                if (dataField && dataField.values) {
                    let value = dataField.values[0].amocrm_field_value,
                        fieldIntegration = config.integration.fields.find((el) => { return el.amocrm_field_id == dataField.amocrm_field_id });

                    //Change value by type
                    if (fieldIntegration) {
                        //Флаг
                        if (fieldIntegration.amocrm_field_type_id == 3) {
                            value = value == 1 ? 'Да' : 'Нет';
                        }
                        //Дата | День рождения
                        if ([6, 14].indexOf(fieldIntegration.amocrm_field_type_id) !== -1 && value) {
                            value = F.formatIntegrationFieldDate(dataField.values[0]);
                        }
                    }

                    F.updateContent(model.view.$el.children('div').eq(1).data('model'), value);
                }
            }
            //Bitrix24
            if (config.offer.bitrix24_deal && config.offer.bitrix24_deal.data && config.offer.bitrix24_deal.data.fields) {
                //Find field
                let dataField = config.offer.bitrix24_deal.data.fields.find((field) => {
                    return model.view.$el.attr('data-field-id') == field.bitrix24_field_id;
                });
                //
                if (dataField && dataField.values) {
                    let value = dataField.values[0].bitrix24_field_value,
                        fieldIntegration = config.integration.fields.find((el) => { return el.bitrix24_field_id == dataField.bitrix24_field_id });

                    //Change value by type
                    if (fieldIntegration) {
                        //boolean
                        if (fieldIntegration.bitrix24_field_type_id == 'boolean') {
                            value = value == 1 ? 'Да' : 'Нет';
                        }
                        //date
                        if (fieldIntegration.bitrix24_field_type_id == 'date' && value) {
                            value = F.formatIntegrationFieldDate(dataField.values[0]);
                        }
                        //money
                        if (fieldIntegration.bitrix24_field_type_id == 'money') {
                            //Get value without currency
                            value = value.split('|')[0];
                        }
                    }

                    F.updateContent(model.view.$el.children('div').eq(1).data('model'), value);
                }
            }
        }

    });

    //When component dropped into page
    editor.on('component:remove', (model) => {
        //Fix https://github.com/artf/grapesjs/pull/202
        if (model.opt.temporary) {
            return;
        }

        let type = model.get('type');

        if (type === 'add-goods-coll') {
            F.removeGoodColl(model);
        }

        if (type === 'gallery-img') {
            F.removeGalleryImg(model);
        }

        if (type === 'slider-img') {
            F.removeSliderImg(model);
        }

        if (type === 'goods-variant') {
            F.removeProduct(model);
        }

        if (type === 'add-special-discount') {
            F.removeSpecialDiscount(model);
        }

        //Integration crm field
        if (type == 'integration-field') {
            //Find field type
            let $integration = model.view.$el.find('[data-gjs-type^=integration]');
            //If row removed
            if ($integration.length && !$wrapper.find('[data-gjs-type="' + $integration.attr('data-gjs-type') + '"]').length) {
                F.removeCpSettingsIntegrationField(model.view.$el.attr('data-id'));
            }
        }

    });

    //when editor enabled
    editor.on('rte:enable', (model) => {

        //check if variant selected         
        if (F.isVariantSelected()) {
            //Disable rte
            model.rte.disable(model, model.rteEnabled);

            F.showVariantSelectedModal();
        }

        //Hide rte toolbar only for numeric
        // if (model.$el.attr('data-kp10-update-prices')) {
        editor.on('rteToolbarPosUpdate', (pos) => {
            pos.top = 10000;
        });
        // }

        model.$el.on('paste', function (e) {
            _.delay(() => {
                //Update page content. Remove tags
                $(this).html(F.removeTagsFromEditable($(this)));
            }, 100);
        });
    });

    //after editor was closed
    editor.on('rte:disable', (model) => {
        //Disable event
        model.$el.off('paste');

        let type = model.model.get('type'),
            updatePrices = model.$el.attr('data-kp10-update-prices'),
            text = model.$el.html();

        //Integration fields
        if (type.indexOf('integration-field') !== -1) {
            //Parse value
            text = F.parseIntegrationFieldValue(type, text);
            //Update value
            F.updateContent(model.model, text);
            //Store for update in CRM
            F.updateCpSettingsIntegrationField({ type: type, value: text });
            return;
        }

        //Convert all to text
        F.updateContent(model.model, text);

        if (updatePrices && type !== 'text' && type != 'editable') {
            //Convert to number
            F.updateContent(model.model, F.parseF(text + ''));
        }

        //control empty text
        if (!model.$el.text()) {
            F.updateContent(model.model, 'Введите текст'); //TODO: не завжди потрібен текст
        }

        if (updatePrices) {
            //update all relative model data
            F.updateProductModel(F.prepareModelRteProduct(model));

            //update calculate positions
            F.calculatePositionsPrices();
        }

        if (type === 'add-goods-coll') {
            F.updateProductGoodsColl(model);
        }

        if (type === 'variant-name') {
            let text = model.$el.text();

            if (model.$el.hasClass('tab-pane-inner__title')) {
                let $navTabName = wrapper.view.$el.find('ul.nav-tabs a[href="#' + model.$el.closest('[role="tabpanel"]').attr('id') + '"] .kp10-header-name');
                F.updateContent($navTabName.data('model'), text);
            }
            //update from tab
            if (model.$el.hasClass('kp10-header-name')) {
                let $contentName = wrapper.view.$el.find('.tab-content ' + model.$el.closest('a').attr('href') + ' h2.tab-pane-inner__title');
                F.updateContent($contentName.data('model'), text);
            }

            //Store new variant name
            F.updateCpSettingsVariant();
        }
        //Update values for product columns and field value
        F.addValueToCpVariant(model);
    });

    //Commands
    commands.add('send-email', {
        run(editor, sender) {
            //inactive button
            sender && sender.set('active', false);

            //TODO: додати модалку, після збереження КП пропонувати надіслати !

            if (editor.getModel().get('changesCount')) {
                window.message('Перед отправкой КП нажмите кнопку Сохранить');
                _.delay(window.hideMessage, 2000);
                return;
            }

            //Show modal send mail
            window.utilities.showModalSendEmail();

        },
        stop(editor, sender) {

        },
    });
    //Toolbar add image to gallery
    // commands.add('tlb-add-to-gallery', {
    //     run:  function(editor, sender){
    //         console.log('run');
    //     },
    //     stop:  function(editor, sender){
    //     },
    // });

    //Events
    $(document).on('click', '#level-up', function (e) {
        e.stopPropagation();
        let path = amConfig.params.path;
        if (path) {
            let pathArr = path.split('/');
            pathArr.pop();
            if (pathArr.length >= 2) {
                path = pathArr.join('/');
                //update path for upload
                amConfig.params.path = path;
                //show only selected folder
                F.showAmFolder(path);
                // am.render(am.getAll().filter(
                //     asset => asset.get('folder') === path
                // ));
            }
        }
    });

    // $(document).on('click', '#create-folder', function (e) {
    //     e.stopPropagation();
    //     let folder = prompt('Введите название папки', ''),
    //         path = amConfig.params.path,
    //         data = { path: path, folder: folder, _token: _csrf };

    //     if (folder && path) {

    //         axios.post('/file-manager/folder', data)
    //             .then(function (response) {
    //                 if (response) {
    //                     am.add({
    //                         category: 'folder',
    //                         type: 'folder',
    //                         folder: path, //using for filtering
    //                         name: folder,
    //                         path: path + '/' + folder
    //                     });
    //                     //show only selected folder
    //                     F.showAmFolder(path);
    //                     // am.render(am.getAll().filter(
    //                     //     asset => asset.get('folder') === path
    //                     // ));
    //                 }
    //             })
    //             .catch(function (error) {
    //                 window.message({ text: error.response.data.errors, error: true });
    //             });
    //     }
    // });

    //Gallery images
    $(document).on('click', '#add-to-gallery', function (e) {
        e.stopPropagation();

        F.updateGallery($(this).data('gallery-id'));

    });

    // --- End Assets --- //

    // --- Add product --- //
    var addedProducts = 0;
    $(document).on('keyup', '#product-search', _.debounce(function (e) {
        //Clear 
        addedProducts = 0;

        let search = e.target.value;
        if (search) {
            $('#add-new-product').show();
        } else {
            $('#add-new-product').hide();
        }

        let tableHead =
            '<div class="kp10-add-goods-variant">' +
            '<div class="kp10-goods-list">' +
            '<table>' +
            '<thead>' +
            '<th>Артикул</th>' +
            '<th>Название</th>' +
            '<th>Описание</th>' +
            '<th>Цена</th>' +
            // '<th>Себестоимость</th>' +
            '</thead>' +
            '<tbody>' +
            '</tbody>' +
            '</table>' +
            '</div>' +
            '</div>';

        window.axios.get('/products/list/json', { params: { search: search } })
            .then((response) => {
                let data = response.data,
                    products = data.products,
                    html = '';

                if (products.length) {

                    modal.setContent(tableHead);

                    let $addGoodsVariant = $('.kp10-add-goods-variant');

                    $.each(products, function (index, product) {
                        //Control new product
                        if (!config.products.find(el => el.id === product.id)) {
                            //Fix
                            product.photo = product.file;
                            //Add new product
                            config.products.push(product);
                        }
                        let fullDesription = '';
                        if (product.description && product.description.length > 65) {
                            fullDesription = '<a href="#" class="full-description"><i class="fa fa-caret-down"></i></a>';
                        }

                        html +=
                            '<tr data-id="' + product.id + '">' +
                            '<td><input type="checkbox"> ' + product.article + '</td>' +
                            '<td class="name"><div><img src="' + product.file + '" width="46"> <span>' + product.name + '</span></div></td>' +
                            '<td class="description"><div class="short-desription">' + (product.description || '') + '</div>' + fullDesription + '</td>' +
                            '<td class="cost">' + F.parseF(product.cost) + '</td>' +
                            // '<td class="prime-cost">' + F.parseF(product.prime_cost) + '</td>' +
                            '</tr>';
                    });

                    $addGoodsVariant.find('table tbody').html(html);
                } else {
                    modal.setContent(
                        `<p>Товар не найден. Вы можете импортировать товары через Excel в разделе <a href="/products" target="_blank">Товары</a></p>`
                    );
                }

                //Save products in model
                // config.products = products;

            })
            .catch(error => window.message({ text: error.response.data.errors, error: true }));

    }, 500));
    //Show full product description
    $(document).on('click', '.kp10-goods-list .full-description', function (e) {
        e.preventDefault();
        let $icon = $(this).find('i');
        if ($icon.hasClass('fa-caret-down')) {
            $icon.removeClass('fa-caret-down').addClass('fa-caret-up');
            $(this).parent().children(0).removeClass('short-desription');
        } else {
            $icon.addClass('fa-caret-down').removeClass('fa-caret-up');
            $(this).parent().children(0).addClass('short-desription');
        }
    });
    //Events
    $(document).on('change', '.kp10-add-goods-variant input[type=checkbox]', function (e) {
        e.stopPropagation();
        let $row = $(this).closest('tr'),
            status = $(this).is(':checked') ? 1 : 0,
            id = $row.data('id'),
            name = $row.find('.name span').text(),
            photo = $row.find('img').attr('src'),
            cost = $row.find('.cost').text(),
            description = $row.find('.description').html(),
            $addedProducts = $('#added-products'),
            index = $('#product-search').attr('data-index');

        //generate unique id for product
        let d = new Date(),
            cProductId = d.getTime(); //new product id

        if (status) {
            F.addProduct({
                id: id,
                cProductId: cProductId,
                name: name,
                file: photo,
                cost: cost,
                description: description,
                index: index
            });

            //Count added products
            addedProducts += 1;

            if (addedProducts > 0) {
                //Добавлено товаров: 14
                $addedProducts.show();
                $addedProducts.children('span').text(addedProducts);
            } else {
                $addedProducts.hide();
            }

        } else {
            // //find model by view attr
            // for (let i in mModel.collection.models) {
            //     if (id === parseInt(mModel.collection.models[i].view.attr['data-id'])) {
            //         //remove model from collection
            //         mModel.collection.remove(mModel.collection.models[i]);
            //     }
            // }
        }

    });

    $(document).on('click', '#add-new-product', function (e) {

        e.stopPropagation();
        let $variant_name = $('input#product-search'),
            name = $variant_name.val(),
            photo = config.productEmptyImg,
            description = 'Введите описание товара',
            cost = '100';

        if (name) {
            //generate unique id for product
            let d = new Date(),
                cProductId = d.getTime();

            //Add new product
            F.addProduct({
                id: cProductId,
                cProductId: cProductId,
                name: name,
                file: photo,
                cost: cost,
                description: description,
                fake: true //marker for checking product in products
            });

            //Hide modal
            modal.close();
        } else {
            window.message('Необходимо ввести имя товара');
        }

    });

    //Advantage search icons
    $(document).on('keyup', '#advantage-search', function () {
        F.delay(() => {
            //Show only advantage icons
            am.render(am.getAll().filter(
                asset => asset.get('type') === 'advantage-image' && asset.get('cls').match(this.value)
            ));
        }, 500);
    });

    //Cancel variant selection 
    $(document).on('click', '#cancel-variant-selection', function () {
        //Add preloader/ disable event
        $(this).addClass('disabled').closest('.gjs-mdl-content').append('<div class="loader"></div>');

        axios.post(location.href + '/cancel-variant-selection')
            .then(function (response) {
                //Clear data
                editor.UndoManager.clear();
                //reload page
                location.reload();
            })
            .catch(function (error) {
                window.message({ text: error.response.data.errors, error: true });
            });
    });

    //Fix for prevent double click on file uploading
    var fileTimeout = null;
    $(document).on('click', '.gjs-am-file-uploader', function (e) {
        //Check target
        if (e.target.id === 'gjs-am-uploadFile') {
            return;
        }

        e.stopPropagation();
        e.preventDefault();

        clearTimeout(fileTimeout);

        fileTimeout = setTimeout(() => {
            //Open filebox
            $(document).find('.gjs-am-file-uploader input[type="file"]').click();
        }, 500);
    });

    //Fix for bootstrap
    //Events for tabs
    wrapper.view.$el.on('click', '.nav-tabs a', function (e) {
        //remove class from content
        let tabModel = wrapper.view.$el.find('.tab-content .tab-pane.active').data('model');
        if (tabModel.attributes.classes) {
            tabModel.attributes.classes.each(function (item) {
                if (item && item.id === 'active') {
                    tabModel.attributes.classes.remove(item);
                }
            });
        }

        //remove class from tabs
        let navModel = wrapper.view.$el.find('.nav-tabs > li.active').data('model');
        if (navModel.attributes.classes) {
            navModel.attributes.classes.each(function (item) {
                if (item && item.id === 'active') {
                    navModel.attributes.classes.remove(item);
                }
            });
        }
        //add class to content
        wrapper.view.$el.find('.tab-content ' + $(this).attr('href')).data('model').attributes.classes.add({ name: 'active' });
        //add class to tab
        wrapper.view.$el.find('.nav-tabs a[href^="' + $(this).attr('href') + '"]').parent().data('model').attributes.classes.add({ name: 'active' });
    });

    //list/table view
    wrapper.view.$el.on('click', ' a.nav-offer__link', function () {

        let $container = $(this).closest('.container-inner');

        $(this).closest('ul').find('li').removeClass('active');
        $(this).parent().addClass('active');

        $container.find('.tab-content__table,.tab-content__list').removeClass('active');
        $container.find('.tab-content ' + $(this).attr('href')).addClass('active');
    });

    //build fake fancybox
    wrapper.view.$el.on('click', '.kp10-js-fancybox-product', function () {

        let $product = wrapper.view.$el.children($(this).data('src'));
        if ($product.length) {
            let productModel = $product.data('model').clone(),
                fancyModel = wrapper.view.$el.find('.fancybox-container').data('model');

            //set id of the editable model
            productModel.set('relativeId', $product.attr('id'));
            //apply new model
            wrapper.view.$el.find('.fancybox-container .fancybox-stage > .fancybox-slide').data('model').get('components').add(productModel);
            //show modal
            fancyModel.get('classes').remove(fancyModel.get('classes').at(4));
        } else {
            window.message({ text: 'Error. Fancybox offer not found', error: true });
        }
        //editor.select(fancyModel);
    });

    //close popup
    wrapper.view.$el.on('click', '.fancybox-container .fancybox-close-small', function () {

        let $activeTab = wrapper.view.$el.find('.tab-content .tab-pane.active'),
            $fancyContainer = wrapper.view.$el.find('.fancybox-container'),
            offerModel = $fancyContainer.find('.fancybox-stage > .fancybox-slide > div').data('model'),
            relativeId = offerModel.get('relativeId'),
            $tableRow = $activeTab.find('.tab-content__table .js-fancybox-offer[data-src="#' + relativeId + '"]');

        //clone real model
        offerModel = $fancyContainer.find('.fancybox-stage > .fancybox-slide div.modal-product').data('model').clone();

        $fancyContainer.data('model').get('classes').add({ name: 'display-none' });

        let offerImg = $fancyContainer.find('img.modal-product__preview-img').data('model').clone(),
            offerName = $fancyContainer.find('.modal-product__title').text().trim(),
            offerCount = $fancyContainer.find('.kp10-cell-count').text().trim(),
            offerPrice = $fancyContainer.find('.modal-product__price > .kp10-cell-price').text().trim();

        //need destroy model if rows from fancybox was deleted
        wrapper.view.$el.children('#' + relativeId).children('.modal-product').data('model').destroy();
        wrapper.view.$el.children('#' + relativeId).data('model').get('components').add(offerModel);
        //update id trait of the offer model (need for save template in DB)
        if (wrapper.view.$el.children('#' + relativeId).data('model').get('traits').models[0]) {
            wrapper.view.$el.children('#' + relativeId).data('model').get('traits').models[0].set('value', relativeId);
        }

        F.updateProductModel({
            relativeId: '#' + relativeId,
            imgAttributes: {
                src: offerImg.get('src'),
                attributes: { src: offerImg.get('src'), 'data-src': '#' + relativeId }
            },
            name: offerName,
            count: offerCount,
            price: offerPrice,
        });

        //Update cp settings for updating in DB
        F.updateCpSettingsVariant({
            product: {
                'db-id': $tableRow.attr('data-db-id'),
                image: offerImg.get('src'),
                fakeProductId: '#' + relativeId
            }
        });

        //remove old model
        $fancyContainer.find('.fancybox-stage > .fancybox-slide > div').data('model').destroy();

        //update prices
        F.calculatePositionsPrices();
    });

    //Fix for slide
    // wrapper.view.$el.on('click', '.cbp-fwslider .cbp-fwprev', function () {
    //     console.log('fprev');
    // });
    // wrapper.view.$el.on('click', '.cbp-fwslider .cbp-fwnext', function () {
    //     console.log('fwnext');
    // });

    //
    // function getFolderContent(path) {

    //     let pathExist = false;
    //     //Check if path exist in model
    //     am.getAll().each(function (k) {
    //         if (path === k.get('folder')) {
    //             pathExist = true;
    //         }
    //     });

    //     if (path && pathExist === false) {
    //         $.ajax({
    //             url: "/editor/folder-content",
    //             data: { path: path, _token: _csrf },
    //             type: "GET",
    //             success: function (response) {

    //                 if (response.files) {
    //                     for (let f in response.files) {
    //                         let file = response.files[f];

    //                         am.add({
    //                             folder: response.folder, //using for filtering
    //                             src: file.file
    //                         });
    //                     }
    //                 }

    //                 if (response.directories) {
    //                     for (let d in response.directories) {
    //                         let directory = response.directories[d];
    //                         am.add({
    //                             category: 'folder',
    //                             folder: response.folder, //using for filtering
    //                             name: directory.name,
    //                             path: directory.path
    //                         });
    //                     }
    //                 }
    //                 //show only selected folder
    //                 am.render(am.getAll().filter(
    //                     asset => asset.get('folder') == response.folder
    //                 ));
    //             }
    //         });
    //     } else {
    //         //show only selected folder
    //         am.render(am.getAll().filter(
    //             asset => asset.get('folder') == path
    //         ));
    //     }
    // }

    function startAnimation() {
        $assetManager.find('.gjs-am-file-uploader, .gjs-am-assets-cont').addClass('disabled');
        $assetManager.append('<div class="loader"></div>');
    }

    function endAnimation() {
        $assetManager.find('.gjs-am-file-uploader, .gjs-am-assets-cont').removeClass('disabled');
        $assetManager.find('.loader').remove();
    }
});

/**
 Скидка
 Атрибут data-kp10-update-prices="true" необхідний для розрахунку поля Стоимость(спрацьовує calculatePositionsPrices() )


 */