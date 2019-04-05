@extends('layouts.app') 
@section('content')
<div class="box box-warning">
    <div class="box-header with-border">
        <i class="fa fa-info"></i>
        <h3 class="box-title">Оплата заказа</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <h4>Ожидание подтверждения оплаты</h4>
        <p>Вернутся на страницу <a href="{{url('/settings/order')}}">Оплата сервиса</a></p>
    </div>
    <!-- /.box-body -->
</div>
@stop