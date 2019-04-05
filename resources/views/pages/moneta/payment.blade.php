@extends('layouts.app') 
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
        <i class="fa fa-info"></i>
        <h3 class="box-title">Оплата заказа</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <h4>Оплата заказа была отменена</h4>
        <p>Вернутся на страницу <a href="{{url('/settings/order')}}">Оплата сервиса</a></p>
    </div>
    <!-- /.box-body -->
</div>
@stop