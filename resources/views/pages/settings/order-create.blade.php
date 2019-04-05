@extends('layouts.app') 
@section('styles') 
@stop 
@section('scripts') 
<script src="{{asset('js/pages/settings/order-create.min.js')}}"></script> 
@stop 
@section('content')
@if($orderNotPaid)
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="fa fa-warning"></i> Внимание!</h4>
        Существуют неоплаченные счета. Отмените или оплатите их. 
        Перейти на страницу <a href="{{url('/settings/order')}}">Оплата сервиса</a> 
    </div>
@endif
<div class="row" id="app">
    <template v-if="loaded">    
        <div class="col-md-4">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">
                        <span v-if="order.isActive">Продление </span>
                        <span v-else>Покупка </span>
                        тарифа
                    </h3>
                    <div class="pull-right">
                        <a href="{{url('/settings/order/create')}}" class="btn btn-success" @click.prevent="createOrder" :class="{disabled: calculatePrice()<=0}"> 
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
                        <label>Месяцы</label>
                            <input class="form-control" type="number" :value="order.months" @input="order.months=$event.target.value" min="1">
                        </div>
                        <div class="col-xs-6">
                            <label>Лицензии</label>
                            <input class="form-control" type="number" :value="order.licenses" @input="order.licenses=$event.target.value" min="1">
                        </div>
                    </div>

                    <h3>
                        Стоимость: <span>@{{calculatePrice()}}</span> <i class="fa fa-rub"></i>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="callout callout-info" v-if="accountBalance>0">
                <h4>Активный период!</h4>
                <p>С учетом неоконченного оплаченного периода остаток @{{accountBalance}} <i class="fa fa-rub"></i></p>
                <p>Вы не сможете пролдить тариф пока на остатке есть сумма. Измените значения Месяцы, Лицензии чтобы Стоимость была больше нуля</p>
            </div>
        </div>
    </template>
</div>
@endsection