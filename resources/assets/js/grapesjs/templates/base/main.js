$(document).ready(function () {

    // fix for editor after update slider
    $(document).on('slider:add', '#wrapper', function (e) {
        $('.cbp-fwslider').each(function () {
            //remove
            if ($(this).data('cbpFWSlider')) {
                $(this).cbpFWSlider('destroy');
            }
            //init new slider
            $(this).cbpFWSlider();
            $(this).find('.cbp-fwprev').click();
        });
    });

    //fix for editor
    if (location.href.indexOf('about:blank') !== -1) {
        return;
    }

    // fancybox
    $(".js-fancybox-offer").fancybox({
        baseClass: 'fancybox-offer'
    });

    //Get offer settings
    $.get(location.href.replace(location.hash, '') + '/json', function (response) {
        let $wrapper = $('#wrapper');
        //show template
        $wrapper.fadeIn();

        //slider init
        //remove fake nav, using in editor
        $('.cbp-fwslider').find('nav').remove()
        //init
        $('.cbp-fwslider').cbpFWSlider();

        let number = response.number,
            create = response.created_at_formatted,
            update = response.updated_at_formatted.split(' '),
            offerUpdate = new Date(response.updated_at);

        //Update template number
        $wrapper.find('.cp-details__number').text('№' + number + ' от ' + create + ' (обновлено ' + update[1] + ' в ' + update[0] + ')');

        let variants = response.variants,
            index = -1;

        //Update products attr id after save in db
        updateVariantsProducts({ variants: variants });

        //Check selected variants
        variants.forEach((variant, i) => {
            if (variant.selected === 1) {
                index = i;
            }
            //Work only with active variants
            if (!variant.active) {
                return;
            }

            //If variant fields empty
            if (variant.fields) {
                //Check variant field
                variant.fields.forEach((field) => {
                    let $el = $wrapper.find('[data-db-id="' + field.id + '"]');
                    if ($el.length) {
                        //Update field names
                        $el.text(field.name);
                    }
                });
            }

            if (variant.products) {
                let totalCost = 0,
                    totalCostWithoutDiscount = 0,
                    discountEnabled = false,
                    groupCosts = [],
                    groupId = 0;

                //Sort
                variant.products.sort(function (a, b) {
                    return a.index - b.index;
                });

                //Check variant product update date
                variant.products.forEach((product) => {

                    let count = 0,
                        discount = 0,
                        price = 0,
                        goodsValue = 1,
                        cost = 0;

                    let $navTab = $wrapper.find('.nav-tabs a[data-db-id="' + variant.id + '"]'),
                        $activeTab = $navTab.length && $wrapper.find('.tab-pane[role="tabpanel"]' + $navTab.attr('href'));

                    //For group
                    if (product.group) {
                        groupId = product.id;
                    }
                    //Fancybox
                    let $hiddenProduct = $wrapper.children(product.fake_product_id);

                    //Fancybox
                    $hiddenProduct && $hiddenProduct.find('.modal-product__info > div').html(product.description);

                    if (product.values) {
                        //Sort values
                        product.values && product.values.sort(function (a, b) {
                            return a.index - b.index;
                        });

                        //For new products
                        let $newProductList = '',
                            $newProductTable = '',
                            $newProductHidden = '';

                        //If product not found
                        if ($activeTab && !product.group && !$hiddenProduct.length) {

                            //Clone last product for template
                            $newProductTable = $activeTab.find('.row.tab-pane-inner__menu-row').last().clone();
                            $newProductList = $activeTab.find('.tab-content__list').children().last().clone();
                            $newProductHidden = $wrapper.children($newProductTable.attr('data-src')).clone();

                            //Update fake product id
                            $newProductTable.attr('data-src', product.fake_product_id);
                            $newProductList.children('a').attr('data-src', product.fake_product_id);
                            $newProductHidden.attr('data-src', product.fake_product_id).attr('id', product.fake_product_id.substr(1));
                            //Update image
                            $newProductTable.find('.kp10-cell-name img').attr('src', product.image);
                            $newProductList.find('img.card-offer__preview-img').attr('src', product.image);
                            $newProductHidden.find('.modal-product__preview-img').attr('src', product.image);
                            //Remove attr
                            $newProductTable.attr('data-db-id', '').find('[data-db-id]').attr('data-db-id', '');
                        }

                        //Store index for good coll
                        let goodCollIndex = 1;

                        product.values.forEach((value) => {
                            let $el = $wrapper.find('[class*="col-"][data-db-id="' + value.id + '"]');
                            if (!product.group) {

                                let $list = $el.closest('[role="tabpanel"]').find('.tab-content__list a[data-src="' + product.fake_product_id + '"]');
                                //
                                switch (value.type) {
                                    case 'name':
                                        if ($el.length) {
                                            //Table  
                                            $el.find('.row div:nth-child(2) span').text(value.value);
                                            //List
                                            $list.find('.kp10-cell-name > span').text(value.value);
                                            //Fancybox
                                            $hiddenProduct.find('.kp10-cell-name > span').text(value.value);
                                        }
                                        //New product
                                        $newProductTable && $newProductTable.find('.kp10-cell-name > .row div:nth-child(2) span').text(value.value);
                                        $newProductList && $newProductList.find('.kp10-cell-name > span').text(value.value);
                                        $newProductHidden && $newProductHidden.find('.kp10-cell-name > span').text(value.value);
                                        break;
                                    case 'price':
                                        price = parseF(value.value);
                                        if ($el.length) {
                                            //Table
                                            $el.find('.kp10-cell-price').text(price);
                                            //List
                                            $list.find('.kp10-cell-price').text(price);
                                            //Fancybox
                                            $hiddenProduct.find('.kp10-cell-price').text(value.value);
                                        }

                                        //New product
                                        $newProductTable && $newProductTable.find('.kp10-cell-price').text(price);
                                        $newProductList && $newProductList.find('.kp10-cell-price').text(price);
                                        $newProductHidden && $newProductHidden.find('.kp10-cell-price').text(price);
                                        break;
                                    case 'count':
                                        count = parseF(value.value);
                                        if ($el.length) {
                                            //Table
                                            $el.text(count);
                                            //List
                                            $list.find('.kp10-cell-count').text(count);
                                            //Fancybox
                                            $hiddenProduct.find('.kp10-cell-count').text(count);
                                        }
                                        //New product list
                                        $newProductTable && $newProductTable.find('.kp10-cell-count').text(count);
                                        $newProductList && $newProductList.find('.kp10-cell-count').text(count);
                                        $newProductHidden && $newProductHidden.find('.kp10-cell-count').text(price);
                                        break;
                                    case 'discount':
                                        discount = parseF(value.value);
                                        if ($el.length) {
                                            discountEnabled = discountEnabledGlobal = true;
                                            //Table 
                                            $el.text(value.value);
                                            //Fancybox
                                            $hiddenProduct.find('.kp10-discount').text(value.value);
                                        }
                                        //New product list
                                        $newProductTable && $newProductTable.find('.kp10-discount').text(discount);
                                        $newProductHidden && $newProductHidden.find('.kp10-discount').text(discount);
                                        break;
                                    case 'price-with-discount':
                                        //New product table
                                        $newProductTable && $newProductTable.find('[data-gjs-type="discount"]').last().text(value.value);
                                        break;
                                    case 'good-coll':
                                        if ($el.length) {
                                            //Table    
                                            $el.text(parseF(value.value));
                                            //Fancybox
                                            let $goodColl = $hiddenProduct.find('.modal-product__content > div.kp10-goods-coll > div:nth-child(' + goodCollIndex + ')');

                                            $goodColl.find('span').text(parseF(value.value));

                                            if (value.value_in_price) {
                                                goodsValue *= parseF(value.value);
                                            }
                                        }
                                        //New product list
                                        $newProductTable && $newProductTable.find('.kp10-good-coll').eq(goodCollIndex - 1).text(parseF(value.value));
                                        $newProductHidden && $newProductHidden.find('.modal-product__content > div.kp10-goods-coll > div:nth-child(' + goodCollIndex + ') span').text(parseF(value.value));

                                        //Incement index
                                        goodCollIndex += 1;

                                        break;
                                    case 'cost':
                                        cost = parseF(value.value);
                                        if ($el.length) {
                                            //Table
                                            $el.find('.kp10-cell-cost').text(numberFormat(cost));
                                            //Fancybox
                                            $hiddenProduct.find('.kp10-cell-cost').text(numberFormat(cost));
                                        }
                                        //New product
                                        $newProductTable && $newProductTable.find('span.kp10-cell-cost').text(numberFormat(cost));
                                        $newProductHidden && $newProductHidden.find('.kp10-cell-cost').text(numberFormat(cost));
                                        break;
                                }
                            } else { //Group
                                $el && $el.children().eq(0).text(value.value);
                            }

                        });

                        //If product not found
                        $newProductTable && $activeTab.find('.tab-content__table.table-offer').append($newProductTable);
                        $newProductList && $activeTab.find('.tab-content__list').append($newProductList);
                        $newProductHidden && $wrapper.append($newProductHidden);

                    }

                    //calculate total cost
                    totalCost += cost;
                    //calculate total cost without discount
                    if (discountEnabled) {
                        totalCostWithoutDiscount += price * count * goodsValue;
                    }

                    if (groupId) {
                        let group = groupCosts.filter(a => a.groupId == groupId);

                        if (group.length) {
                            group[0].cost += cost;
                        } else { //for new one
                            //Using for groups
                            groupCosts.push({
                                groupId: groupId,
                                cost: cost
                            });
                        }
                    }

                });

                if (variant.special_discounts) {
                    //Add discount to final price
                    variant.special_discounts.forEach(function (discount) {
                        totalCost -= parseF(discount.value);
                        //
                    });
                }

                let $variant = $wrapper.find('.tab-content .tab-pane[role="tabpanel"]').eq(i);

                if ($variant.length) {
                    //update cost in tab
                    $wrapper.find('[role="tab"][data-db-id="' + variant.id + '"] .kp10-header-price').text(numberFormat(totalCost));
                    //update total cost
                    $variant.find('.price-decoration .tab-pane-inner__price-cell_finally span').text(numberFormat(totalCost));

                    //update total cost without discount
                    if (discountEnabled) {
                        $variant.find('.kp10-pane-discount > div:last-child span').text(numberFormat(totalCostWithoutDiscount));
                    }

                    //Tax
                    if (variant.tax) {
                        let $tax = $variant.find('.total-sum-tax .tax-value');

                        //If tax exists
                        if ($tax.length) {
                            let tax = numberFormat(Math.round(totalCost / 1.18 * 0.18));
                            $tax.text(tax);
                        }
                    }
                }
                //Update group cost
                if (groupCosts) {
                    groupCosts.forEach(function (el) {
                        $wrapper.find('.tab-pane-inner__menu-row[data-db-id="' + el.groupId + '"] .kp10-group-cost').text(numberFormat(el.cost));
                    });
                }

            }
        });

        //Integration fields amoCRM
        if (response.amocrm_deal && response.amocrm_deal.data && response.amocrm_deal.data.fields) {
            response.amocrm_deal.data.fields.forEach((field) => {
                //Find field on the page
                let $field = $wrapper.find('.add-order[data-field-id="' + field.amocrm_field_id + '"]');
                //Find field on the page
                if ($field.length) {
                    let value = field.values && field.values[0] && field.values[0].amocrm_field_value;

                    if (value) {
                        //Флаг
                        if (field.custom_field.amocrm_field_type_id == 3) {
                            value = value == 1 ? 'Да' : 'Нет';
                        }

                        //Дата | День рождения
                        if ([6, 14].indexOf(field.custom_field.amocrm_field_type_id) !== -1) {
                            value = formatIntegrationFieldDate(field.values[0]);
                        }

                        //Update field content
                        $field.children().eq(1).text(value);
                    }
                }

            });
        }
        //Integration fields Megaplan
        if (response.megaplan_deal && response.megaplan_deal.values) {
            response.megaplan_deal.values.forEach((field) => {
                //Find field on the page
                let $field = $wrapper.find('.add-order[data-field-id="' + field.field.field_id + '"]');
                //Find field on the page
                if ($field.length) {
                    let value = field.megaplan_field_values;

                    if (value) {
                        //Bool
                        if (field.field.content_type == 'BoolField') {
                            value = value == 1 ? 'Да' : 'Нет';
                        }
                        //Дата
                        if (field.field.content_type == 'DateField' || field.field.content_type == 'DateTimeField') {
                            value = formatIntegrationFieldDate(field);
                        }

                        //Update field content
                        $field.children().eq(1).text(value);
                    }
                }

            });
        }

        //If has selected 
        if (index !== -1) {
            updateVariants(index);
            return;
        }

        //Fix for new product
        $(".js-fancybox-offer").fancybox({
            baseClass: 'fancybox-offer'
        });

        //Select variant
        $('button.button-choose').click(function () {
            let $tab = $('ul.nav-tabs li.active:not(.disabled-variant-selected)'),
                index = $tab.index();

            if (index !== -1) {
                //Set selected variant
                $.post(location.href + '/variant', { index: index }, function (response) {
                    updateVariants(index);

                    message('Вариант успешно выбран');

                }).fail(function (response) {
                    message(response.responseJSON.errors);
                });

            } else {
                message('Ошибка выбора Варианта. Обновите страницу и повторите попытку');
            }
        });


    }).fail(function (error) {
        if (error.responseJSON && error.responseJSON.errors) {
            message(error.responseJSON.errors);
        } else {
            message("Произошла ошибка при получении информации о КП. Обновите страницу и повторите попытку");
        }
    });

    //Download excel
    $('a.download-excel').click(function (e) {
        e.preventDefault();
        message('Формирование Excel...');
        location.href = window.location.href + '/excel';
    });
    //Download pdf
    $('a.download-pdf').click(function (e) {
        e.preventDefault();
        message('Формирование PDF...');
        location.href = window.location.href + '/pdf';
    });
    //Download pdf full
    $('a.download-pdf-full').click(function (e) {
        e.preventDefault();
        message('Формирование PDF...');
        location.href = window.location.href + '/pdf/full';
    });

    function updateVariants(index) {
        //Disable buttons
        $('button.button-choose').addClass('disabled');
        //Set label, remove recommended
        $('ul.nav-tabs li .label_top_recomended').remove();

        $('ul.nav-tabs li').each(function (i) {
            if (i !== index) {
                $(this).addClass('inactive');
            } else {
                //for selected
                //INFO: create duplicate if template was saved from editor
                $(this).children('a').prepend('<span class="label_top corporate-bg-color">Выбран</span>');
                $(this).children('a').trigger('click');
            }
        });
    }

    function message(text) {
        let $body = $('body');
        $body.find('.modal[role="dialog"]').remove();
        $body.append(
            '<div class="modal fade" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-body">' +
            '<h4 class="text-center">' + text + '</h4>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

        $('.modal').modal('show');
    }

    function calculatePositionsPrices() {

        let $activeTab = $('.tab-content .tab-pane.active');



    }

    function parseF(str) {
        if (str) {
            let parse = str.replace(/[^0-9.]/g, '');
            if (parse) {
                return parseFloat(parse);
            }
        }
        return 0;
    };

    function numberFormat(str) {
        str = str + '';//convert to string
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
            return str_temp;
        } else {
            return str;
        }
    }
    //Format integration date
    function formatIntegrationFieldDate(field) {
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
    }

    function updateVariantsProducts(params) {
        let updateId = params && params.updateId,
            variants = params && params.variants;

        if (variants) {

            let $wrapper = $('#wrapper');

            variants.forEach((variant, index) => {

                //nav-tabs
                let $tab = $wrapper.find('.nav-tabs li:nth-child(' + (index + 1) + ') a');
                if ($tab.length) {
                    let $tabContent = $wrapper.find('.tab-content .tab-pane' + $tab.attr('href'));

                    $tab.attr('data-db-id', variant.id);

                    if (variant.fields) {
                        variant.fields.forEach((field, fIndex) => {
                            //Find field
                            let $field = $tabContent.find('.row.tab-pane-inner__menu-row-heading > div:nth-child(' + (field.index + 1) + ')');
                            if ($field.length) {
                                //Exclude field with id
                                if (!$field.attr('data-db-id') || updateId) {
                                    $field.attr('data-db-id', field.id)
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
                                if (!$product.attr('data-db-id') || updateId) {
                                    $product.attr('data-db-id', product.id);
                                }

                                //Update db-id for field values
                                if (product.values) {

                                    product.values.forEach((value, vIndex) => {
                                        let $value = $product.children().eq(value.index);

                                        if (!$value.attr('data-db-id') || updateId) {
                                            $value.attr('data-db-id', value.id);
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
                                if (!$sDiscount.attr('data-db-id') || updateId) {
                                    $sDiscount.attr('data-db-id', sDiscount.id);
                                }
                            }
                        });
                    }
                }
            });
        }
    }
});
