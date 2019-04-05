@extends('layouts.app') 
@section('styles')
{{-- <link rel="stylesheet" href="{{asset('/css/pages/settings/employees.css')}}">  --}}
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/square/blue.css')}}">
@stop 
@section('scripts')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('js/pages/settings/roles.min.js')}}"></script>
@stop
@section('content')
<div class="box" id="app">
    <div class="box-header">
        <h3 class="box-title">{{$page->description}}</h3>
        <div class="pull-right">
            <button class="btn btn-primary" 
                :disabled="isSaveEnabled"
                @click="store" >Сохранить
                {{-- <i class="fa fa-circle-o-notch fa-spin"></i> --}}
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <template>
                            <th v-for="(role, index) in roles" :key="index">
                                <a v-if="role.name!='user'" :href="'/settings/role/'+role.id+'/edit'" data-toggle="tooltip" title="Настройка прав доступа">@{{role.translation_relation.translation}}</a>
                                <template v-else>
                                    @{{role.translation_relation.translation}}
                                </template>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template>
                        <tr v-for="(employee, index) in employees" :key="index">
                            <td>
                                <img :src="employee.avatarUrl" width="40px"> 
                                <a :href="'/settings/employee/'+employee.id+'/edit'">@{{employee.displayName}}</a>    
                            </td>
                            <template>
                                <td v-for="(role, index) in roles" :key="index">
                                    <input type="checkbox" :checked="hasRole(employee,role.name)" :data-employee-id="employee.id" :data-role-name="role.name" :disabled="employee.isAdmin">
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        {{-- @endif --}}
    </div>
</div>
@endsection