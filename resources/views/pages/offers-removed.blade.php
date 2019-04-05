@extends('layouts.app') 
@section('title', $page->title) 
@section('description', $page->description)
@section('scripts')
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('js/pages/offers-deleted.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<link rel="stylesheet" href="{{asset('/css/pages/offers-removed.css')}}">
@stop 
@section('content')

<div class="offers-deleted-list-table-content" id="app">
	<br>
	<div class="box">
		<div class="box-header">
			<div class="btn-group" v-if="showPannel">
				<button type="button" class="btn btn-default btn-sm" @click="restoreOffers()"><i class="fa fa-reply-all"></i></button>
			</div>
			<h3 v-if="!showPannel" class="box-title">Список удалённых коммерческих предложений</h3>
			<div class="box-tools">
				<div class="input-group input-group-sm search">
					<input @keyup.prevent="search()" id="searchfield" class="form-control pull-right">
					<div class="input-group-btn">
						<button class="btn btn-default"><i class="fa fa-search"></i></button>
					</div>
				</div>
			</div>
		</div>
		<div class="box-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped display">
					<thead>
						<tr>
							<th><input type="checkbox" @click="toogleSelection"></th>
							<th @click.prevent="sortby('offer_name')">Название</th>
							<th>Менеджер</th>
							<th @click.prevent="sortby('url')">Url</th>
							<th @click.prevent="sortby('created_at')">Дата создания</th>
							<th @click.prevent="sortby('created_at')">Дата удаления</th>
							{{--<th>Редактирование</th>--}}
						</tr>
					</thead>
					<tbody id="table-body">
						<template>
							<tr v-for="(offer, index) in offers" :key="index" @mouseover="showByIndex = index" @mouseout="showByIndex = null">
								<td>
									<input type="checkbox" @click="updateCheckedOffers(offer)" :checked="offer.check == 1">
								</td>
								<td>
									<a :href="'/editor/'+ offer.id" target="_blade">@{{ offer.offer_name }}</a>
								</td>
								<td>
									@{{offer.employee.user.surname}}
									@{{offer.employee.user.name}}
								</td>
								<td>
									@{{ offer.url }}
								</td>
								<td>
									@{{ offer.created_at }}
								</td>
								<td>
									@{{ offer.deleted_at }}

									<div class="offer-panel" v-show="showByIndex === index">
										<div class="btn-group">
											<a  class="btn btn-default" data-toggle="tooltip" title="Восстановить" @click.prevent="restoreOffer(offer.id)">
												<i class="fa fa-reply"></i>
											</a>
											<a  class="btn btn-default" data-toggle="tooltip" title="Удалить насовсем" @click.prevent="deleteOffer(offer.id)">
												<i class="fa fa-remove"></i>
											</a>
										</div>
									</div>

								</td>
								{{--<td>--}}
									{{--<a href="#" @click.prevent="restoreOffer(offer.id)" class='button-remove button-restore'>Восстановить</a>&nbsp;--}}
									{{--<a href="#" @click.prevent="deleteOffer(offer.id)" class='button-remove button-remove'>Удалить</a>--}}
								{{--</td>--}}
							</tr>
						</template>
					</tbody>
				</table>
			</div>
			<pagination v-if="pagination.last_page > 1" :pagination="pagination" :offset="5" @paginate="fetchProducts()"></pagination>
		</div>
	</div>
</div>
@endsection