@extends('layouts.app')
@section('title', $page->title)
@section('description', $page->description)
@section('styles') {{--
@if(\App\Models\UserMeta::getMeta('show-tour', true))
<link rel="stylesheet" href="{{asset('plugins/bootstrap-tour/css/bootstrap-tour.min.css')}}">
<link rel="stylesheet" href="{{asset('/css/bootstrap-tour.css')}}"> @endif --}}
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('/plugins/select2/dist/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<link rel="stylesheet" href="{{asset('/css/pages/offers.css')}}">
@stop
@section('scripts') {{-- @if(\App\Models\UserMeta::getMeta('show-tour', true))
<script src="{{asset('plugins/bootstrap-tour/js/bootstrap-tour.min.js')}}"></script>
<script src="{{asset('js/tour.min.js')}}"></script>
@endif --}}
<!-- iCheck -->
<script src="{{asset('plugins/select2/dist/js/select2.full.min.js')}}"></script>
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('js/pages/offers.min.js')}}"></script>
@stop
@section('content')
<div class="offers-list-table-content tour-step-1" id="app">
	<div class="box" :v-clock="loaded">
		<div class="box-header">
			<div class="btn-group" v-if="showPannel">
				<button type="button" class="btn btn-default btn-sm" @click="removeOffers"><i class="fa fa-trash-o"></i></button>
			</div>
			<h3 class="box-title" v-if="!showPannel">Список коммерческих предложений</h3>
			<div class="box-tools pull-right">
				<div class="input-group search">
					<input @keyup.prevent="search()" id="searchfield" placeholder="Поиск КП" class="form-control ">
					<div class="input-group-btn">
						<button 
							type="button" 
							class="btn btn-success"
							@click.prevent="createOffer"
							data-toggle="tooltip" 
							title="Создание нового КП"
						>Создать КП</button>
					</div>
				</div>
			</div>
		</div>
		<div class="box-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped display">
					<thead>
						<tr>
							<th>
								<input type="checkbox" @click="toogleSelection">
							</th>
							<th @click.prevent="sortby('id')">№ КП</th>
							<th @click.prevent="sortby('offer_name')">Название</th>
							<th>Клиент</th>
							<th>Варианты</th>
							<th>Менеджер</th>
							<th>Версия</th>
							<th @click.prevent="sortby('created_at')">Создано</th>
							<th @click.prevent="sortby('updated_at')">Изменено</th>
						</tr>
					</thead>
					<tbody id="table-body">
						<template>
							<tr v-for="(offer, index) in offers" :key="index" @mouseover="showByIndex = index" @mouseout="showByIndex = null">
								<td>
									<div v-if="(!offer.system && canDelete(offer))">
										<input type="checkbox" @click="updateCheckedOffers(offer)"  :checked="offer.check == 1">
									</div>
									{{--<a href="#" class="" v-if="!offer.system"><i class="fa fa-star text-yellow"></i></a> --}}
								</td>
								<td>
									<template v-if="!offer.system&&!offer.isUserTemplate&&offer.state&&offer.state.data">
										<div class="dropdown">
											<a class="label dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true"
												:class="'bg-'+offer.state.data.color">
												<span data-toggle="tooltip" title="Кликните для изменения статуса">
													@{{ offer.number }} . @{{offer.state.data.name}}
												</span>
											</a>
											<ul class="dropdown-menu">
												<li role="presentation" v-for="(state, index) in systemStates" :key="index">
													<a role="menuitem" tabindex="-1" href="#"
														@click.prevent="setState(offer, state)">
														<small class="label" :class="'bg-'+state.color">&nbsp;&nbsp;</small>
														@{{state.name}}
													</a>
												</li>
											</ul>
										</div>
									</template>
									<template v-else>
										{{-- <a href="#" class="label bg-gray" data-toggle="tooltip" title="Базовый шаблон коммерческого предложения" --}}
											{{-- @click.prevent="createTemplate(offer)" --}}
											{{-- v-if="canCreate(offer)" --}}{{-- > --}}
											{{-- Создать КП --}}
											<span class="text-green">Шаблон</span>
										{{-- </a> --}}
									</template>
								</td>
								<td>
									<a :href="'/editor/'+ offer.id" class="offer-name" target="_blank">@{{ offer.offer_name }}</a>
								</td>
								{{-- Клиент --}}
								<td>
									<template v-if="!offer.system && offer.client_relation && offer.client_relation.client">
										@{{offer.client_relation.client.displayName}}
										<br>
										<small v-if="!offer.system && offer.contact_person_relation && offer.contact_person_relation.client">
											(@{{offer.contact_person_relation.client.displayName}})
										</small>
										{{-- <a :href="'/client/'+offer.client_relation.client.id+'/edit'" target="_blank" class="client-name">
											@{{offer.client_relation.client.displayName}}
										</a><br>
										<small v-if="!offer.system && offer.contact_person_relation && offer.contact_person_relation.client">
											<a :href="'/client/'+offer.contact_person_relation.client.id+'/edit'" target="_blank" class="client-name">
												(@{{offer.contact_person_relation.client.displayName}})
											</a>
										</small> --}}
									</template>
								</td>
								{{-- Варианты --}}
								<td>
									<div class="btn-group" v-if="!offer.system && !offer.isUserTemplate">
										<template v-for="(variant, index) in offer.variants">
											<span data-toggle="tooltip" :title="variant.selected?'Выбран: '+variant.name:variant.name" :key="index"
												v-if="variant.active">
												<big v-if="variant.selected">
													<b>@{{numberFormat(variant.price)}} <i :class="offerCurrency(offer)"></i></b>
												</big>
												<template v-else>@{{numberFormat(variant.price)}} <i :class="offerCurrency(offer)"></i></template>
												<span v-if="showVariantDelimeter(offer, index)"> / </span>
											</span>
										</template>
									</div>
								</td>
								{{-- Менеджер --}}
								<td>
									@{{offer.employee.user?offer.employee.user.surname:''}}
									@{{offer.employee.user?offer.employee.user.name:''}}
								</td>
								{{-- Версия --}}
								<td>@{{offer.template.version}}</td>
								<td>
									<span>
										@{{ offer.created_at_formatted }}
									</span>
								</td>
								<td>
									<span>
										@{{ offer.updated_at_formatted }}
									</span>

									<div class="functional-panel" v-if="!offer.system" v-show="showByIndex === index">
										<div class="btn-group">
											{{-- <a href="#" class="btn btn-default" data-toggle="tooltip" title="Создать шаблон"
												@click.prevent="setUserTemplate(offer)">
												<i class="fa" :class="{'fa-star-o':!offer.isUserTemplate, 'fa-star':offer.isUserTemplate}"></i>
											</a> --}}
											<a v-if="offer.dealCardLink" :href="offer.dealCardLink" target="_blank" class="btn btn-default btn-sm" data-toggle="tooltip" title="Открыть в CRM">
												<i class="fa fa-external-link"></i>
											</a>
											<a :href="'/'+offer.url+'/excel'" class="btn btn-default btn-sm" data-toggle="tooltip" title="Скачать Excel" @click="downloadFile()">
												<i class="fa fa-file-excel-o"></i>
											</a>
											<a :href="'/'+offer.url+'/pdf'" class="btn btn-default btn-sm" data-toggle="tooltip" title="Скачать PDF" @click="downloadFile()">
												<i class="fa fa-file-pdf-o"></i>
											</a>
											<a :href="'/'+offer.url+'/pdf/full'" class="btn btn-default btn-sm" data-toggle="tooltip" title="Скачать расширенный PDF" @click="downloadFile()">
												<i class="fa fa-file-pdf-o"></i>
											</a>
											<a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" title="Скопировать ссылку"
												@click.prevent="copyToClipbord($event, '{{$user->domain.'.'.env('APP_DOMAIN')}}/'+offer.url)">
												<i class="fa fa-link"></i>
											</a>
											<a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" title="Удалить"
												@click.prevent="removeOffer(offer.id)"
												v-if="canDelete(offer)">
												<i class="fa fa-trash"></i>
											</a>
											<a href="#" class="btn btn-primary btn-sm" data-toggle="tooltip" title="Скопировать"
												@click.prevent="copyTemplate(offer)">
												<i class="fa fa-copy"></i>
											</a>
										</div>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
			<pagination v-if="pagination.last_page > 1" :pagination="pagination" :offset="5" @paginate="fetchProducts()"></pagination>
		</div>
	</div>

  	<!-- Modal Component -->
	<div class="modal fade" ref="modalCopy">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					<h4 class="modal-title">@{{modalCopy.title}}</h4>
				</div>
				<div class="modal-body">
					<div v-show="kpListShow">
						<label>Список шаблонов КП</label>
						<div class="form-group has-error">
							<select class="" data-live-search="true" name="kp-list" id="kp-list">
								<option value="-1">Выберите шаблон</option>
							</select>
						<span class="help-block" v-if="modalCopy.errors.noSelectOffer">@{{modalCopy.errors.noSelectOfferText}}</span>
						</div>
					</div>
					<div class="form-group" :class="{'has-error': modalCopy.errors.templateName}">
						<label>@{{modalCopy.templateTitle}}</label>
						<input type="text" class="form-control name" :value="modalCopy.templateName" @keyup="modalCopySetTemplateName">
					</div>
					<div class="form-group">
						<label>Создать шаблон</label><br>
						<input type="checkbox" class="is-template" @click="modalCopyIsTemplate">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left btn-cancel" data-dismiss="modal">Отмена</button>
					<button type="button" class="btn btn-primary btn-ok" @click="modalCopySave">Сохранить</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" ref="modalDownloadFile">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body text-center">
					<br>
						Идет формирование файла...
					<br><br>
				</div>
			</div>
		</div>
	</div>

</div>
@endsection