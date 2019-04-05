@extends('layouts.app')
@section('styles')
    {{-- <link rel="stylesheet" href="{{asset('/css/pages/settings/employees.css')}}">  --}}
    <!-- iCheck -->
    <link rel="stylesheet" href="{{asset('plugins/iCheck/square/blue.css')}}">
@stop
@section('scripts')
    <!-- iCheck -->
    <script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
    <script src="{{asset('js/pages/settings/scenario.min.js')}}"></script>
@stop
@section('content')
    <div class="box" id="app">
        <div class="box-header">
            <h3 class="box-title">{{$page->description}}</h3>
            <div class="pull-right">
                <button class="btn btn-default"
                        @click="createScenario">
                    <i class="fa fa-plus" style="font-size: 10px; margin-right: 2px;"></i>
                    Добавить
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row" v-if="storedScenario.length">
                <div class="col-xs-1 col-sm-1 col-md-1">
                    <label>№</label>
                </div>
                <div class="col-xs-5 col-sm-5 col-md-5">
                    <label>Условие</label>
                </div>
                <div class="col-xs-5 col-sm-5 col-md-5">
                    <label>Действие</label>
                </div>
            </div>
            <template v-for="(scenario, index) in storedScenario">
                <div class="row" :key="index">
                    <div class="col-xs-1 col-sm-1 col-md-1">
                        <div class="form-group">
                            <span>@{{index+1}}</span>
                        </div>
                    </div>
                    <div class="col-xs-5 col-sm-5 col-md-5">
                        <div class="form-group">
                            <span>@{{ scenario.event_name + ' ' }}<b>@{{ scenario.additional_event  }}</b></span>
                        </div>
                    </div>
                    <div class="col-xs-5 col-sm-5 col-md-5">
                        <div class="form-group">
                            <span>@{{ scenario.action_name + ' ' }}<b>@{{ scenario.additional_action }}</b></span>
                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-1">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm"
                                    @click="deleteScenario(index, scenario)"><i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="!storedScenario.length">
                <div class="alert alert-warning">
                    <h4><i class="fa fa-warning"></i> Список сценариев пуст</h4>
                    <p>Начните работу с добавления нового сценария</p>
                </div>
            </template>
        </div>
        {{-- Modal --}}
        <div class="modal fade" id="modal-scenario" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content" v-bind:class="{'disabled': disIfChanged}">
                    <!-- modal-body -->
                    <div class="box-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <h4>Добавление нового сценария</h4>
                                </div>
                            </div>
                            <div class="row" v-show="eventsList.length">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectEvent">
                                            <option value="-1">Выберите условие</option>
                                            <option v-for="event in eventsList" :key="event.id" :value="event.id">
                                                @{{event.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-show="additionalEventsList.length">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectAdditionalEvent" :disabled="additionalSecondEventFlag">
                                            <option v-for="event in additionalEventsList" :key="event.id"
                                                    :value="event.id">
                                                @{{event.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-show="additionalSecondEventFlag">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectAdditionalSecondEvent">
                                            <option value="-1">Выберите статус</option>
                                            <option v-for="event in additionalSecondEventsList" :key="event.id"
                                                    :value="event.id">
                                                @{{event.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12" v-show="actionsList.length">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectAction">
                                            <option value="-1">Выберите действие</option>
                                            <option v-for="action in actionsList" :key="action.id" :value="action.id">
                                                @{{action.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12" v-if="additionalActionFlag">
                                    <div class="form-group">
                                        <textarea v-if="additionalActionType === 'input'" class="form-control" rows="3" style="resize:none;" @input="inputAdditionalAction" placeholder="Введите описание задачи/дела"></textarea>
                                        <select class="form-control" v-else @change="selectAdditionalAction" :disabled="additionalSecondActionFlag">
                                            <option v-for="action in additionalActionsList" :key="action.id" :value="action.id">
                                                @{{action.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-show="additionalSecondActionFlag">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectAdditionalSecondAction">
                                            <option value="-1">Выберите статус</option>
                                            <option v-for="event in additionalSecondActionsList" :key="event.id"
                                                    :value="event.id">
                                                @{{event.name}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-2 col-sm-2 col-md-2" v-show="fullConditions">
                                    <button class="btn btn-default" title="Сохранить" @click="saveNewScenario"
                                            :disabled="disabledSaveBtn">Добавить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection