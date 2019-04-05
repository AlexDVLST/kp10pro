@extends('layouts.app') 
@section('styles') 
<link rel="stylesheet" href="{{asset('/css/pages/clients.css')}}"> 
@stop 
@section('scripts')
<script src="{{asset('js/pages/clients.min.js')}}"></script>
@stop 
@section('content')

<div class="box" id="app">
    <div class="box-header">
        <h3 class="box-title">{{$page->title}}</h3>
        @can('create client')
        <div class="pull-right">
            <a href="#" class="btn btn-default" @click.prevent="$refs.client.showModal(0)">
                <i class="fa fa-user-plus"></i>
                Добавить
            </a>
        </div>
        @endcan
    </div>
    <div class="box-body">
        <template v-if="loaded&&clients.length>0">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>ФИО/Название</th>
                            <th>Ответственный</th>
                            <th>Тип</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template>
                            <tr v-for="(client, index) in clients" :key="client.id" @mouseover="showByIndex = index" @mouseout="showByIndex = null">
                                <td>@{{(pagination.current_page*pagination.per_page) + index + 1 - pagination.per_page}}</td>
                                <td>
                                    <a href="#" class="client-name" @click.prevent="$refs.client.showModal(client.id)">
                                        @{{client.displayName}} 
                                    </a>
                                <small v-if="client.companyId&&client.companyName">(<a href="#" @click.prevent="$refs.client.showModal(client.companyId)">@{{client.companyName}}</a>)</small>
                                </td>
                                <td><p v-for="(responsible, index) in client.responsible_relation">@{{responsibleName(responsible.user_id)}}</p></td>
                                <td>@{{client.typeName}} 
                                    <div class="functional-panel" v-show="showByIndex === index">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" title="Удалить" 
                                                @click.prevent="remove(client.id)" 
                                                v-can="{permission: ['delete client', 'delete-own client'], userId: client.user_id, responsibles: client.responsible_relation}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <pagination v-if="pagination.last_page > 1" :pagination="pagination" :offset="5" @paginate="fetch()"></pagination>
        </template>
        <template v-if="errorEmpty">
            <div class="alert alert-warning">
                <h4><i class="fa fa-warning"></i> Список клиентов пуст</h4>
                <p>Начните работу с добавления клиентов в систему</p>
            </div>
        </template>
        {{-- Modal client --}}
        <client ref="client" v-bind="clientId"></client>
    </div>
</div>

@stop