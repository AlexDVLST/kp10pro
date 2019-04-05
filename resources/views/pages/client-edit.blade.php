@extends('layouts.app') 
@section('title', $page->title) 
@section('description', $page->description) 
@section('styles') 
<!-- DaData -->
<link rel="stylesheet" href="{{asset('/plugins/dadata/suggestions.min.css')}}"> 
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('/plugins/select2/dist/css/select2.min.css')}}"> 
<link rel="stylesheet" href="{{asset('/css/pages/client.css')}}"> 
@stop 
@section('scripts') 
<!-- DaData script -->
<script src="{{asset('/plugins/dadata/jquery.suggestions.min.js')}}"></script>
<!-- Select2 script -->
<script src="{{asset('/plugins/select2/dist/js/select2.full.min.js')}}"></script> 
<script src="{{asset('/plugins/select2/dist/js/i18n/ru.js')}}"></script>
<!-- Page script -->
<script src="{{asset('js/pages/client-edit.min.js')}}"></script> 
@stop 
@section('content')
<div id="app">
    <client v-bind:client-id="clientId"></client>
</div>
@stop