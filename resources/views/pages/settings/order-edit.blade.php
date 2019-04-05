@extends('layouts.app') 
@section('styles') 
@stop 
@section('scripts')
<script src="{{asset('js/pages/settings/order-edit.min.js')}}"></script>
@stop 
@section('content')
<div class="row" id="app">
    <template v-if="loaded">
        <div class="col-md-4">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Редактирования заказа</h3>
                    <div class="pull-right">
                        <a href="#" class="btn btn-success" @click.prevent="updateOrder"> 
                            <i class="fa fa-credit-card"></i>
                            Оплатить
                        </a>
                        <form id="moneta-pay" method="post" action="https://www.payanyway.ru/assistant.htm">
                            <input type="hidden" name="MNT_ID" value="{{$mntId}}">
                            <input type="hidden" name="MNT_TRANSACTION_ID" :value="order.invoice.reference">
                            <input type="hidden" name="MNT_CURRENCY_CODE" value="RUB">
                            <input type="hidden" name="MNT_AMOUNT" :value="order.invoice.total">
                            <input type="hidden" name="MNT_DESCRIPTION" value="Оплата заказа">
                            <input type="hidden" name="MNT_SUCCESS_URL" value="{{url('/moneta/success')}}">
                            <input type="hidden" name="MNT_FAIL_URL" value="{{url('/moneta/failure')}}">
                            <input type="hidden" name="MNT_RETURN_URL" value="{{url('/moneta/payment')}}">
                            <input type="hidden" name="MNT_INPROGRESS_URL" value="{{url('/moneta/processing')}}">
                            <input type="hidden" name="MNT_CUSTOM1" value="{{auth()->user()->accountId}}">
                        </form>
                    </div>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-xs-6">
                            <label>Дата окончания</label>
                            <input class="form-control" type="text" disabled :value="order.expired_at_format">
                        </div>
                        <div class="col-xs-6">
                            <label>Лицензии</label>
                            <input class="form-control" type="number" :value="order.licenses" @input="order.licenses=$event.target.value" min="1">
                        </div>
                    </div>

                    <h3>
                        Доплата: <span>@{{order.tariff.price*order.invoice.payment_info.months*order.licenses}}</span> <i class="fa fa-rub"></i>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <p>Добавление лицензий к оплаченному тарифу</p>
            <p>Расчет происходит исходя из даты окончания текущего тарифа</p>
            <p>Стоимость добавления 1 лицензии <span>@{{dayPrice}}</span> <i class="fa fa-rub"></i>/день</p>
        </div>
    </template>
</div>
@endsection