@extends('layouts.app')

@section('title', $page->title)
@section('description',$page->description)

{{-- Show tour --}}
@if(\App\Models\UserMeta::getMeta('show-tour', true))
@section('styles')
	<link rel="stylesheet" href="{{asset('plugins/bootstrap-tour/css/bootstrap-tour.min.css')}}">
	<link rel="stylesheet" href="{{asset('/css/bootstrap-tour.css')}}">
@stop
@section('scripts')
	<script src="{{asset('plugins/bootstrap-tour/js/bootstrap-tour.min.js')}}"></script>
	<script src="{{asset('bower_components/datatables.net/js/jquery.dataTables.js')}}"></script>
@stop
@endif 

@section('scripts')
	<script src="{{asset('js/pages/products-dopfields.min.js')}}"></script>
@stop

@section('content')
	<div class="product-custom-fields-list-table" id="app">
		<div class="box">
			<div class="box-header"> 
				<h3 class="box-title">Список дополнительных полей</h3>
				<div class="box-tools pull-right">
					<a @click.prevent="addCustomField()" class="btn btn-default">
						<i class="fa fa-user-plus"></i>
						Добавить
					</a>
				</div>
			</div>
			<div class="box-body"> 
				<div class="table-responsive">
					<table class="table table-bordered table-striped display">
						<thead>
						<tr>
							<th>Id</th>
							<th>Название</th>
							<th>Тип</th>
							<th>Редактирование</th>
						</tr>
						</thead>
						<tbody id="table-body">
						<template>
							<tr class="table" v-for="customfield in customfields">
								<td>
									@{{ customfield.id }}
								</td>
								<td>
									@{{ customfield.name }}
								</td>
								<td>
									@{{ customfield.type }}
								</td>
								<td><a @click.prevent="deleteCustomField(customfield.id)" class='button-remove remove-customfields'>Удалить</a>
									<a @click.prevent="editCustomField(customfield.id)" class='button-remove remove-customfields'>Изменить</a>
								</td>
							</tr>
						</template>
						</tbody>
					</table>
				</div>
				<pagination v-if="pagination.last_page > 1" :pagination="pagination" :offset="5"
				            @paginate="fetchProducts()"></pagination>
			</div>
		</div>
	</div>
@endsection