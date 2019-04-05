@extends('layouts.integration') 
@section('styles') 
    <link rel="stylesheet" href="{{asset('/css/pages/settings/integration.css')}}"> 
@stop 
@section('scripts')
    {{-- <script src="{{asset('js/pages/settings/integration.min.js')}}"></script> --}}
@stop 
@section('content')
@if($message == 'successfulIntegration')
    <div class="modal fade in" id="modal-default" style="display: block; padding-right: 15px;">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">{{-- 
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button> --}}
                <h4 class="modal-title">Сообщение от KP10</h4>
            </div>
            <div class="modal-body">
                <h4>Интеграция прошла успешно!</h4>
                <p>Можно закрыть страницу и продолжить работу с KP10</p>
                <p>Перейти в <a href="{{$account}}?auth=remote&type=megaplan&token={{$token}}&uid={{$uid}}" target="_blank">КП10</a></p>
            </div>
            {{-- 
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div> --}}
            </div>
            <!-- /.modal-content -->
        </div>
    <!-- /.modal-dialog -->
    </div>
@else
    <div class="modal fade in" id="modal-default" style="display: block; padding-right: 15px;">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Сообщение от KP10</h4>
            </div>
            <div class="modal-body">
                <h4>{!! $message !!}</h4>
            </div>
            </div>
        </div>
    </div>
@endif
@stop