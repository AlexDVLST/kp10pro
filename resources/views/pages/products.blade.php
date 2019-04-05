@extends('layouts.app')

@section('styles')
	<!-- Стандарт -->
	<link rel="stylesheet" href="{{asset('/css/pages/products.css')}}">
@stop

@section('scripts')
	<!-- Стандарт -->
	<script src="{{asset('js/pages/products.min.js')}}"></script> 
@stop

@section('content')
	<div class="product-list-table-content" id="app">
		<div class="box">
			<div class="box-header">
				<h3 class="box-title">{{$page->title}}</h3>

				<div class="box-tools">
					<div class="input-group input-group-sm search">
						<input @keyup.prevent="search()" id="searchfield" class="form-control pull-right">
						<div class="input-group-btn">
							<button class="btn btn-default">
								<i class="fa fa-search"></i> 
							</button> 
							@can('create product')
							<a href="{{url('/products/create')}}" class="btn btn-default">
								<i class="fa fa-cart-plus"></i>
							</a>
							@endcan
							@if(Auth::user()->hasAnyPermission(['delete product', 'import product']))
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									<span class="fa fa-caret-down"></span></button>
								<ul class="dropdown-menu">
									@can('delete product')
									<li><a @click.prevent="deleteSelected" href="#">Удалить</a></li>
									@endcan
									@can('import product')
									<li><a @click.prevent="importExcel" href="#">Импорт</a></li>
									<li><a href="{{url('/products/excel')}}">Экспорт</a></li>
									@endcan
								</ul>
							@endif
						</div> 
					</div> 
				</div> 
			</div>
			
			<div class="box-body"> 
				<div class="table-responsive">
					<table class="table table-hover table-striped display productTable">
						<thead>
							<template v-if="enableFieldCheckboxBlock === true">
								<div v-click-outside="displayFieldsCheckboxes"  class="enableFieldCheckboxBlock">
									<label>Настройка отображаемых полей</label> <a @click.prevent="displayFieldsCheckboxes" href="#"><i class="pull-right fa fa-close"></i></a><br>
									<span class="enableFieldCheckboxSpan">
										<input @change="ProductColl" class="enableFieldCheckbox"  
										:value="'collName'"
										v-model="enableProductColl" 
										type="checkbox">Название 
									</span><br>
									<span class="enableFieldCheckboxSpan">
											<input @change="ProductColl" class="enableFieldCheckbox"  
											:value="'collDescription'"
											v-model="enableProductColl" 
											type="checkbox">Описание 
										</span><br>
									<span class="enableFieldCheckboxSpan"> 
										<input @change="ProductColl" class="enableFieldCheckbox" 
										:value="'collCost'"
										v-model="enableProductColl" 
										type="checkbox">Цена
									</span><br>
									{{-- <span class="enableFieldCheckboxSpan">
										<input @change="ProductColl" class="enableFieldCheckbox" 
										:value="'collPrimeCost'"
										v-model="enableProductColl" 
										type="checkbox">Себестоимость
									</span><br> --}}
									@if(isset($dopfields_list))
										@if(is_object($dopfields_list))   
											@foreach($dopfields_list as $dopfield) 
											<span class="enableFieldCheckboxSpan">
												<input @change="ProductColl" class="enableFieldCheckbox" 
												:value="'collCustomField{{$dopfield->id}}'"
												v-model="enableProductColl" 
												type="checkbox">{{$dopfield->name}}
											</span><br>
											@endforeach  
										@endif  
									@endif
								</div> 
							</template>
						<tr>
							{{--  <th @click.prevent="sortby('id')">Id</th>  --}} 
							<th>
								<input v-bind:class="{ selectAllActive: isProductsSelectChecked }" class="selectAll" @click="selectAllProducts($event)" type="checkbox">
								<a class="productFieldsGear" href="#"><i @click.prevent="displayFieldsCheckboxes" class="fa fa-gear"></i></a>
								<span @click.prevent="sortby('article')">Артикул</span>
							</th>
							<th v-if="enableProductColl.includes('collName')" @click.prevent="sortby('name')">Название</th>
							<th v-if="enableProductColl.includes('collDescription')" @click.prevent="sortby('description')" class="product-description-title">Описание</th>
							<th v-if="enableProductColl.includes('collCost')" @click.prevent="sortby('cost')">Цена</th>  
							{{-- <th v-if="enableProductColl.includes('collPrimeCost')" @click.prevent="sortby('prime_cost')">Себестоимость</th> --}}
							@if(isset($dopfields_list))
								@if(is_object($dopfields_list)) 
									@foreach($dopfields_list as $dopfield)
										<th v-if="enableProductColl.indexOf('collCustomField{{$dopfield->id}}') !== -1 ">{{$dopfield->name}}</th>
									@endforeach
								@endif  
							@endif
						</tr>
						</thead>
						<tbody id="table-body">
						<template>
							<tr v-for="product in products">
								<td>
									<table> 
										<td width="30px;">
											<input :id="product.id" v-bind:class="{ productSelectActive: isProductsSelectChecked }" 
												@change="isProductsCheckboxChecked" 
												class="productSelect" type="checkbox" :value="product.id" v-model="checkedProduct" @click="shiftClick($event)">  
										</td> 
										<td>
											<a target="_blank" :href="'/products/' + product.id + '/edit'">@{{ product.article }}</a>
										</td>
									</table> 
								</td>
								<td v-if="enableProductColl.includes('collName')">
									<table>
										<tr>
											<td>
												{{-- TODO: Get Only first image in array --}}
												<img class="product-image" :src='product.file[0]' @click.prevent="changeFile(product.id)"> 
											</td>
											<td>
												@{{ product.name }}
											</td>
										</tr>
									</table>
								</td>
	
								<!-- Описание товара --> 
								<td class="product-description" v-if="enableProductColl.includes('collDescription')"> 
									<p v-if="collapsedDescriptions.includes(product.id)===false" class="description-content" > @{{ product.description }}</p>
									<p v-if="collapsedDescriptions.includes(product.id)" >@{{ product.description }}</p>
									<div class="collapsedButton"> 
									<span v-if="product.description">
									<i v-if="collapsedDescriptions.includes(product.id)===false && product.description.length > 35" @click="showDescription(product.id)" class="fa fa-caret-down"></i>
									</span>
									<i v-if="collapsedDescriptions.includes(product.id)" @click="showDescription(product.id)" class="fa fa-caret-up"></i>
									</div>
								</td>   
							
								<td v-if="enableProductColl.includes('collCost')"> 
									@{{ product.cost }} <span v-if="product.cost"> @{{basicCurrency}} <span>
								</td>
								{{-- <td v-if="enableProductColl.includes('collPrimeCost')">
									@{{ product.prime_cost }} <span v-if="product.prime_cost"> @{{basicCurrency}} </span>
								</td>  --}}
								@if(isset($dopfields_list))
									@if(is_object($dopfields_list))
										@foreach($dopfields_list as $dopfield)
											<td v-if="enableProductColl.includes('collCustomField{{$dopfield->id}}')" class="align-middle">@{{ product.dopfields[{!! $dopfield['id'] !!}]}}</td>
										@endforeach
									@endif 
								@endif
								{{--  <td class="align-middle"><a href="#" @click.prevent="removeProduct(product.id)"
									class='button-remove remove-product'>Удалить</a></td>  --}}
						
							</tr>
						</template>
						</tbody>
					</table>
				</div>
				<pagination v-if="pagination.last_page > 1" :pagination="pagination" :offset="5"
				            @paginate="fetchProducts()"></pagination>
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
	</div> 

@endsection