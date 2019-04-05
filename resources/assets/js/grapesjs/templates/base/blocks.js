export default (editor, config = {}, F) => {
    const blockManager = editor.BlockManager;

    //TODO необхідно передавати список співробітників !!!
    /****************** Функции ******************/

    blockManager.add('add-goods-variant', {
        label: 'Товар',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-cart-plus gjs-block'
        },
        content: '<div class="add-goods-variant" data-gjs-type="add-goods-variant" data-gjs-draggable=".tab-pane-inner__menu,.tab-content__list,.tab-pane-inner"></div>'
    });

    blockManager.add('add-goods-group', {
        label: 'Группа товаров',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-object-group gjs-block'
        },
        content:
            `<div class="row tab-pane-inner__menu-row" data-gjs-type="add-goods-group" >
                <div class="col-md-12 tab-pane-inner__menu-cell pane-title" data-gjs-type="disabled">
                    <span data-gjs-type="editable">Группа</span> 
                    (<span class="kp10-group-cost" data-gjs-type="disabled">0</span>
                    <i class="fa fa-rub currency" data-gjs-type="disabled"></i>)
                </div>
            </div>`
    });

    blockManager.add('add-discount', {
        label: 'Скидка', //data-gjs-droppable=".row-cell"
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-percent gjs-block' //gjs-fonts gjs-f-b1 gjs-block
        },
        content: '<div class="add-discount" data-gjs-type="add-discount" data-gjs-draggable=".tab-pane-inner__menu" data-gjs-copyable="false"></div>',
    });

    blockManager.add('add-special-discount', {
        label: 'Специальная скидка', //data-gjs-droppable=".row-cell"
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-percent gjs-block'
        },
        content:
            `<div class="kp10-row-special-discount" data-gjs-type="add-special-discount">
                <span data-gjs-type="editable">Специальная скидка</span>
                    (<span class="kp10-special-discount-value" data-gjs-type="editable">0</span> 
                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>)
            </div>`
    });

    blockManager.add('add-goods-coll', {
        label: 'Столбец товаров',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-columns gjs-block'
        },
        content: 
            `<div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-header-cell kp10-good-coll" 
                data-gjs-type="add-goods-coll"
                data-gjs-draggable="div.tab-pane-inner__menu-row-heading"
                data-gjs-removable="true">Столбец</div>`
    });

    blockManager.add('text', {
        label: 'Текст',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-font gjs-block'
        },
        content: '<div class="custom-text-indent custom-text-row" data-gjs-draggable=".text-row" data-gjs-badgable="false">Текст</div>'
    });

    blockManager.add('title-h1', {
        label: 'Заголовок H1',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-header gjs-block'
        },
        content: '<h1 class="cp-details__title custom-text-indent" data-gjs-draggable=".title-row" data-gjs-badgable="false">Заголовок H1</h1>'
    });

    blockManager.add('title-h2', {
        label: 'Заголовок H2',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-header gjs-block'
        },
        content: '<h2 class="cp-options__title custom-text-indent" data-gjs-draggable=".title-row" data-gjs-badgable="false">Заголовок H2</h2>'
    });

    blockManager.add('gallery', {
        label: 'Галерея',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-file-image-o gjs-block'
        },
        content: 
        `<section class="carousel" data-gjs-type="gallery"> 
            <div class="container-inner" data-gjs-type="disabled"> 
                <div class="alternative-container-inner title-row" data-gjs-type="disabled" data-gjs-droppable="h1"> 
                    <div class="csslider infinity carousel-big" data-gjs-type="disabled">
                        <ul data-gjs-badgable="false" data-gjs-type="disabled"> </ul> 
                        <div class="arrows" data-gjs-type="disabled"> 
                            <label class="goto-first arrow corporate-bg-color" for="slides_1" data-gjs-type="disabled"></label> 
                            <label class="goto-last arrow corporate-bg-color" for="slides_1" data-gjs-type="disabled"></label> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </section>`
    });

    blockManager.add('slider', {
        label: 'Слайдер',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-image gjs-block'
        },
        content: 
            `<div class="cbp-fwslider slider"> 
                <ul data-gjs-type="disabled"> </ul> 
            </div>`
    });

    blockManager.add('advantages', {
        label: 'Преимущество',
        category: 'Функции',
        attributes: {
            'title': '',
            'class': 'fa fa-star-o gjs-block'
        },
        content: `
        <div class="col-md-6 advantage-block advantage">  
            <div class="media" data-gjs-type="disabled"> 
                <div class="advantage-content" data-gjs-type="disabled"> 
                    <div class="advantage-img" data-gjs-type="disabled"> 
                        <i class="fa fa-image advantage-image corporate-color"></i> 
                    </div>  
                    <div class="media-body" data-gjs-type="disabled"> 
                        <div class="advantage-title advantage corporate-color" data-gjs-type="editable">Преимущество 8</div> 
                        <div data-gjs-type="editable">Опишите более подробно преимущество 8</div> 
                    </div> 
                </div> 
            </div> 
        </div>`
    });

    /****************** Детали заказа ******************/

    blockManager.add('download-excel', {
        label: 'Скачать Excel',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-file-excel-o gjs-block'
        },
        content: `
            <a href="#" data-gjs-type="download-excel" class="button-feature_excel download-excel corporate-color">
                <i class="fa fa-file-excel-o" data-gjs-type="disabled"></i>
                <span data-gjs-type="editable">Коммерческое предложение<br>в формате xls</span>
            </a>`
    });

    blockManager.add('download-pdf', {
        label: 'Скачать PDF',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-file-pdf-o gjs-block'
        },
        content: `
            <a data-gjs-type="download-pdf" class="button-feature_excel download-pdf corporate-color" href="#" id="download_pdf" >
                <i class="fa fa-file-pdf-o" data-gjs-type="disabled"></i>
                <span data-gjs-type="editable">Коммерческое предложение<br>в формате pdf</span>
            </a>`
    });

    blockManager.add('download-pdf-full', {
        label: 'Расширенный PDF',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-file-pdf-o gjs-block'
        },
        content: `
            <a data-gjs-type="download-pdf-full" class="button-feature_excel download-pdf-full corporate-color" href="#">
                <i class="fa fa-file-pdf-o" data-gjs-type="disabled"></i>
                <span data-gjs-type="editable">Коммерческое предложение<br>с подробным описанием</span>
            </a>`
    });

    blockManager.add('client', {
        label: 'Клиент',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-user gjs-block'
        },
        content: `
            <div data-gjs-type="client" class="row add-order client">
                <div class="col-md-6 cp-details-about-cell" data-gjs-type="editable">Клиент</div>
                <div class="col-md-6 cp-details-about-cell" data-gjs-type="disabled" data-tooltip="${config.messages.openElementSettings}"> </div>
            </div>`
    });
    
    blockManager.add('contact-person', {
        label: 'Контактное лицо',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-user gjs-block'
        },
        content: `
            <div data-gjs-type="contact-person" class="row add-order contact-person">
                <div class="col-md-6 cp-details-about-cell" data-gjs-type="editable">Контактное лицо</div>
                <div class="col-md-6 cp-details-about-cell" data-gjs-type="disabled" data-tooltip="${config.messages.openElementSettings}"> </div>
            </div>`
    });
    
    blockManager.add('add-order-detail', {
        label: 'Строка',
        category: 'Детали заказа',
        attributes: {
            'title': '',
            'class': 'fa fa-list-alt gjs-block'
        },
        content: `
        <div class="row add-order"
            data-gjs-type="disabled"
            data-gjs-draggable="div.cp-details-about"
            data-gjs-removable="true"
            data-gjs-selectable="true">
            <div class="col-md-6 cp-details-about-cell" data-gjs-type="editable">Деталь заказа</div>
            <div class="col-md-6 cp-details-about-cell" data-gjs-type="editable">0</div>
        </div>`
    });

    //Integration fields
    if (config.integration && config.integration.fields) {
        //Megaplan
        if (config.integration.system_crm_id === 1) {

            let editable = '',
                title = 'data-tooltip="' + config.messages.openElementSettings + '"',
                editableTypes = ['StringField', 'MoneyField', 'FloatField'];

            config.integration.fields.forEach((field) => {

                if (editableTypes.indexOf(field.content_type) !== -1) {
                    editable = 'data-gjs-editable="true" data-gjs-selectable="false"';
                    //Remove title
                    title = '';
                } else {
                    editable = '';
                    title = 'data-tooltip="' + config.messages.openElementSettings + '"';
                }

                blockManager.add('add-order-detail-' + field.field_id, {
                    label: field.field_name,
                    category: 'Детали заказа',
                    attributes: {
                        'title': '',
                        'class': 'fa fa-align-justify gjs-block'
                    },
                    content:
                        `<div class="row add-order" 
                            data-gjs-type="integration-field"
                            data-field-id="${field.field_id}"
                            data-id="${field.id}">
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="editable"
                            data-tooltip="${field.program.program_name}: ${field.field_name}">${field.field_name}</div>
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="integration-field-${field.field_id}"
                            data-gjs-removable="false"
                            data-gjs-copyable="false"
                            data-gjs-draggable="false"
                            data-gjs-badgable="false"
                            ${title} 
                            ${editable}>&nbsp;</div>
                    </div>`
                });
            });
        }
        //Amocrm 
        if (config.integration.system_crm_id === 2) {

            config.integration.fields.forEach((field) => {

                let editable = '',
                    title = 'data-tooltip="' + config.messages.openElementSettings + '"',
                    editableTypes = [
                        1, //Текст
                        2, //Число
                        7, //Ссылка
                        9, //Текстовая область
                        11, //Короткий адрес
                    ];

                if (editableTypes.indexOf(field.amocrm_field_type_id) !== -1) {
                    editable = 'data-gjs-editable="true" data-gjs-selectable="false"';
                    //Remove title
                    title = '';
                } else {
                    editable = '';
                    title = 'data-tooltip="' + config.messages.openElementSettings + '"';
                }

                blockManager.add('add-order-detail-' + field.amocrm_field_id, {
                    label: field.amocrm_field_name,
                    category: 'Детали заказа',
                    attributes: {
                        'title': '',
                        'class': 'fa fa-align-justify gjs-block'
                    },
                    content:
                        `<div class="row add-order" 
                            data-gjs-type="integration-field"
                            data-field-id="${field.amocrm_field_id}"
                            data-id="${field.id}">
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="editable"
                            data-tooltip="${field.amocrm_field_name}">${field.amocrm_field_name}</div>
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="integration-field-${field.amocrm_field_id}"
                            data-gjs-removable="false"
                            data-gjs-copyable="false"
                            data-gjs-draggable="false"
                            data-gjs-badgable="false"
                            ${title} 
                            ${editable}>&nbsp;</div>
                    </div>`
                });
            });
        }
        //Bitrix24
        if (config.integration.system_crm_id === 3) {

            config.integration.fields.forEach((field) => {

                let editable = '',
                    title = 'data-tooltip="' + config.messages.openElementSettings + '"',
                    editableTypes = [
                        'string',
                        'url',
                        'money', 
                        'double'
                    ];

                if (editableTypes.indexOf(field.bitrix24_field_type_id) !== -1) {
                    editable = 'data-gjs-editable="true" data-gjs-selectable="false"';
                    //Remove title
                    title = '';
                } else {
                    editable = '';
                    title = 'data-tooltip="' + config.messages.openElementSettings + '"';
                }

                blockManager.add('add-order-detail-' + field.bitrix24_field_id, {
                    label: field.bitrix24_field_name,
                    category: 'Детали заказа',
                    attributes: {
                        'title': '',
                        'class': 'fa fa-align-justify gjs-block'
                    },
                    content:
                        `<div class="row add-order" 
                            data-gjs-type="integration-field"
                            data-field-id="${field.bitrix24_field_id}"
                            data-id="${field.id}">
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="editable"
                            data-tooltip="${field.bitrix24_field_name}">${field.bitrix24_field_name}</div>
                        <div class="col-md-6 cp-details-about-cell" 
                            data-gjs-type="integration-field-${field.bitrix24_field_id}"
                            data-gjs-removable="false"
                            data-gjs-copyable="false"
                            data-gjs-draggable="false"
                            data-gjs-badgable="false"
                            ${title} 
                            ${editable}>&nbsp;</div>
                    </div>`
                });
            });
        }
    }

    /****************** Подписи ******************/

    if (config.employees) {
        for (let i in config.employees) {
            let employee = config.employees[i],
                signature = '';

            if (employee.signature) {
                employee.signature.split(/(?:\r\n|\r|\n)/g).forEach(line => {
                    if (line) {
                        if (F.validateEmail(line)) {
                            signature +=
                                `<a class="sign_hrefs corporate-color-hover" 
                                    data-gjs-type="disabled" href="mailto:${line}">${line}</a>`;
                        } else if (line.replace(/[^0-9]/g, '').length > 9) {
                            signature +=
                                `<a class="sign_hrefs corporate-color-hover" 
                                    data-gjs-type="disabled" href="tel:${line}">${line}</a>`;
                        } else {
                            signature += line;
                        }
                        signature += '<br>';
                    }
                });
            }

            //data-gjs-draggable="div.person.message__person" 
            blockManager.add('employee-' + employee.id, {
                label: '<img class="employee-image" src="' + employee.avatarUrl + '" width="40"><br>' + employee.surname, //data-gjs-droppable=".row-cell"
                category: 'Подпись',
                content:
                    `<div class="clearfix person-container" data-id="${employee.id}" 
                        data-gjs-type="employee-signature">
                        <img class="photo_medium obj_left" src="${employee.avatarUrl}" 
                            data-gjs-type="employee-avatar">
                        <div class="person__info_small obj_right" 
                            data-gjs-type="disabled">
                            <p class="person__info-text" 
                                data-gjs-type="disabled">
                                ${signature}
                            </p>
                        </div>
                    </div>`,
            });
        }
    }
}