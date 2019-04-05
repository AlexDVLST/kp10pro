@foreach($products as $product)
<div id="modal-product-{{$product['id']}}-standard" class="display-none"
    data-src="#modal-product-{{$product['id']}}-standard"
    data-gjs-type="disabled">
    <div class="modal-product" data-gjs-type="disabled">
        <div class="modal-product__inner" data-gjs-type="disabled">
            <div class="modal-product__close" data-gjs-type="disabled"></div>
            <div class="row" data-gjs-type="disabled">
                <div class="col-md-6" data-gjs-type="disabled">
                    <div class="modal-product__preview" data-gjs-type="disabled">
                        <img src="{{$product['img']}}"
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
                                <span data-gjs-type="editable" data-kp10-update-prices="true">
                                    {{$product['name']}}</span>
                        </div>
                        <div class="modal-product__price" data-gjs-type="disabled">
                            <span data-gjs-type="disabled">Цена: </span>
                            <span class="kp10-cell-price"
                                data-gjs-type="editable"
                                data-kp10-update-prices="true">{{$product['price']}}</span> 
                                <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                        </div>
                        <div class="kp10-goods-coll" data-gjs-type="disabled"></div>
                        <div class="modal-product__count" data-gjs-type="disabled">
                            <b data-gjs-type="disabled">Количество: </b>
                            <span class="kp10-cell-count"
                                data-gjs-type="editable"
                                data-kp10-update-prices="true">{{$product['count']}}</span>
                        </div>
                        <div class="kp10-discount-container" data-gjs-type="disabled"></div>
                        <div class="modal-product__count" data-gjs-type="disabled">
                            <b data-gjs-type="disabled">Итого: </b>
                            <span class="kp10-cell-cost" data-gjs-type="disabled">{{$product['cost']}}</span> 
                            <i class="fa fa-rub currency" data-gjs-type="disabled"></i>
                        </div>
                        <div class="modal-product__info" data-gjs-type="disabled">
                            <div data-gjs-type="editable">
                                {{$product['productInfo']}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button data-fancybox-close="" class="fancybox-close-small" title="Close" data-gjs-type="disabled"></button>
</div>
@endforeach