@extends('layouts.app') 
@section('title', $page->title) 
@section('description', $page->description) 
@section('styles')
<link rel="stylesheet" href="{{asset('/css/pages/settings/employee.css')}}">
@stop 
@section('scripts')
<!-- Page script -->
<script src="{{asset('js/pages/settings/employee-create.min.js')}}"></script>
@stop 
@section('content')
<div id="app">
    <employee :page-name="pageName" v-bind="employee"></employee>
</div>
@endsection