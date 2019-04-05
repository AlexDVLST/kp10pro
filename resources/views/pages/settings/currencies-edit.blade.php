@extends('layouts.app')

@section('title', 'Редактирование товара: '.$page->title)
@section('description','Товар '.$page->description)

{{-- Show tour --}}
{{-- @include('tour') --}}

@section('styles')

@stop

@section('scripts')
	<script src="{{asset('js/pages/settings/currencie-edit.min.js')}}"></script>
@stop

@section('content')
	<section class="content" id="currencie-edit-app">
		<div class="row">
			<!-- left column -->
			<div class="">
				<!-- general form elements -->
				<div class="box box-primary">
					@if($page_type == "edit")
						<div class="box-header with-border">
							<h4 class="box-title">Редактирование валюты</h4>
						</div>
					@endif
					<form role="form" id="add-currencie-form">
						<div class="box-body">
							<h3 id="h4_product_article">Название валюты:</h3>
							<input ref="currencieName" id="currencieName" class="form-control" type="text"
							       @if($currencie->name) value="{{$currencie->name}}"
							       @endif
							       placeholder="Название валюты">

							<p class="help-block">Например: Российский рубль</p>

							<h3 id="h4_product_article">Код валюты:</h3>

							<div class="input-group ">
								<div class="input-group-btn">
									<button type="button" class="btn btn-warning dropdown-toggle"
									        data-toggle="dropdown">Выбрать
										<span class="fa fa-caret-down"></span></button>
									<ul class="dropdown-menu">
										<li><a data-code="840" @click.prevent="currencieCodeSet" href="#">USD</a></li>
										<li><a data-code="978" @click.prevent="currencieCodeSet" href="#">EUR</a></li>
										<li><a data-code="643" @click.prevent="currencieCodeSet" href="#">RUB</a></li>
										<li><a data-code="980" @click.prevent="currencieCodeSet" href="#">UAH</a></li>
										<li><a data-code="826" @click.prevent="currencieCodeSet" href="#">GBP</a></li>
										<li><a data-code="392" @click.prevent="currencieCodeSet" href="#">JPY</a></li>
										<li><a data-code="756" @click.prevent="currencieCodeSet" href="#">CHF</a></li>
										<li><a data-code="156" @click.prevent="currencieCodeSet" href="#">CNY</a></li>
									</ul>
								</div>
								<input ref="currencieCode" id="currencieCode" class="form-control" type="text"
								       @if($currencie->code) value="{{$currencie->code}}"
								       @endif
								       placeholder="Код валюты">
							</div>
							<!-- <p class="help-block">Например: USD, EUR, RUB</p> -->

							<h3 id="h4_product_article">Курс:</h3>

							Синхронизировать курс: &nbsp;<input ref="currencieSync" type="checkbox"> &nbsp;

							<br><br>

							<input ref="currencieRate" id="currencieRate" class="form-control" type="text"
							       @if($currencie->rate) value="{{$currencie->rate}}"
							       @endif
							       placeholder="Курс">

							<h3 id="h4_product_article">Подпись:</h3>

							<input ref="currencieSign" id="currencieSign" class="form-control" type="text"
							       @if($currencie->sign) value="{{$currencie->sign}}"
							       @endif
							       placeholder="Подпись">

							<p class="help-block">Например: руб.</p>

							<br><br>

							<button data-pageType="{{$page_type}}"
							        @if($page_type == "edit") data-currencyId={{$currencie->id}} @endif type="button"
							        class="btn btn-primary"
							        @click="currencieSave">Сохранить
							</button>


						</div>
					</form>
				</div>
			</div>
		</div>
	</section>


	<br><br>

@endsection