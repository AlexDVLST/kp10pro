@extends('layouts.app') 
@section('title', $page->title) 
@section('description', $page->description) 
@section('styles')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<link rel="stylesheet" href="{{asset('/css/pages/settings/employees.css')}}"> 
@stop 
@section('scripts')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('js/pages/settings/employees.min.js')}}"></script>
@stop
@section('content')
<div class="box" id="app">
    <div class="box-header">
        <h3 class="box-title">Список сотрудников</h3>
        <div class="pull-right">
            @if(!$integration)
                <a href="/settings/employee/create" class="btn btn-default">
                    <i class="fa fa-user-plus"></i>
                    Добавить
                </a>
                {{-- @if($integration)
                    <a href="№" class="btn btn-default"
                        data-type="{{$integration->system_crm_id}}"
                        @click.prevent="importUser">
                        <i class="fa fa-user-plus"></i>
                        Импортировать
                    </a>
                @endif --}}
            @endif
            @if($integration)
                <div class="btn-group">
                    {{-- <button type="button"
                        class="btn btn-default">
                        <i class="fa fa-user-plus"></i>
                        Добавить
                    </button> --}}
                    <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-plus"></i>
                            Добавить
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="/settings/employee/create" class="">
                            {{-- <i class="fa fa-user-plus"></i> --}}
                            Создать    
                        </a></li>
                        <li><a href="№" class=""
                            data-type="{{$integration->system_crm_id}}"
                            @click.prevent="importUser">
                            {{-- <i class="fa fa-user-plus"></i> --}}
                            Импортировать из CRM
                        </a></li>
                    </ul>
                    </div>
                </div>
            @endif
            {{-- <div class="btn-group">
                <button type="button" class="btn btn-default">Добавить</button>
                <button type="button" class="btn btn-default">Импортировать из CRM</button>
            </div> --}}
        </div>
    </div>
    <div class="box-body">
        @if($employees && !$employees->isEmpty())
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Должность</th>
                        <th>Подпись в коммерческом предложении</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                    <tr>
                        <td>{{$index+1}}</td>
                        <td>
                            <img src="{{$employee->avatarRelation->url or ''}}" width="40px"> 
                            <a href="/settings/employee/{{$employee->id}}/edit">{{$employee->displayName}}</a>
                            @if($employee->isOnline())
                            <span class="pull-right badge bg-green">online</span>
                            @endif
                            @if($employee->trashed())
                            <span class="pull-right badge bg-orange">Заблокирован</span>
                            @endif
                        </td>
                        <td>{{$employee->email}}</td>
                        <td>{{$employee->phone}}</td>
                        <td>{{$employee->position}}</td>
                        <td title="{{$employee->signature}}">
                            <div class="signature">
                                {{$employee->signature}}
                            </div>
                        </td>
                        
                        <td>
                            {{-- @can('delete employee')
                                @if(!$employee->hasRole('user'))
                                    <a href="#" class="btn btn-default delete" data-id="{{$employee->id}}" data-name="{{$employee->displayName}}"><i class="fa fa-trash"></i></a>
                                @endif
                            @endcan --}}
                            @if($adminId != $employee->id)
                            <a href="#" class="btn btn-default delete" 
                                data-id="{{$employee->id}}" 
                                data-name="{{$employee->displayName}}"
                                @click.prevent="deleteEmployee">
                                <i class="fa fa-trash"
                                    data-id="{{$employee->id}}" 
                                    data-name="{{$employee->displayName}}">
                                </i>
                            </a>
                            @endif
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-warning">
            <h4><i class="icon fa fa-warning"></i> Список сотрудников пуст</h4>
            <p>Начните работу с добавления сотрудников в систему</p>
        </div>
        @endif
    </div>
    <div class="modal" id="popup-import-employee">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button> --}}
                    <button type="button" class="btn btn-primary pull-right" 
                        :class="{'disabled': isDisabled}"
                        @click="importBtn">
                        Импортировать
                    </button>
                    <h4 class="modal-title">Импорт пользователей из CRM</h4>
                    {{-- <div class="form-group">   --}}
                        <button 
                            data-toggle="tooltip" title="Показать/скрыть уже добавленых сотрудников"
                            type="button" 
                            class="btn btn-default btn-sm"
                            @click="showHideUser"
                            >@{{ btnName }}</button>
                    {{-- </div> --}}
                </div>
                <div class="modal-body"
                    :class="{'disabled': isDisabled}">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll"></th>
                                <th>ФИО</th>
                                <th>Должность</th>
                                <th>Email</th>
                                <th>Телефон</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(e, index) in employeeFromCrm" {{-- v-show="showExist" --}} data-toggle="tooltip" :title="alreadyAddedTitle(e.id)">
                                <td :class="{'bg-success': alreadyAdded(e.id)}">
                                    <input type="checkbox" @click="addEmployee(index)">
                                </td>
                                <td 
                                    :class="{'bg-success': alreadyAdded(e.id)}">@{{e.firstName}} @{{e.middleName}} @{{e.lastName}}
                                </td>
                                <td
                                    :class="{'bg-success': alreadyAdded(e.id)}"
                                    class="">@{{e.position}}
                                </td>
                                <td 
                                    :class="{'bg-success': alreadyAdded(e.id)}"
                                    class="">@{{e.email}}
                                </td>
                                <td 
                                    :class="{'bg-success': alreadyAdded(e.id)}"
                                    class="">@{{e.phone}}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left"
                        :class="{'disabled': isDisabled}" 
                        data-dismiss="modal">
                        Отмена
                    </button>
                    <button type="button" class="btn btn-primary" 
                        :class="{'disabled': isDisabled}"
                        @click="importBtn">
                        Импортировать
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="popup-delete-employee">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Удаление сотрудника</h4>
                </div>
                <div class="modal-body" v-if="noAssigned">
                    {{-- <div> --}}
                        <h5>За сотрудником <strong>@{{employeeName}}</strong> закреплены:</h5>
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-3">Коммерческих предложений:</div>
                                <div class="col-sm-2"><strong>@{{offersCnt}}</strong></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">Клиентов:</div>
                                <div class="col-sm-2"><strong>@{{clientsCnt}}</strong></div>
                            </div>
                        </div>
                        <h5>Передать другому сотруднику:</h5>
                        <select name="employee-to-replace" id="employee-to-replace">
                            <option value="-1">Выберите сотрудника</option>
                            <option v-for="(e, index) in employeeList" :value="e.id">@{{e.displayName}}</option>
                        </select>  
                    {{-- </div> --}}
                </div>
                <div class="modal-body" v-if="!noAssigned">
                    <h5>Удалить сотрудника <strong>@{{employeeName}}</strong>?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left"
                        data-dismiss="modal">
                        Отмена
                    </button>
                    <button type="button" 
                            class="btn btn-primary"
                            :class="{'disabled': isDisabled}"
                        @click="confirmDeletion">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection