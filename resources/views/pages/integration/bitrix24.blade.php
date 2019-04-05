@extends('layouts.integration')
@section('styles')
    <link rel="stylesheet" href="{{asset('widgets/bitrix24/bitrix24.css')}}">
@stop
@section('scripts')
    <script src="//api.bitrix24.com/api/v1/"></script>
    <script src="{{asset('plugins/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{asset('js/integration/bitrix24.min.js')}}"></script>
@stop
@section('content')
    <script>
        var data = {!! json_encode($data) !!};
    </script>
    <div id="kp10-widget-bitrix24">
        <template v-if="integration">
            <div class="modal fade in" id="modal-default"
                 style="display: block; padding-right: 15px; background-color: rgb(238, 242, 244); color: #545c6a;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Сообщение от КП10</h4>
                        </div>
                        <div class="modal-body">
                            <h4>@{{integrationStatusText}}</h4>
                            <p v-html="integrationStatusMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template v-if="widget">
            <div v-show="createOfferBlock">
                <div class="kp-offer-detail-modal">
                    <div class="kp-offer-detail-form">
                        <div class="kp-card-tabs kp-card-tabs-top">
                            <div class="kp-card-tabs-item kp-card-tabs-item-disabled">
                                <span class="kp-card-tabs-item-inner kp-logo">
                                <b>КП</b>10</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!creatingOfferBlock">
                    <div class="row m-top-15">
                        <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-sm-4 col-md-4">
                            <button type="button" @click="addSelect" v-show="!showCreateForm"
                                    class="btn btn-secondary full-w">
                                Создать КП
                            </button>
                        </div>
                    </div>
                    <div class="row m-top-15">
                        <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-sm-4 col-md-4"
                             v-if="showCreateForm">
                            <div class="form-group full-w">
                                <select class="select-template" @change="selectTemplate" v-show="selectTemplateShow"
                                        id="selectTemplate" data-live-search="true">
                                    <option value="-1">Выберите шаблон</option>
                                    <option :value="template.id" v-for="template in template" :key="template.id">@{{
                                        template.offer_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-sm-4 col-md-4"
                             v-if="showCreateForm && showKpBtnAndName">
                            <div class="form-group full-w">
                                <input type="text" class="form-control full-w" @click="kpNameBlockFocus"
                                       :class="{errors : isErrorName}"
                                       placeholder="Введите название КП" v-on:input="offerName = $event.target.value">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-sm-4 col-md-4"
                             v-if="showCreateForm && showKpBtnAndName">
                            <div class="form-group full-w">
                                <button type="button" class="btn btn-secondary full-w" :disabled="disabledBtn"
                                        @click="createOffer">Создать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="creatingOfferBlock">
                    <div class="row m-top-15">
                        <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-sm-4 col-md-4 creating-offer-block-information">
                            <div v-if="errorMessage">
                                <h4>Ошибка!</h4>
                                <h5>@{{errorMessage}}</h5>
                            </div>
                            <div v-if="!errorMessage">
                                <h4 style="margin-top: 32px; text-align: center;">Идет процесс формирование <b>КП</b>,
                                    пожалуйста, подождите...</h4>
                                <div id="bowlG" v-show="preloader">
                                    <div id="bowl_ringG">
                                        <div class="ball_holderG">
                                            <div class="ballG">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- модалка 1 -->
            <div class="modal fade in" id="kp10-popup">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title">Виджет КП10</h4>
                        </div>

                        <div class="modal-body">
                            <!-- <p>One fine body…</p> -->
                            <div>Для продолжения работы необходимо перейти в редактор КП</div>
                            <div>После завершения конфигурирования КП, в Вашем Bitrix24 нажмите на кнопку Обновить в
                                виджете
                            </div>
                            <a :href="'https://' + kp10Host + '/editor/' + dataOfferId + '?auth=remote&type=bitrix24&uid=' + uId + '&token=' + specToken"
                               target="_blank" @click="openEditor">Перейти в редактор</a>

                        </div>
                        <!-- <div class="modal-footer"> -->
                        <!-- <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button> -->
                        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                        <!-- </div> -->
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>

            <!-- модалка 2 -->
            <div class="modal fade in" id="kp10Description" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-row">
                                <div class="modal-cell">
                                    <img class="" :src="'https://' + kp10Host + productImg" onmousedown="return false">
                                </div>
                                <div class="modal-cell">
                                    <div class="description-header" v-if="productName"> @{{productName}}
                                    </div>
                                    <div class="description-price" v-if="productPrice">
                                        <span>Цена: @{{productPrice}}
                                        <svg-icon :icon="currency"></svg-icon></span>
                                    </div>
                                    <div class="description-info" v-if="productCount">
                                        <b>Количество:</b> @{{productCount}}
                                    </div>
                                    <div class="description-info" v-if="productDiscount">
                                        <b>Скидка:</b> @{{productDiscount}}%
                                    </div>
                                    <div class="description-info" v-if="productCost">
                                        <b>Итого:</b> @{{productCost}}
                                        <svg-icon :icon="currency"></svg-icon>
                                    </div>
                                    <div class="description-info" v-if="productGoodColl"
                                         v-for="(productGood, index) in productGoodColl" :key="index">
                                        <b>@{{productGood.name}}:</b> @{{productGood.value}}
                                    </div>
                                    <div class="description-info" v-if="productDesc"> @{{productDesc}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- далі тіло віджета(таблиця з товарами) -->
            <div class="kp-offer-detail-modal" v-show="showKP10Widget">
                <div class="kp-offer-detail-form">
                    <div class="kp-offer-detail-form-overlay kp-display-none">
                        <span></span>
                    </div>
                    <div class="kp-card-tabs kp-card-tabs-top">
                        <div class="kp-card-tabs-item kp-card-tabs-item-disabled">
                            <span class="kp-card-tabs-item-inner kp-logo">
                            <b>КП</b>10</span>
                        </div>
                        <div class="kp-card-tabs-item" v-for="(variant, index) in variants" :key="index"
                             :class="{'kp-selected kp-card-tabs-item-preload': selectedVariantIndex == index, 'inactive': !variant.active }"
                             @click="switchTab(index)" data-id="" data-type="">
                            <span class="kp-card-tabs-item-inner kp-card-cost"
                                  title="">@{{numberFormat(variant.price)}}</span>
                            <svg-icon :icon="currency"></svg-icon>
                            <span v-if="variant.selected" class="kp-variant-selected">Выбран</span>
                            <br>
                            <span class="kp-card-tabs-item-inner" title=""
                                  @keyup="changeItemName">@{{variant.name}}</span>
                        </div>
                        <div class="kp-card-tabs-item kp-card-tabs-item-disabled kp-actions">
                            <a href="#" class="kp-checkmark" data-kp-tooltip="Отменить выбор"
                               data-kp-tooltip-pos="bottom" v-show="varSelected"
                               @click.prevent="cancelVariantSelection">
                                <svg-icon :icon="'rotate-left'"></svg-icon>
                            </a>
                            <a href="#" class="kp-checkmark" data-kp-tooltip="Сохранить изменения в КП"
                               data-kp-tooltip-pos="bottom" v-show="showEditOfferBtn" @click.prevent="saveForm">
                                <svg-icon :icon="'checkmark'"></svg-icon>
                            </a>
                            <a href="#" class="kp-cancel" data-kp-tooltip="Отменить изменения"
                               data-kp-tooltip-pos="bottom" v-show="showEditOfferBtn" @click.prevent="cancelEdit">
                                <svg-icon :icon="'cancel'"></svg-icon>
                            </a>
                            <a :href="'https://'+ kp10Host +'/editor/' + offerId + '?auth=remote&type=bitrix24&uid=' + uId + '&token=' + specToken"
                               target="_blank" data-kp-tooltip="Перейти в редактор" data-kp-tooltip-pos="bottom">
                                <svg-icon :icon="'edit'"></svg-icon>
                            </a>
                            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/pdf'" target="_blank"
                               data-kp-tooltip="Скачать PDF" data-kp-tooltip-pos="bottom">
                                <svg-icon :icon="'file-pdf'"></svg-icon>
                            </a>
                            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/pdf/full'" target="_blank"
                               data-kp-tooltip="Скачать расширенный PDF" data-kp-tooltip-pos="bottom">
                                <svg-icon :icon="'file-pdf'"></svg-icon>
                            </a>
                            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/excel'" target="_blank"
                               data-kp-tooltip="Скачать Excel" data-kp-tooltip-pos="bottom">
                                <svg-icon :icon="'file-excel'"></svg-icon>
                            </a>
                            <a href="#" class="kp-copy-link" :data-kp-tooltip="kpTooltipCopy"
                               data-kp-tooltip-pos="bottom" @click.prevent="kpCopyLink">
                                <svg-icon :icon="'chain'"></svg-icon>
                            </a>
                            <a href="#" class="kp-arrow-down" data-kp-tooltip="Свернуть/развернуть КП10"
                               data-kp-tooltip-pos="bottom" @click="hideShowKP10">
                                <svg-icon :icon="arrowIcon"></svg-icon>
                            </a>
                        </div>
                    </div>
                    <div class="kp-card-entities-form" v-show="rotateArrow" :class="{'no-click': varSelected}">
                        <div class="kp-card-entity-form"
                             :data-id="variant.id"
                             v-for="(variant, vIndex) in variants"
                             :key="vIndex"
                             :class="{'kp-selected': (selectedVariantIndex == vIndex), 'no-click': !variant.active}"
                             v-show="selectedVariantIndex == vIndex">
                            <table class="kp-products">
                                <thead>
                                <tr>
                                    <th data-index="" data-type="" data-id="" v-for="(field, index) in variant.fields"
                                        :key="index" :colspan="index == 0?2:''">@{{field.name}}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :data-id="product.id" v-for="(product, pIndex) in variant.products" :key="pIndex"
                                    :class="{ 'kp-table-title': product.group == 1 }" :data-group="product.group">
                                    <td v-if="product.image">
                                        <img :src="'https://' + kp10Host + product.image"
                                             @click="showDescription(product)">
                                    </td>
                                    <td v-for="(value, index) in product.values" :key="index"
                                        :colspan="product.group?1 + variant.fields.length:''"
                                        :data-type="variant.fields[index].type || ''">
                                    <span :contenteditable="editContent(variant.fields[index].type || '')"
                                          :data-id="value.id"
                                          @blur="changeItemValue($event.target.innerText, value, variant.id)"
                                          class="single-line"> <!-- @keyup.enter="changeItemValue($event.target.innerText, value, variant.id)" -->
                                        @{{printValue(value)}}</span>
                                        <svg-icon :icon="currency"
                                                  v-if="(value.type == 'price') || (value.type == 'cost')"></svg-icon>
                                        <span v-show="product.group == 1" :contenteditable="false">
                                            {{--(@{{numberFormat(kpGroupCost[product.id])}}--}}
                                            (@{{parseF(kpGroupCost[product.id])}}
                                            <svg-icon :icon="currency"></svg-icon>)</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="kp-add-product" v-show="!varSelected">
                                <select class="search-product" id="searchProduct" data-live-search="true">
                                    <option value="-1">Поиск товаров</option>
                                </select>
                                <div class="form-group display-inline" v-show="showCreateProduct">
                                    <button type="button" class="btn btn-default" @click="createProduct">+ Создать
                                        товар
                                    </button>
                                    <!-- <button type="button" class="btn btn-primary" @click="createProduct">+ Создать товар</button> -->
                                </div>
                                <!-- <button type="button" class="create-product">+Создать продукт</button> -->
                            </div>
                            <table class="kp-card-entity-summary">
                                <tr>
                                    <td class="kp-special-discount">
                                        <div class="kp-row-special-discount" data-id=""
                                             v-for="(specialDiscount, index) in variant.special_discounts" :key="index"><span>@{{specialDiscount.name}}
                                            </span> (<span class="kp-special-discount-value">@{{numberFormat(specialDiscount.value)}}</span>)
                                        </div>
                                    </td>
                                    <td class="kp-discount">
                                        <div :class="{'kp-display-none': !kpDiscount[variant.id] }">
                                            <div class="kp-offer-title">Цена без скидки</div>
                                            <div class="kp-discount-value">
                                                <span>@{{kpDiscount[variant.id]}}<svg-icon :icon="currency"></svg-icon></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="kp-price">
                                        <div>
                                            <div class="kp-offer-title">Стоимость</div>
                                            <div class="kp-price-value">
                                                <!-- <span>@{{variant.price}}</span>  -->
                                                <span>@{{numberFormat(variant.price)}}
                                                    <svg-icon :icon="currency"></svg-icon></span>
                                            </div>
                                            <div class="kp-price-tax"></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- кінець тіла віджета(таблиця з товарами) -->
        </template>
    </div>
@stop