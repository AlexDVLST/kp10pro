{{--Block for commercial proposal settings(Using in Panel)--}}
{{-- <div id="app"   
    data-gjs-badgable="false"
    data-gjs-stylable="false"
    data-gjs-droppable="false"
    data-gjs-draggable="false"
    data-gjs-removable="false"
    data-gjs-copyable="false"
    data-gjs-selectable="false"
    data-gjs-highlightable="false"
    data-gjs-editable="false"> --}}
    <?php 
        $economy = [
            [
                'name' => 'ГРУППА ТОВАРОВ',
                'price' => '30 000'
            ],
            [
                'id' => '1',
                'name' => 'Товар 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 40,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '10 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '2',
                'name' => 'Товар 2',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 40,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '10 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '3',
                'name' => 'Товар 3',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 40,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '10 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '4',
                'name' => 'Товар 4',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 8500,
                'discount' => 100,
                'priceDiscount' => 0,
                'cost' => 0,
                'productInfo' => 'Описание товара'
            ],
            [
                'name' => 'ГРУППА УСЛУГ',
                'price' => '5 000'
            ],
            [
                'id' => '5',
                'name' => 'Услуга 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 5000,
                'discount' => 0,
                'priceDiscount' => 5000,
                'cost' => '5 000',
                'productInfo' => 'Описание услуги'
            ],
        ];
        $standard = [
            [
                'id' => '1',
                'name' => 'Товар 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 60,
                'price' => 250,
                'cost' => '15 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '2',
                'name' => 'Товар 2',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 60,
                'price' => 250,
                'cost' => '15 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '3',
                'name' => 'Товар 3',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 60,
                'price' => 250,
                'cost' => '15 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '4',
                'name' => 'Товар 4',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 6000,
                'cost' => '6 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '5',
                'name' => 'Услуга 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 5000,
                'cost' => '5 000',
                'productInfo' => 'Описание услуги'
            ],
        ];
        $premium = [
            [
                'id' => '1',
                'name' => 'Товар 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 80,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '20 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '2',
                'name' => 'Товар 2',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 80,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '20 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '3',
                'name' => 'Товар 3',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 80,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '20 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '4',
                'name' => 'Товар 4',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 80,
                'price' => 250,
                'discount' => 0,
                'priceDiscount' => 250,
                'cost' => '20 000',
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '5',
                'name' => 'Товар 5',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 12000,
                'discount' => 100,
                'priceDiscount' => 0,
                'cost' => 0,
                'productInfo' => 'Описание товара'
            ],
            [
                'id' => '6',
                'name' => 'Услуга 1',
                'img' => '/storage/resource/templates/base/product/product.png',
                'count' => 1,
                'price' => 5000,
                'discount' => 0,
                'priceDiscount' => 5000,
                'cost' => '5 000',
                'productInfo' => 'Описание услуги'
            ],
        ];
    ?>
    <div id="cp-settings" data-gjs-type="disabled"></div>

    <div class="fancybox-container fancybox-offer fancybox-show-nav fancybox-is-open display-none" role="dialog" tabindex="-1" data-gjs-type="disabled">
        <div class="fancybox-bg" data-gjs-type="disabled"></div>
        <div class="fancybox-inner" data-gjs-type="disabled">
            <div class="fancybox-navigation" data-gjs-type="disabled">
                <button style="display: none;" data-fancybox-prev="" title="Previous" class="fancybox-arrow fancybox-arrow--left"
                    data-gjs-type="disabled"></button>
                <button style="display: none;"
                    data-fancybox-next="" title="Next"
                    class="fancybox-arrow fancybox-arrow--right"
                    data-gjs-type="disabled"></button>
            </div>
            <div class="fancybox-stage" data-gjs-type="disabled">
                <div class="fancybox-slide fancybox-slide--inline fancybox-slide--current fancybox-slide--complete" data-gjs-type="disabled">
                </div>
            </div>
            <div class="fancybox-caption-wrap" data-gjs-type="disabled">
                <div class="fancybox-caption" data-gjs-type="disabled"></div>
            </div>
        </div>
    </div>

    <div style="max-width: 1300px;margin: 0 auto; min-height: 300px"
        data-gjs-type="disabled">
        <header class="header"
            data-gjs-type="disabled">
            <img class="cover type-cover"
                src="/storage/resource/templates/base/cover/header.png"
                data-gjs-stylable="false"
                data-gjs-resizable="false"
                data-gjs-badgable="false"
                data-gjs-droppable="false"
                data-gjs-draggable="false"
                data-gjs-removable="false"
                data-gjs-copyable="false">
            <div class="container-inner"
                data-gjs-type="disabled">
                <img class="logo type-logo"
                    src="/storage/resource/templates/base/logo/logo.png"
                    data-gjs-stylable="false"
                    data-gjs-resizable="false"
                    data-gjs-badgable="false"
                    data-gjs-droppable="false"
                    data-gjs-draggable="false"
                    data-gjs-removable="false"
                    data-gjs-copyable="false">
            </div>
        </header>
        <main class="main title-row text-row"
            data-gjs-type="disabled"    
            data-gjs-droppable=".carousel,.slider,h1,h2,div">
            <section class="message clearfix" data-gjs-type="disabled">
                <div class="message__tooltip" data-gjs-type="editable">
                    Добрый день! Благодарю, что Вы обратились в нашу компанию. <br>
                    Я подготовил несколько вариантов наших услуг.<br>
                    В любой момент отвечу на появившиеся вопросы
                </div>
                <div class="person message__person"
                    data-gjs-type="disabled"
                    data-gjs-droppable="div.person-container">
                    <div class="clearfix person-container" data-gjs-type="disabled">
                        <img class="photo_medium obj_left"
                            src="/storage/resource/no-avatar.png"
                            data-gjs-type="employee-avatar">
                        <div class="person__info_small obj_right" data-gjs-type="disabled">
                            <p class="person__info-text" data-gjs-type="disabled">
                                <span class="person__info-name" data-gjs-type="disabled">Имя Фамилия</span><br>
                                <span class="person__info-position" data-gjs-type="disabled">Должность</span><br>
                                <a class="sign_hrefs corporate-color-hover" href="" data-gjs-type="disabled"></a><br>
                                <a class="sign_hrefs corporate-color-hover" href="" data-gjs-type="disabled"></a>
                            </p>
                        </div>
                    </div>
                    <div class="person__info-contacts message__person-info-contacts" data-gjs-type="disabled">
                        <!-- do not delete -->
                        <!-- <a class="person__info-contact" href="#">watsapp</a>
                            <a class="person__info-contact" href="#">viber</a>
                            <a class="person__info-contact" href="#">telegram</a> -->
                    </div>
                </div>
            </section>
            <!-- <a id="download_pdf" href="">Скачать PDF</a> -->
            <section class="cp-details" data-gjs-type="disabled">
                <div class="container-inner title-row text-row"
                    data-gjs-type="disabled"
                    data-gjs-droppable="h1,h2,div">
                    <h1 class="cp-details__title"
                        data-gjs-type="editable"
                        data-gjs-removable="true"
                        data-gjs-selectable="true">Коммерческое предложение</h1>
                    <span class="cp-details__number"
                        data-gjs-type="disabled">№27624 от 12.12.18 (обновлено 15.12.18)</span>
                    <div class="container-fluid"
                        data-gjs-type="disabled">
                        <div class="row"
                            data-gjs-type="disabled">
                            <div class="col-sm-8 col-md-8 col-lg-8 cp-details__table-col first-col"
                                data-gjs-type="disabled">
                                <h2 class="cp-details__about-title"
                                    data-gjs-type="editable">
                                    Детали заказа
                                </h2>
                                <div class="cp-details-about"
                                    data-gjs-type="disabled"
                                    data-gjs-droppable="div.row.add-order">
                                    <div class="row add-order"
                                        data-gjs-type="disabled"
                                        data-gjs-draggable="div.cp-details-about"
                                        data-gjs-removable="true"
                                        data-gjs-selectable="true">
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Номер заказа
                                        </div>
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Введите текст
                                        </div>
                                    </div>
                                    <div class="row add-order"
                                        data-gjs-type="disabled"
                                        data-gjs-draggable="div.cp-details-about"
                                        data-gjs-removable="true"
                                        data-gjs-selectable="true">
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Адрес
                                        </div>
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Введите текст
                                        </div>
                                    </div>
                                    <div class="row add-order"
                                        data-gjs-type="disabled"
                                        data-gjs-draggable="div.cp-details-about"
                                        data-gjs-removable="true"
                                        data-gjs-selectable="true">
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Дата заказа
                                        </div>
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Введите текст
                                        </div>
                                    </div>
                                    <div class="row add-order"
                                        data-gjs-type="disabled"
                                        data-gjs-draggable="div.cp-details-about"
                                        data-gjs-removable="true"
                                        data-gjs-selectable="true">
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Особые пожелания
                                        </div>
                                        <div class="col-sm-6 cp-details-about-cell"
                                            data-gjs-type="editable">Введите текст
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 cp-details__buttons-col last-col"
                                data-gjs-type="disabled"
                                data-gjs-droppable="a.button-feature_excel">

                                <a href="#" class="button-feature_excel download-excel corporate-color"
                                    data-gjs-type="download-excel">
                                    <i class="fa fa-file-excel-o" data-gjs-type="disabled"></i>
                                        <span data-gjs-type="editable">
                                            Коммерческое предложение в формате xls
                                        </span>
                                </a>

                                <a href="/" class="button-feature_excel download-pdf corporate-color"
                                    data-gjs-type="download-pdf">
                                    <i class="fa fa-file-pdf-o" data-gjs-type="disabled"></i>
                                        <span data-gjs-type="editable">
                                            Коммерческое предложение в формате pdf
                                        </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- SLIDER POSITION 2 -->
            <!-- END OF THE SLIDER POSITION 2-->
            <section class="cp-options"
                data-gjs-type="disabled">
                <div class="container-inner title-row text-row"
                    data-gjs-type="disabled"
                    data-gjs-droppable="h1,h2,div">
                    <h2 class="cp-options__title"
                        data-gjs-type="editable"
                        data-gjs-removable="true"
                        data-gjs-selectable="true">Предлагаю несколько вариантов</h2>
                </div>
                <input type="hidden" value="1" id="dspl1" data-gjs-type="disabled">
                <input type="hidden" value="1" id="dspl2" data-gjs-type="disabled">
                <input type="hidden" value="1" id="dspl3" data-gjs-type="disabled">
                <!-- mark -->
                <ul class="nav nav-tabs cp-options__tabs" role="tablist"
                    data-gjs-type="disabled"
                    data-gjs-droppable="li">
                    <li role="presentation" class="active" data-gjs-type="add-variant">
                        <a id="a-economy" href="#economy" aria-controls="economy" role="tab" data-toggle="tab"
                            data-gjs-type="variant">
                            <span class="kp10-header-name" data-gjs-type="variant-name">Вариант Стандартный</span>
                            <br data-gjs-type="disabled">
                            <strong data-gjs-type="disabled">
                                    <span class="price-summ-header-1" data-gjs-type="disabled">
                                        <span class="kp10-header-price" data-gjs-type="disabled">35 000</span>
                                            <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                    </span>
                                </strong>
                            </a>
                    </li>
                    <li role="presentation" class="" data-gjs-type="add-variant">
                        <a id="a-standart" href="#standard" aria-controls="standard" role="tab" data-toggle="tab"
                            data-gjs-type="variant">
                            <span class="label_top label_top_recomended corporate-bg-color"
                                data-gjs-type="disabled">Рекомендуем</span>
                            <span class="kp10-header-name" data-gjs-type="variant-name">Вариант Оптимальный</span>
                            <br data-gjs-type="disabled">
                            <strong data-gjs-type="disabled">
                                <span class="price-summ-header-2" data-gjs-type="disabled">
                                    <span class="kp10-header-price" data-gjs-type="disabled">56 000</span> 
                                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                    </span>
                                </strong>
                            </a>
                    </li>
                    <li role="presentation" data-gjs-type="add-variant">
                        <a id="a-premium" href="#premium" aria-controls="premium" role="tab" data-toggle="tab"
                            data-gjs-type="variant">
                            <span class="kp10-header-name" data-gjs-type="variant-name">Вариант Премиум</span>
                                <br data-gjs-type="disabled">
                                <strong data-gjs-type="disabled">
                                    <span class="price-summ-header-3" data-gjs-type="disabled">
                                        <span class="kp10-header-price" data-gjs-type="disabled">85 000</span> 
                                            <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                        </span>
                                </strong>
                            </a>
                    </li>
                </ul>

                <div class="tab-content cp-options__content" data-gjs-type="disabled">
                    <div class="tab-content" data-gjs-type="disabled">

                        @include('templates.base.tab-content-economy', ['products' => $economy])

                        @include('templates.base.tab-content-standard', ['products' => $standard])

                        @include('templates.base.tab-content-premium', ['products' => $premium])

                    </div>
                </div>
            </section>
            {{-- Gallery --}}
            @include('templates.base.carousel')

            <h2 class="cp-options__title custom-text-indent" data-gjs-draggable=".title-row" data-gjs-badgable="false">Наши преимущества</h2>

            <section class="cp-advantages" data-gjs-type="disabled">
                <div class="container-inner" data-gjs-type="disabled">
                    <div class="alternative-container-inner" data-gjs-type="disabled">
                        <div class="row advantages-row"
                            data-gjs-type="disabled"
                            data-gjs-droppable=".advantage-block">
                            {{-- Advantages --}}
                            @include('templates.base.advantages')
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <footer class="footer" data-gjs-type="disabled">
        <div class="footer__message" data-gjs-type="disabled">
            <div class="footer__message-inner title-row text-row" 
                data-gjs-type="disabled"
                data-gjs-droppable="h1,h2,div">
                <div>
                    Буду благодарен обратной связи в удобном для Вас формате
                </div>
            </div>
        </div>
        <div class="person footer__person" data-gjs-type="disabled">
            <img class="photo_big" src="/storage/resource/no-avatar.png" alt="" data-gjs-type="employee-avatar">
            <div class="person__info_extended" data-gjs-type="disabled">
                <p class="person__info-text-bottom" data-gjs-type="disabled">
                    <span class="person__info-name" data-gjs-type="disabled">Имя Фамилия</span><br>
                    <span class="person__info-position" data-gjs-type="disabled">Должность</span><br>
                    <a class="sign_hrefs" href="" data-gjs-type="disabled"></a><br>
                    <a class="sign_hrefs" href="" data-gjs-type="disabled"></a>
                </p>
            </div>
            <div class="person__info-contacts" data-gjs-type="disabled">
            </div>
        </div>
    </footer>
    <div class="responsive responsive-desktop" data-gjs-type="disabled"></div>
    <div class="responsive responsive-tablet-portret" data-gjs-type="disabled"></div>

    {{-- Produts modal info --}}
    @include('templates.base.modal-economy', ['products' => $economy])
    @include('templates.base.modal-standard', ['products' => $standard])
    @include('templates.base.modal-premium', ['products' => $premium])

    {{-- Empty product block for new one --}}
    <div id="modal-product-empty" class="display-none"
        data-src=""
        data-gjs-type="disabled">
        <div class="modal-product" data-gjs-type="disabled">
            <div class="modal-product__inner" data-gjs-type="disabled">
                <div class="modal-product__close" data-gjs-type="disabled"></div>
                <div class="row" data-gjs-type="disabled">
                    <div class="col-md-6" data-gjs-type="disabled">
                        <div class="modal-product__preview" data-gjs-type="disabled">
                            <img src=""
                                class="modal-product__preview-img"
                                data-gjs-badgable="false"
                                data-gjs-droppable="false"
                                data-gjs-draggable="false"
                                data-gjs-removable="false"
                                data-gjs-copyable="false"
                                data-gjs-resizable="false">
                        </div>
                    </div>
                    <div class="col-md-6" data-gjs-type="disabled">
                        <div class="modal-product__content" data-gjs-type="disabled">
                            <div class="modal-product__title kp10-cell-name" data-gjs-type="disabled">
                                <span data-gjs-type="editable" data-kp10-update-prices="true"></span>
                            </div>
                            <div class="modal-product__price" data-gjs-type="disabled">
                                <span data-gjs-type="disabled">Цена: </span>
                                <span class="kp10-cell-price"
                                    data-gjs-type="editable"
                                    data-gjs-editable="true"
                                    data-kp10-update-prices="true">0</span> 
                                <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                            </div>
                            <div class="kp10-goods-coll" data-gjs-type="disabled"></div>
                            <div class="modal-product__count" data-gjs-type="disabled">
                                <b data-gjs-type="disabled">Количество: </b>
                                <span class="kp10-cell-count"
                                    data-gjs-type="editable"
                                    data-kp10-update-prices="true">0</span>
                            </div>
                            <div class="kp10-discount-container" data-gjs-type="disabled"></div>
                            <div class="modal-product__count" data-gjs-type="disabled">
                                <b data-gjs-type="disabled">Итого: </b>
                                <span class="kp10-cell-cost" data-gjs-type="disabled">0</span> 
                                <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                            </div>
                            <div class="modal-product__info" data-gjs-type="disabled">
                                <div data-gjs-type="editable"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button data-fancybox-close="" class="fancybox-close-small" title="Close" data-gjs-type="disabled"></button>
    </div>
    {{-- Empty product block for list view --}}
    <div id="empty-product-list" class="display-none">
        <div class="col-xs-12 col-sm-4 col-md-3" data-gjs-type="disabled">
            <a data-fancybox="standard-list" data-src=""
                href="javascript:;"
                class="js-fancybox-offer card-offer card-offer_type2"
                data-gjs-type="disabled">
                <div class="card-offer__inner" data-gjs-type="disabled">
                    <div class="card-offer__preview" data-gjs-type="disabled">
                        <img src=""
                            class="card-offer__preview-img kp10-js-fancybox-product"
                            data-src=""
                            data-gjs-type="product-image">
                    </div>
                    <div class="card-offer__content" data-gjs-type="disabled">
                        <div class="card-offer__title kp10-cell-name" data-gjs-type="disabled">
                            <span data-gjs-type="editable" data-kp10-update-prices="true">A</span>
                        </div>
                        <div class="row" data-gjs-type="disabled">
                            <div class="col-xs-6" data-gjs-type="disabled">
                                <div class="card-offer__price" data-gjs-type="disabled">
                                    <span class="kp10-cell-price"
                                        data-gjs-type="editable"
                                        data-kp10-update-prices="true">
                                        0
                                    </span>
                                    <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                </div>
                            </div>
                            <div class="col-xs-6" data-gjs-type="disabled">
                                <div class="card-offer__count" data-gjs-type="disabled">
                                    <span class="kp10-cell-count"
                                        data-gjs-type="editable"
                                        data-kp10-update-prices="true"
                                        data-kp10-update-relative-model="true">
                                        0
                                    </span>
                                    шт
                                </div>
                            </div>
                        </div>
                        <div class="row" data-gjs-type="disabled">
                            <div class="col-xs-6" data-gjs-type="disabled"></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
{{-- </div> --}}