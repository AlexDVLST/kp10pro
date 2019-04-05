<div role="tabpanel" class="tab-pane tab-p1 active" id="economy" data-gjs-type="disabled">
    <div id="econom" class="section tab-pane-inner" 
        data-gjs-type="variant"
        data-gjs-droppable="div.add-goods-variant,div.row.tab-pane-inner__menu-row">
        <div class="tab-pane-inside" data-gjs-type="disabled">
            <div class="container-inner" data-gjs-type="disabled">
                <div class="row" data-gjs-type="disabled">
                    <div class="col-md-10 col-sm-9" data-gjs-type="disabled">
                        <h2 class="tab-pane-inner__title" data-gjs-type="variant-name">Вариант Стандартный</h2>
                    </div>
                    <div class="col-md-2 col-sm-3" data-gjs-type="disabled">
                        <ul class="nav-offer" data-gjs-type="disabled">
                            <li class="nav-offer__item active" data-gjs-type="disabled">
                                <a class="nav-offer__link nav-offer__link_table" 
                                    data-toggle="tab" role="tab" href="#economy-table" aria-expanded="true" 
                                    data-gjs-type="disabled">
                                    <i class="fa fa-th-list corporate-color" data-gjs-type="variant"></i>
                                </a>
                            </li>
                            <li class="nav-offer__item" data-gjs-type="disabled">
                                <a class="nav-offer__link nav-offer__link_list"
                                   data-toggle="tab" role="tab" href="#economy-list" aria-expanded="false" 
                                   data-gjs-type="disabled">
                                    <i class="fa fa-th corporate-color" data-gjs-type="variant"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-pane-top" data-gjs-type="disabled">
                    <div class="tab-pane-inner__description" data-gjs-type="disabled">
                        <div data-gjs-type="editable">
                            Введите краткое описание первого варианта
                        </div>
                        <img class="photo_small tab-pane-inner__description-photo" src="/storage/resource/no-avatar.png" data-gjs-type="employee-avatar">
                    </div>
                </div>
                <div class="tab-content" data-gjs-type="disabled">
                    {{--todo tab-pane-inner__menu usin for block add product--}}
                    <div class="tab-pane-inner__menu tab-content__table table-offer active" id="economy-table" 
                        data-gjs-type="disabled"
                        data-gjs-droppable="div.add-goods-variant,div.row.tab-pane-inner__menu-row,div.add-discount">
                        <div class="row tab-pane-inner__menu-row-heading"
                            data-gjs-type="disabled"
                            data-gjs-droppable="div.tab-pane-inner__menu-header-cell">
                            <div class="col-xs-7 col-sm-7 col-md-4 tab-pane-inner__menu-header-cell kp10-cell-name"
                                data-gjs-type="editable">
                                Наименование
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-header-cell kp10-cell-count"
                                data-gjs-type="editable">
                                Количество
                            </div>
                            <div class="col-xs-1 col-sm-1 col-md-1 tab-pane-inner__menu-header-cell kp10-cell-price"
                                data-gjs-type="editable">
                                Цена
                            </div>
                            <div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-header-cell kp10-cell-discount" 
                                data-gjs-type="discount">
                                Скидка
                            </div>
                            <div class="col-md-2 hidden-xs hidden-sm tab-pane-inner__menu-header-cell kp10-cell-price-with-discount" 
                                data-gjs-type="discount">
                                Цена со скидкой
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-header-cell kp10-cell-cost"
                                data-gjs-type="disabled">
                                Стоимость
                            </div>
                        </div>
                        @foreach($products as $product)
                            @if(isset($product['id']))
                            <div class="row tab-pane-inner__menu-row js-fancybox-offer corporate-color-hover vertical-align"
                                data-fancybox="standard-list"
                                data-src="#modal-product-{{$product['id']}}-economy"
                                data-gjs-type="goods-variant">
                                <div class="col-xs-7 col-sm-7 col-md-4 tab-pane-inner__menu-cell kp10-cell-name"
                                    data-gjs-type="disabled">
                                    <div class="row vertical-align" data-gjs-type="disabled">
                                        <div class="col-xs-3" data-gjs-type="disabled">
                                            <img class="standard-table-image kp10-js-fancybox-product"
                                                src="{{$product['img']}}"
                                                data-src="#modal-product-{{$product['id']}}-economy"
                                                data-gjs-type="product-image">
                                            </div>
                                            <div class="col-xs-9" data-gjs-type="disabled">
                                                <span data-gjs-type="editable" data-kp10-update-prices="true">{{$product['name']}}</span>
                                            </div>
                                    </div>
                                </div>
                                <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-cell kp10-cell-count"
                                    data-gjs-type="editable"
                                    data-kp10-update-prices="true">
                                    {{$product['count']}}
                                </div>
                                <div class="col-xs-1 col-sm-1 col-md-1 tab-pane-inner__menu-cell nowrap"
                                    data-gjs-type="disabled">
                                    <span class="kp10-cell-price"
                                        data-gjs-type="editable"
                                        data-kp10-update-prices="true">
                                        {{$product['price']}}
                                    </span>
                                    <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                </div>
                                <div class="col-md-1 hidden-xs hidden-sm tab-pane-inner__menu-cell kp10-discount"   
                                    data-gjs-type="discount" 
                                    data-kp10-update-prices="true"
                                    data-gjs-editable="true">
                                    {{$product['discount']}}
                                </div> 
                                <div class="col-md-2 hidden-xs hidden-sm tab-pane-inner__menu-cell"  
                                    data-gjs-type="discount">  
                                    {{$product['priceDiscount']}}
                                </div>
                                <div class="col-xs-2 col-sm-2 col-md-2 tab-pane-inner__menu-cell"
                                    data-gjs-type="disabled">
                                    <span class="kp10-cell-cost" data-gjs-type="disabled">
                                        {{$product['cost']}}
                                    </span>
                                    <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                </div>
                            </div>
                            @else
                            <div class="row tab-pane-inner__menu-row" data-gjs-type="add-goods-group"> 
                                <div class="col-md-12 tab-pane-inner__menu-cell pane-title" data-gjs-type="disabled">
                                    <span data-gjs-type="editable">{{$product['name']}}</span> 
                                    (<span class="kp10-group-cost" data-gjs-type="disabled">{{$product['price']}}</span> 
                                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>)
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="tab-content__list tab-content__list_type1 row"
                        id="economy-list"
                        data-gjs-type="disabled">
                        @foreach($products as $product)
                            @if(isset($product['id']))
                            <div class="col-xs-12 col-sm-4 col-md-3" data-gjs-type="disabled">
                                <a data-fancybox="standard-list" data-src="#modal-product-{{$product['id']}}-economy"
                                    href="javascript:;"
                                    class="js-fancybox-offer card-offer card-offer_type2"
                                    data-gjs-type="disabled">
                                    <div class="card-offer__inner" data-gjs-type="disabled">
                                        <div class="card-offer__preview"
                                            data-gjs-type="disabled">
                                            <img src="{{$product['img']}}"
                                                class="card-offer__preview-img kp10-js-fancybox-product"
                                                data-src="#modal-product-{{$product['id']}}-economy"
                                                data-gjs-type="product-image">
                                        </div>
                                        <div class="card-offer__content" data-gjs-type="disabled">
                                            <div class="card-offer__title kp10-cell-name" data-gjs-type="disabled">
                                                <span data-gjs-type="editable" data-kp10-update-prices="true">
                                                    {{$product['name']}}
                                                </span>
                                            </div>
                                            <div class="row" data-gjs-type="disabled">
                                                <div class="col-xs-6" data-gjs-type="disabled">
                                                    <div class="card-offer__price" data-gjs-type="disabled">
                                                        <span class="kp10-cell-price"
                                                            data-gjs-type="editable"
                                                            data-kp10-update-prices="true">
                                                            {{$product['price']}}
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
                                                            {{$product['count']}}
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
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="price-decoration row" data-type="econom"
                    data-name="Стандартный"
                    data-gjs-type="disabled">
                    <div class="col-xs-12 col-sm-3 col-md-3 kp10-special-discount"
                        data-gjs-type="disabled"
                        data-gjs-droppable="div.kp10-row-special-discount"></div>
                    <div class="col-xs-12 col-sm-3 col-md-3 kp10-discount" data-gjs-type="disabled">
                         <div class="kp10-pane-discount" data-gjs-type="discount">
                            <div class="tab-pane-inner__price-cell" data-gjs-type="disabled">Цена без скидки</div> 
                            <div class="tab-pane-inner__price-cell discount" data-gjs-type="disabled"> 
                                <span data-gjs-type="disabled">43 500</span> 
                                <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                            </div>
                        </div>    
                    </div>
                    <div class="col-xs-12 col-sm-3 col-md-3" data-gjs-type="disabled">
                        <div class="tab-pane-inner__price-row" data-gjs-type="disabled">
                            <div class="tab-pane-inner__price-cell" data-gjs-type="disabled">
                                Стоимость
                            </div>
                            <div class="tab-pane-inner__price-cell" data-gjs-type="disabled">
                                <div class="total_sum tab-pane-inner__price-cell_finally price-summ-1" data-gjs-type="disabled">
                                        <span data-gjs-type="disabled">35 000</span> 
                                        <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                                </div>
                                <div class="total-sum-tax" data-gjs-type="disabled"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 col-md-3" data-gjs-type="disabled">
                        <button class="button-choose upload-to-deal upload-preview-to-deal corporate-bg-color" data-gjs-type="variant">
                            Выбрать
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>