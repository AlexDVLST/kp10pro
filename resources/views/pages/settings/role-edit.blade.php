@extends('layouts.app') 
@section('styles')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/square/blue.css')}}"> 
@stop 
@section('scripts')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('js/pages/settings/role-edit.min.js')}}"></script>
@stop 
@section('content')
<div class="box" id="app">
    <div class="box-header">
        <h3 class="box-title">Настройка прав доступа для роли "@{{roleName}}"</h3>
        <div class="pull-right">
            <button class="btn btn-primary" :disabled="isSaveEnabled" @click="store">Сохранить
                {{-- <i class="fa fa-circle-o-notch fa-spin"></i> --}}
            </button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Страница</th>
                    <th>Права</th>
                    <th>Доступ</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="permission in permissions">
                    <tr>
                        <td colspan="3"><h4>@{{permission.name}}</h4></td>
                    </tr>
                    <tr v-for="perm in permission.permissions">
                        <td></td>
                        <td>@{{perm.translation}}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default" :class="{active: button.active}" 
                                    v-for="button in perm.buttons"
                                    :data-name="button.name"
                                    @click="changeUpdate($event, button)">@{{button.label}}</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
@endsection