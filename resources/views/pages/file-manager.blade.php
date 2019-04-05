@extends('layouts.app')

@section('title', $page->title)
@section('description', $page->description)

@section('styles')
@stop
@section('scripts')
	<script src="{{asset('js/pages/file-manager.min.js')}}"></script>
@stop

@section('content')
	<div id="app">
		<file-manager></file-manager>
	</div>
@endsection