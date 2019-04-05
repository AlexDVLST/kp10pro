@extends('layouts.app') 
@section('title', 'Редактирование товара: '.$product->name) 
@section('description','Товар '.$product->name)

@section('styles')
<!-- Стили страницы -->
<link rel="stylesheet" href="{{asset('/css/pages/product-edit.css')}}">
@stop 

@section('scripts')
<script src="{{asset('js/pages/products-edit.min.js')}}"></script>
@stop 

@section('content')
<section class="content" id="product-edit-app">
    <div class="row">
        <!-- left column -->
        <div class="">
            <!-- general form elements -->
            <div class="box box-primary">

                @if($page_type == "edit")

                <div class="box-header with-border">
                    <h4 class="box-title">Товар : {{$product->name}}</h4>
                </div>

                @endif

                <!-- /.box-header -->
                <!-- form start -->

                <form role="form">
                    <div class="box-body">

                        {{--<input id="page-type" type="hidden" value="{{$page_type}}">--}} 
                        @if($page_type == "edit")
                        <input type="hidden" id="product-id" value="{{$product->id}}">
                        @endif

                        <div class="col-md-8">
                            <h4 id="h4_product_name">Название товара:</h4>
                            <input id="product_name" class="form-control" type="text" value="{{$product->name}}" placeholder="Название">
                            <h4 id="h4_product_article">Артикул:</h4>
                            <input id="product_article" class="form-control" type="text" @if($product->article) value="{{$product->article}}"
                            @endif placeholder="Артикул"> 
                            <h4 id="h4_product_cost">Цена:</h4>
                            <input :maxlength="14" v-on:keypress="isNumber" id="product_cost" class="form-control" type="text" @if($product->cost)
                            value="{{$product->cost}}" @endif placeholder="Цена"> 
                            {{-- <h4 id="h4_product_prime_cost">Себестоимость:</h4>
                            <input :maxlength="14" v-on:keypress="isNumber" id="product_prime_cost" class="form-control" type="text" @if($product->prime_cost)
                            value="{{$product->prime_cost}}" @endif placeholder="Себестоимость">  --}}
                            <h4 id="h4_prod_description">Описание товара:</h4>
                            <textarea id="description" class="form-control" placeholder="Описание товара">@if($product->description){{$product->description}}@endif</textarea>                            @if (is_array($customFieldsArray)) @foreach($customFieldsArray as $field)
                            <h4>{{$field['name']}}: </h4>
                            @if(isset($field['product_value']))
                            <input @if($field[ 'type']=="cost" OR $field[ 'type']=="primecost" ) :maxlength="14" v-on:keypress="isNumber" @endif placeholder="{{$field['name']}}"
                                data-customFieldId="{{$field['id']}}" class="product-dopfield form-control" type="text" value="{{$field['product_value']}}">                            @else
                            <input @if($field[ 'type']=="cost" OR $field[ 'type']=="primecost" ) :maxlength="14" v-on:keypress="isNumber" @endif placeholder="{{$field['name']}}"
                                data-customFieldId="{{$field['id']}}" class="product-dopfield form-control" type="text" value="">                            @endif @endforeach @endif
                            <br><br>

                            <button data-type="{{$page_type}}" @click.prevent="saveProduct" class="btn btn-primary save-product">Сохранить
                                </button>

                        </div>

                        <!-- Фото товара -->
                        <div class="col-md-4">
                            <h4>Фото товара:</h4>
                            <img @click.prevent="changeFile" class="product-file" :src="fileSrc" :data-id="fileId" data-fclick="true">
                            <br><br>

                            <!-- Дополнительные поля товаров -->

                            <button @click.prevent="addCustomField()" class="btn btn-default">
                                    <i class="fa fa-server"></i> &nbsp; Добавить поле
                                </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- File manager component -->
    <div class="modal fade modal-default in file-select" :class="{'show': showModalFileManager}"> 
        <div class="" :style="{width: '90%', margin: '30px auto'}">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="pull-right close-modal cursor-pointer" @click="closeModal">
                        <i class="fa fa-close"></i>
                    </span>
                    <h4 class="modal-title">Добавление фото к товару</h4>
                </div>
                <div class="modal-body file-manager-modal">
                <file-manager ref="fileManager"></file-manager>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection