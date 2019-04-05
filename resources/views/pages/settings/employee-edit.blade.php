@extends('layouts.app') 
@section('title', $page->title) 
@section('description', $page->description) 
@section('styles')
<link rel="stylesheet" href="{{asset('/css/pages/settings/employee.css')}}">
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}"> 
@stop 
@section('scripts')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<!-- Page script -->
<script src="{{asset('js/pages/settings/employee-edit.min.js')}}"></script>
@stop
@section('content')
    {{-- @if($user->can('view settings') || $user->id === $employee->id ) --}}
    @if($user->userCan('view settings') || $user->id === $employee->id )
         {{-- Check if employee exist --}} 
        @if($employee) 
        <div id="app">
            <employee :page-name="pageName" v-bind="employee"></employee>
        </div>
        @else
        <div class="alert alert-danger">
            <h4><i class="fa fa-ban"></i> Alert!</h4>
            Сотрудник не найден!
        </div>
        @endif
    @else
    <div class="alert alert-danger">
    <h4><i class="fa fa-ban"></i> {{__('messages.permission.denied')}}</h4>
    </div>
    @endif
@endsection