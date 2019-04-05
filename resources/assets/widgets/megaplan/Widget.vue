<template>
  <div class="">
    <div class="box-body create-offer-block" v-show="createOfferBlock">
      <div class="row">
        <div class="col-xs-1" :class="{'no-width': showCreateForm}">
          <button type="button" @click="addSelect" v-show="!showCreateForm" class="btn btn-secondary">Создать КП</button>
        </div>
        <div class="col-xs-3" v-if="showCreateForm">
          <div class="form-group">
            <select class="select-template" @change="selectTemplate" v-show="selectTemplateShow" id="selectTemplate" data-live-search="true">
              <option value="-1">Выберите шаблон</option>
              <option :value="template.id" v-for="template in template" :key="template.id">{{ template.offer_name }}</option>
            </select>
          </div>
        </div>
        <div class="col-xs-2" v-if="showCreateForm">
          <div class="form-group">
            <input type="text" class="form-control" placeholder="Введите название КП" @input="inputEvent" v-on:input="offerName = $event.target.value">
          </div>
        </div>
        <div class="col-xs-2" v-if="showCreateForm">
          <div class="row">
            <div class="col-md-8">
              <button type="button" class="btn btn-primary" 
                :class="{'no-click': noClick}" 
                :disabled="disabledBtn" 
                @click="createOffer">  <!-- mouseOver noClick disabledBtn -->
                Создать
              </button>
            </div>
            <div class="col-md-2">
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
        <!-- <div class="col-xs-1">
          <div class="form-group"> -->
            <!-- <button type="button" class="btn btn-secondary" @click="loadOffer">Обновить</button>
            + -->
            <!-- <button type="button" class="btn btn-secondary" v-if="loadOfferShow" @click="loadOffer">Обновить</button>
          </div>
        </div> -->
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
            <div>После завершения конфигурирования КП, в Вашем Megaplan нажмите на кнопку Обновить в виджете</div>
            <a :href="'https://' + kp10Host + '/editor/' + dataOfferId + '?auth=remote&type=megaplan&token=' + mpApiToken" target="_blank" @click="openEditor">Перейти в редактор</a>

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
                <div class="description-header" v-if="productName"> {{productName}}
                </div>
                <div class="description-price" v-if="productPrice">
                  <span>Цена: {{productPrice}}<svg-icon :icon="currency"></svg-icon></span>
                </div>
                <div class="description-info" v-if="productCount">
                  <b>Количество:</b> {{productCount}}
                </div>
                <div class="description-info" v-if="productDiscount">
                  <b>Скидка:</b> {{productDiscount}}%
                </div>
                <div class="description-info" v-if="productCost">
                  <b>Итого:</b> {{productCost}}<svg-icon :icon="currency"></svg-icon>
                </div>
                <div class="description-info" v-if="productGoodColl" v-for="(productGood, index) in productGoodColl" :key="index">
                  <b>{{productGood.name}}:</b> {{productGood.value}}
                </div>
                <div class="description-info" v-if="productDesc"> {{productDesc}}</div>
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
          <div class="kp-card-tabs-item"
              v-for="(variant, index) in variants"
              :key="index"
              :class="{'kp-selected kp-card-tabs-item-preload': selectedVariantIndex == index, 'inactive': !variant.active }"
              @click="switchTab(index)" data-id="" data-type="">
            <span class="kp-card-tabs-item-inner kp-card-cost" title="">{{numberFormat(variant.price)}}</span>
            <svg-icon :icon="currency"></svg-icon>
            <span v-if="variant.selected" class="kp-variant-selected">Выбран</span>
            <br>
            <span class="kp-card-tabs-item-inner" title="" @keyup="changeItemName">{{variant.name}}</span>
          </div>
          <div class="kp-card-tabs-item kp-card-tabs-item-disabled kp-actions">
            <a href="#" class="kp-checkmark" data-kp-tooltip="Отменить выбор" data-kp-tooltip-pos="bottom" v-show="varSelected" @click.prevent="cancelVariantSelection">
              <svg-icon :icon="'rotate-left'"></svg-icon>
            </a>
            <a href="#" class="kp-checkmark" data-kp-tooltip="Сохранить изменения в КП" data-kp-tooltip-pos="bottom" v-show="showEditOfferBtn" @click.prevent="saveForm">
              <svg-icon :icon="'checkmark'"></svg-icon>
            </a>
            <a href="#" class="kp-cancel" data-kp-tooltip="Отменить изменения" data-kp-tooltip-pos="bottom" v-show="showEditOfferBtn" @click.prevent="cancelEdit">
              <svg-icon :icon="'cancel'"></svg-icon>
            </a>
            <a :href="'https://'+ kp10Host +'/editor/' + offerId + '?auth=remote&type=megaplan&token=' + mpApiToken + '&uid=' + uid" target="_blank" data-kp-tooltip="Перейти в редактор" data-kp-tooltip-pos="bottom"> 
              <svg-icon :icon="'edit'"></svg-icon>
            </a>
            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/pdf'" target="_blank" data-kp-tooltip="Скачать PDF" data-kp-tooltip-pos="bottom">
              <svg-icon :icon="'file-pdf'"></svg-icon>
            </a>
            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/pdf/full'" target="_blank" data-kp-tooltip="Скачать расширенный PDF" data-kp-tooltip-pos="bottom">
              <svg-icon :icon="'file-pdf'"></svg-icon>
            </a>
            <a :href="'https://'+ kp10Host +'/'+offerUrl+'/excel'" target="_blank" data-kp-tooltip="Скачать Excel" data-kp-tooltip-pos="bottom">
              <svg-icon :icon="'file-excel'"></svg-icon>
            </a>
            <a href="#" class="kp-copy-link" :data-kp-tooltip="kpTooltipCopy" data-kp-tooltip-pos="bottom" @click.prevent="kpCopyLink">
              <svg-icon :icon="'chain'"></svg-icon>
            </a>
            <a href="#" class="kp-arrow-down" data-kp-tooltip="Свернуть/развернуть КП10" data-kp-tooltip-pos="bottom" @click="hideShowKP10">
              <svg-icon :icon="arrowIcon"></svg-icon>
            </a>
          </div>
        </div>
        <div class="kp-card-entities-form" v-show="rotateArrow" :class="{'no-click': varSelected}">
          <div class="kp-card-entity-form" 
            :data-id="variant.id" 
            v-for="(variant, vIndex) in variants" 
            :key="vIndex" 
            :class="{'kp-selected': (selectedVariantIndex == vIndex), 'no-click': !variant.active}" v-show="selectedVariantIndex == vIndex">
            <!-- v-show="selectedVariantIndex == index" -->
            <table class="kp-products">
              <thead>
                <tr>
                  <th data-index="" data-type="" data-id="" v-for="(field, index) in variant.fields" :key="index" :colspan="index == 0?2:''">{{field.name}}</th>
                </tr>
              </thead>
              <tbody>
                <tr :data-id="product.id" v-for="(product, pIndex) in variant.products" :key="pIndex" :class="{ 'kp-table-title': product.group == 1 }" :data-group="product.group">
                  <td v-if="product.image">
                    <img :src="'https://' + kp10Host + product.image" @click="showDescription(product)">
                  </td>
                  <td v-for="(value, index) in product.values" :key="index" :colspan="product.group?1 + variant.fields.length:''" :data-type="value.type || ''">
                    <span  
                      :contenteditable="editContent(value.type || '')" 
                      :data-id="value.id" 
                      @blur="changeItemValue($event.target.innerText, value, variant.id)"
                      v-on:keypress="isNumber(event, value.type)"
                      class="single-line"
                    > 
                      <!-- @input="inputNumber($event.target.innerText)" -->
                    <!-- @keyup.enter="changeItemValue($event.target.innerText, value, variant.id)" -->
                      {{printValue(value)}}
                    </span>
                      <!-- <svg-icon :icon="currency" v-if="(value.type == 'price') || (value.type == 'cost')"></svg-icon> -->
                    <span v-show="product.group == 1" :contenteditable="false">
                      ({{parseF(numberFormat(kpGroupCost[product.id]))}} <svg-icon :icon="currency"></svg-icon>)
                    </span>
                  </td>
                  <!-- <td>
                    <img src="">
                  </td>
                  <td data-id="" data-index="" data-type="" data-value-in-price="" colspan="">
                    <span contenteditable="true"></span>
                    (<span class="kp-group-cost"></span> )
                  </td> -->
                </tr>
              </tbody>
            </table>
            <div class="kp-add-product" v-show="!varSelected">
              <select class="search-product" id="searchProduct" data-live-search="true">
                <option value="-1">Поиск товаров</option>
              </select>
              <div class="form-group display-inline" v-show="showCreateProduct">
                <button type="button" class="btn btn-default" @click="createProduct">+ Создать товар</button>
                <!-- <button type="button" class="btn btn-primary" @click="createProduct">+ Создать товар</button> -->
              </div>
              <!-- <button type="button" class="create-product">+Создать продукт</button> -->
            </div>
            <table class="kp-card-entity-summary">
              <tr>
                <td class="kp-special-discount">
                  <div class="kp-row-special-discount" data-id="" v-for="(specialDiscount, index) in variant.special_discounts" :key="index">
                    <span>
                      {{specialDiscount.name}}
                    </span> (
                    <span class="kp-special-discount-value">{{numberFormat(specialDiscount.value)}} <svg-icon :icon="currency"></svg-icon></span>)
                  </div>
                </td>
                <td class="kp-discount">
                  <div :class="{'kp-display-none': !kpDiscount[variant.id] }">
                    <div class="kp-offer-title">Цена без скидки</div>
                    <div class="kp-discount-value">
                      <span>
                        {{kpDiscount[variant.id]}} <svg-icon :icon="currency"></svg-icon>
                      </span>
                    </div>
                  </div>
                </td>
                <td class="kp-price">
                  <div>
                    <div class="kp-offer-title">Стоимость</div>
                    <div class="kp-price-value">
                      <!-- <span>{{variant.price}}</span>  -->
                      <span>{{numberFormat(variant.price)}} <svg-icon :icon="currency"></svg-icon></span>
                      <div class="kp-price-tax">
                        <div v-if="(variant.tax == 1)">
                          Включая НДС: {{tax}} <svg-icon :icon="currency"></svg-icon>
                        </div>
                        <div v-if="(variant.tax == 2)">
                          <div>ндс не облагается</div>
                          <div>(согласно п.2, ст.346.11 нк рф)</div>
                        </div>
                      </div>
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
  </div>
</template>

<script>
import svgIcon from "./Svg";
require('../../../../public/plugins/select2/dist/js/select2.full.min.js');
// require('../../../../public/plugins/select2/dist/js/i18n/ru.js');
$.fn.select2.defaults.set('language', 'ru');
export default {
  props: {
    offer: {
      default: ""
    }
  },
  components: {
    svgIcon
  },
  data() {
    return {
      errors: {
        name: false,
        email: false,
        surname: false
      },
      showCreateForm: false,
      apiKey: "",
      mpApiToken: "",
      kp10Host: "",
      userId: "",
      host: window.location.hostname,
      template: [],
      offerId: "",
      offerUrl: "",
      offerName: "",
      dealId: "",
      dataOfferId: "",
      loadOfferShow: false,
      variants: [],
      variantsForCancel: [],
      cardTabsActive: "kp-selected kp-card-tabs-item-preload",
      currency: '',
      // currency: 'uah',
      selectedVariantIndex: 0,
      showEditOfferBtn: false,
      totalCostValue: 0,
      kpGroupCost: [],
      kpDiscount: [],
      rotateArrow: true,
      arrowIcon: "arrow-down",
      kpTooltipCopy: 'Скопировать ссылку',
      productImg: '',
      productName: '',
      productDesc: '',
      productCost: '',
      productCount: '',
      productPrice: '',
      productDiscount: '',
      productGoodColl: [],
      selectTemplateShow: false,
      tempSearch: [],
      defaultProductImg: '',
      createOfferBlock: false,
      showKP10Widget: false,
      preloader: false,
      disabledBtn: true,
      showCreateProduct: true,
      tax: 0,
      varSelected: 0,
      noClick: true,
      uid: 0
    };
  },

  // beforeUpdate() {
  //   console.log('beforeUpdate');
  // },

  mounted: function() {
    let href = window.location.href.split("/");

    if (href[3] == 'deals') {
      this.dealId = href[4];
    } else if (href[3] == 'bp') {
      this.dealId = href[6];
    }
    
    a9n.user().then((current_user) => {
      this.uid = current_user.id;
    });

    //Get kp10Token
    window.axios
      .get("https://" + this.host + "/api/v3/userSetting/kp10Token")
      .then(response => {
        this.apiKey = response.data.data.value;

        //Get kp10Host
        window.axios
          .get("https://" + this.host + "/api/v3/userSetting/kp10Host")
          .then(response => {
            this.kp10Host = response.data.data.value;
            this.loadOffer();

          })
          .catch(error => {
            // window.ajaxError(error);
          });
      })
      .catch(error => {
        // window.ajaxError(error);
      });
  },

  methods: {
    //
    update(input, value) {
      //Notify parent element
      this.$parent.$emit("update", {
        field: input,
        value: value
      });
      //Clear errors
      this.removeError(input);
    },

    addSelect(input) {
      this.showCreateForm = true;
      window.axios.defaults.headers.common["Accept"] = "application/json";
      window.axios.defaults.headers.common["Authorization"] =
        "Bearer " + this.apiKey;

      window.axios
        .get("https://" + this.kp10Host + "/api/offers/list/json")
        .then(response => {
          this.template = response.data.data.data;
          this.$nextTick(() => {
            $('.select-template').select2({
              placeholder: "Выберите шаблон",
              width: '270px',
              // minimumInputLength: 3,
              ajax: {
                delay: 250,
                headers: {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + this.apiKey
                },
                url: "https://" + this.kp10Host + "/api/offers/list/json",
                data: function(params) {
                  var query = {
                    search: params.term,
                    page: params.page || 1
                  };
                  // Query parameters will be ?search=[term]&page=[page]
                  return query;
                },
                processResults: function(data, params) {
                  params.page = params.page || 1;
                  // Tranforms the top-level key of the response object from 'items' to 'results'
                  return {
                    results: $.map(data.data.data, function(item) {
                      if (item && typeof item == 'object') {
                          return {
                            id: item.id,
                            text: item.offer_name+' '+ item.template.version
                          };
                      }
                    }),
                    pagination: {
                      more: params.page * 10 < data.total
                    }
                  };
                }
              }
            })
            .on("change", e => {
              //update data in parent component
              this.offerId = $(e.target).val();
            });
            this.selectTemplateShow = true;
          });
        })
        .catch(error => {
          // window.ajaxError(error);
        });
        //Default language for select2
        // $.fn.select2.defaults.set('language', 'ru');
    },

    selectTemplate(index) {
      this.offerId = index.target.value;

      if (this.offerId != '' && this.offerName != '') {
        this.noClick = false;
        this.disabledBtn = false;
      }
      // window.axios.put('/api/offers/' + this.offerId + '/copy', { name: offer_name })
      //   .then((response) => {

      //   })
      //   .catch((error) => {
      //       // window.ajaxError(error);
      //   });
    },

    inputEvent(index){
      
      var input = index.target.value;

      if (this.offerId != '' && input != '') {
        this.noClick = false;
        this.disabledBtn = false;
      } else {
        this.noClick = true;
        this.disabledBtn = true;
      }

    },

    createOffer() {
      this.preloader = true;
      this.disabledBtn = true;
      
      if (this.offerName != "" && this.offerId != "") {
        window.axios
          .put( "https://" + this.kp10Host + "/api/offers/" + this.offerId + "/copy", { name: this.offerName }
          )
          .then(response => {
            this.dataOfferId = response.data.offer.id;
            window.axios
              .put(
                "https://" + this.kp10Host + "/api/megaplan/deals/" + this.dealId + "/offer", { id: response.data.offer.id }
              )
              .then(response => {
                // $("#kp10-popup").modal("show");
                this.loadOffer(); 
                this.preloader = false;
              })
              .catch(error => {
                // window.ajaxError(error);
              });
          })
          .catch(error => {
            // window.ajaxError(error);
          });
      }
    },

    openEditor() {
      this.loadOfferShow = true;
      this.showCreateForm = false;
      $("#kp10-popup").modal("hide");
    },

    loadOffer() {
      window.axios.defaults.headers.common["Accept"] = "application/json";
      window.axios.defaults.headers.common["Authorization"] =
        "Bearer " + this.apiKey;
      
      window.axios
        .get("https://" + this.kp10Host + "/api/megaplan/deals/" + this.dealId + "/offer")
        .then(response => {
          let check = false;

          this.mpApiToken = response.data.api_token;

          if (response.data.offer && response.data.offer.currency.data) {
            this.currency = this.offerCurrency(response.data.offer);
          }

          if (response && response.data && response.data.offer && response.data.offer.variants) {

            this.defaultProductImg = response.data.offer.productEmptyImg; 
            response.data.offer.variants.forEach(variant => {

              if (variant.selected == 1) {
                this.varSelected = 1;
              }

              if (variant.products && variant.products.length) {
                check = true;

                //Sort
                variant.products.sort(function(a, b) {
                  return a.index - b.index;
                });

                variant.products.forEach(product => {
                  //Sort values
                  product.values && product.values.sort(function(a, b) {
                      return a.index - b.index;
                    });
                });
              }

              //Sort fields
              variant.fields && variant.fields.sort(function(a, b) {
                  return a.index - b.index;
                });

            });
            // не показуємо блок створення КП
            this.createOfferBlock = false;
            this.showKP10Widget   = true;

          } else {
            // показуємо блок створення КП
            this.createOfferBlock = true;
            this.showKP10Widget   = false;
          }

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
                      "Accept" : "application/json",
                      "Authorization" : "Bearer " + this.apiKey
                  },
                  url: "https://" + this.kp10Host + "/api/products/list/json", 
                  data: function(params) {
                    var query = {
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

              var prod_id = $(e.target).val();
              var selectedVariant = this.variants[this.selectedVariantIndex];
              var filteredProduct = this.tempSearch.find(function (el) {
                return el.id == prod_id;
              });

              this.tempSearch.forEach( (item) => {                
                if (item.id == prod_id) {
                  let productId = 0,
                      // productId = item.id,
                      index = selectedVariant.products.length,
                      d = new Date(),
                      cProductId = d.getTime(), //new product id;
                      type = selectedVariant.type,
                      fakeProductId = '#modal-product-' + (cProductId + productId) + '-' + type,
                      values = [];

                  // selectedVariant = this.variants[this.selectedVariantIndex];

                  if (!selectedVariant['db-id']) {
                    selectedVariant['db-id'] = selectedVariant.id;
                  }

                  if (selectedVariant.products.length) {
                    for (var i = 0; i < selectedVariant.products.length; i++) {                      
                    // for (var i = 0; i < selectedVariant.fields.length; i++) {                      
                      let props = selectedVariant.products[i];
                      
                      props['db-id'] = 0;

                      if (props.group == 0) {                 
                        let val = props.values;
                        var value = '';

                        val.forEach( (v, k) => {
                          let valueInPrice = 0;

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
                            'db-id' : 0,
                            index: k,
                            type: v.type,
                            value: value,
                            value_in_price: valueInPrice,
                            variant_product_id: item.id
                          });

                        });
                        break; 
                      }
                    }
                  }
                  else {
                      for (var i = 0; i < selectedVariant.fields.length; i++) {              
                          let val = selectedVariant.fields[i];
                          var value = '';

                            let valueInPrice = 0;

                            switch (val.type) {
                              case "name":
                                value = filteredProduct.name;
                                break;
                              case "cost":
                                value = this.numberFormat(filteredProduct.cost);
                                break;
                              case "count":
                                value = '1';
                                break;
                              case "price":
                                value = this.numberFormat(filteredProduct.cost);
                                break;
                              case "discount":
                                value = '0';
                                break;
                              case "good-coll":
                                value = '0';
                                valueInPrice = 1;
                                break;
                            }

                            values.push({
                              'db-id' : 0,
                              index: i,
                              type: val.type,
                              value: value,
                              value_in_price: valueInPrice,
                              variant_product_id: '0'
                            });
                      }
                  }

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

                  for (var i = 0; i < selectedVariant.products.length; i++) { 
                  // for (var i = 0; i < selectedVariant.fields.length; i++) { 
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
        .catch(error => {
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
    //  self.request('POST', '/api/editor/' + self.data.offer.id + '/cancel-variant-selection', {}, function (response) {//success

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

    cancelEdit: function() {

      let copy = JSON.parse(JSON.stringify(this._beforeEditingCache));

      this.variants = copy;
      this.showEditOfferBtn = false;
      
      // this._beforeEditingCache = Object.assign({}, this.variants);
    },

    saveForm() {
      let copy = JSON.parse(JSON.stringify(this.variants));
      var result = [];

      copy.forEach(function(params) {

        let fields = [],
            products = [],
            values = [],
            variant = [];
        var variant_id = params.id;

        params.fields.forEach(function(field) {
          let name = field.name, 
              fieldId = field.id;

          fields.push({
            name: name,
            'db-id': fieldId
          });
        });

        params.products.forEach(function(product) {
          var values = [];

          if (product.variant_id == variant_id) {
            product.values.forEach(function(val) {

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
        .post("https://" + this.kp10Host + "/api/editor/" + this.offerId + "/store", {settings: { variants: result }})
        .then(response => {
          let message = response.data.message;

          $("#kp10-popup").find('.modal-body').html('<div>'+message+'</div>');
          $("#kp10-popup").modal("show");
          
          this.loadOffer();
          this.showEditOfferBtn = false;

          setTimeout(function() {
            $("#kp10-popup").modal("hide");
          }, 1000);

        })
        .catch(error => {
          // window.ajaxError(error);
        });

    },

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
        let parse = str.replace(/[^0-9.]/g, '');
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

            if(product.group){
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
            if ( variant.tax == 1 ) {
              let totalSum = variant.price;
              
              this.tax = this.numberFormat(Math.round(totalSum / 1.18 * 0.18));              
            }

            //calculate total cost
            totalCost += cost;
            //calculate total cost without discount
// TODO: перевірити чи коректно працює спеціалина знижка при додаванні стовпчика
            if (discountEnabled || special_discounts.length) {
              totalCostWithoutDiscount += price * count * goodsValue;
              this.kpDiscount[variant.id] = this.parseF(totalCostWithoutDiscount);
              if (!this.kpDiscount[variant.id]) {this.kpDiscount[variant.id] = "0";}
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

      this.variants.forEach( (variant) => {

        if (variantId == variant.id) {
            variant.price = this.parseF(totalCost);
            variant.products.forEach( (product) => {
              if (product.group == 1) {
                groupCosts.forEach( (item) => {
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
      return 'false';
    },

    isNumber: function(evt, type) {

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
    changeItemValue: _.debounce(function(item, value, variantId) {

      value.value = item;
      this.calculatePositionsPrices(variantId);
      this.showEditOfferBtn = true;

    }, 500),

    //On change item name
    changeItemName: _.debounce(function(e) {
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
              if (result.length == 0) {result = '0';}
              break;
          }
        return result;  
    }, 

    //Copy to clipboard
    kpCopyLink() { 
      const el = document.createElement('textarea');
      el.value = 'https://' + this.kp10Host + '/' + this.offerUrl;
      // el.value = 'https://kp10.pro/' + this.offerUrl;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);

      this.kpTooltipCopy = 'Ссылка скопирована';
      setTimeout( () => {
        this.kpTooltipCopy = 'Скопировать ссылку';
      }, 1000);

    },

    showDescription(product) {

      this.productImg      = product.image;
      this.productDesc     = product.description;
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
            this.productGoodColl.push({'name': this.variants[this.selectedVariantIndex].fields[element.index].name,'value': element.value});
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
      return $('<span><img class="kp10-widget-product-thumbnail" src="https://' + this.kp10Host + state.img +'" class="img-flag">' + state.text + ' ' + this.numberFormat(state.cost) + '<svg-icon :icon="currency"></svg-icon>' + '</span>');
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
                'db-id' : 0,
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
		offerCurrency (offer) {
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

    remoutn () {
      this.$mount('#kp10-widget-megaplan'); 
      // this.$mount(); 

      console.log('TEST remoutn mount');
      let href = window.location.href.split("/");

      if (href[3] == 'deals') {
        this.dealId = href[4]; 
      } else if (href[3] == 'bp') {
        this.dealId = href[6];
      }
      
      a9n.user().then((current_user) => {
        this.uid = current_user.id;
      });

      //Get kp10Token
      window.axios
        .get("https://" + this.host + "/api/v3/userSetting/kp10Token")
        .then(response => {
          this.apiKey = response.data.data.value;

          //Get kp10Host
          window.axios
            .get("https://" + this.host + "/api/v3/userSetting/kp10Host")
            .then(response => {
              this.kp10Host = response.data.data.value;
              this.loadOffer();

            })
            .catch(error => {
              // window.ajaxError(error);
            });
        })
        .catch(error => {
          // window.ajaxError(error);
        });
    }
  }
};
</script>