@extends('layouts.app')
@section('title', $page->title)
@section('description', $page->description)
{{-- Show tour --}}
{{-- @include('tour') --}}

@section('styles')
	<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
	<link rel="stylesheet" href="{{asset('/css/pages/settings/currencies.css')}}">
@stop
@section('scripts')
	<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
	<script src="{{asset('js/pages/settings/currencies.min.js')}}"></script>
@stop
@section('content')
	<div class="currencies-list-content" id="app">
		<div class="box">
			<div class="box-header">
				<h3 class="box-title">Список валют</h3>
				<div class="box-tools">
					<div class="input-group input-group-sm search">
						<div class="input-group input-group-sm search">
						<div class="input-group-btn">
							<a href="{{url('/settings/currencies/create')}}" class="btn btn-default">
								<i class="fa fa-plus"></i>
							</a>
						</div>
						</div>
					</div>
				</div>
			</div>
			<div class="box-body">
				<div class="table-responsive">
					<currencies v-bind="currencyData"></currencies>
				</div>
			</div>
		</div>
	</div>
@endsection
