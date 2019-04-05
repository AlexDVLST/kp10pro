@extends('layouts.app') 
@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
        <i class="fa fa-info"></i>
        <h3 class="box-title">Оплата заказа</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <h4>Произошла ошибка при оплате заказа</h4>
        <p>Вернутся на страницу <a href="{{url('/settings/order')}}">Оплата сервиса</a></p>
    </div>
    <!-- /.box-body -->
</div>
@stop