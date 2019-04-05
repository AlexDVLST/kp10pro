@extends('layouts.app')

@section('title', 'Рабочий стол')
@section('description', 'Рабочий стол пользователя')

@section('content')

	{{-- Show tour --}}
	@include('layouts.pages.home.tour-intro')


@endsection
