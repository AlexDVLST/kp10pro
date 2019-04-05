@extends('layouts.app') 
@section('styles') 
@stop 
@section('scripts')
<script src="{{asset('js/pages/settings/order.min.js')}}"></script>
@stop 
@section('content')
<div class="row" id="app">
    <template v-if="loaded">
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Тариф @{{order.tariff.price}} <i class="fa fa-rub"></i></h3>
                </div>
                <div class="box-body">
                    <template v-if="order.isActive">
                        <h4 class="text-center">Активен до @{{order.expired_at_format}}</h4>

                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Лицензии</td>
                                    <td>@{{order.licenses}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <template v-else>
                        <h4 class="text-center">Тариф не оплачен</h4>
                    </template>
                </div>
                <div class="box-footer">
                    <a href="{{url('/settings/order/create')}}" class="btn" :class="{'btn-primary': order.isActive, 'btn-success': !order.isActive}"> 
                        <i class="fa fa-edit"></i>
                        <span v-if="order.isActive">Продлить</span>
                        <span v-else>Купить</span>
                    </a>
                    <a href="{{url('/settings/order/edit')}}" class="btn btn-default pull-right" v-if="order.isActive"> 
                        <i class="fa fa-plus"></i>
                        Добавить лицензии
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <template v-if="unpaidInvoices.length>0">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Неоплаченные счета</h3>
                    </div>
                    <div class="box-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Дата создания</th>
                                    <th>Статус</th>
                                    <th>Сумма</th>
                                    <th>Детали</th>
                                    <th>Скидка</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(invoice, index) in unpaidInvoices" :key="index">
                                    <td>@{{invoice.created_at_format}}</td>
                                    <td>@{{invoice.statusTranslation}}</td>
                                    <td>@{{invoice.total}} <i class="fa fa-rub"></i></td>
                                    <td>
                                        <template v-if="invoice.payment_info.months">
                                            Месяцы: @{{invoice.payment_info.months}},
                                            Лицензии: @{{invoice.payment_info.licenses}}
                                        </template>
                                    </td>
                                    <td><span v-if="invoice.discount">@{{invoice.discount}} <i class="fa fa-rub"></i></span></td>
                                    <td>
                                        <form class="pull-left" method="post" action="https://www.payanyway.ru/assistant.htm">
                                            <input type="hidden" name="MNT_ID" value="{{$mntId}}">
                                            <input type="hidden" name="MNT_TRANSACTION_ID" :value="invoice.reference">
                                            <input type="hidden" name="MNT_CURRENCY_CODE" value="RUB">
                                            <input type="hidden" name="MNT_AMOUNT" :value="invoice.total">
                                            <input type="hidden" name="MNT_DESCRIPTION" value="Оплата заказа">
                                            <input type="hidden" name="MNT_SUCCESS_URL" value="{{url('/moneta/success')}}">
                                            <input type="hidden" name="MNT_FAIL_URL" value="{{url('/moneta/failure')}}">
                                            <input type="hidden" name="MNT_RETURN_URL" value="{{url('/moneta/payment')}}">
                                            <input type="hidden" name="MNT_INPROGRESS_URL" value="{{url('/moneta/processing')}}">
                                            <input type="hidden" name="MNT_CUSTOM1" value="{{auth()->user()->accountId}}">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-credit-card"></i> Оплатить</button>
                                        </form>
                                        &nbsp;
                                        <button class="btn btn-default" @click="cancelInvoice(invoice, index)"><i class="fa fa-trash"></i> Отменить</button>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection