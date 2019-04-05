@extends('layouts.app') 
@section('styles') 
    <link rel="stylesheet" href="{{asset('/css/pages/settings/integration.css')}}"> 
@stop 
@section('scripts')
    <script src="{{asset('js/pages/settings/integration.min.js')}}"></script>
@stop 
@section('content')
<div class="row" id="app">
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Интеграция с CRM</h3>
            </div>
            <div class="callout callout-warning text-center col-md-12">
                <p>Интеграция находится на стадии Бета - тестирования</p>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {{-- <label></label> --}}
                    <select class="form-control" :disabled="disabledSelect" @change="selectIntegration" :value="integrationData.crm">
                        <option value="">Выберите интеграцию</option>
                        @foreach($system_crm as $sc)
                            <option value="{{$sc->type}}">{{$sc->name}}</option>
                        @endforeach
                    </select>
                </div>
                <template v-if="megaplan">
                    <div class="form-group"
                        :class="{'has-error': errors.host}">
                        <div class="input-group">
                            <span class="input-group-addon">https://</span>
                                <input type="text" class="form-control" placeholder="Введите host Megaplan (your.megaplan.ru)" 
                                :value='integrationData.host' 
                                @input="updateHost">
                            {{-- <span class="help-block" v-if="errors.host">Введите host</span> --}}
                        </div>
                    </div>
                    {{-- <div class="form-group"> --}}
                        <button type="submit" 
                            v-if="!integrationData.uuid" 
                            class="btn btn-primary"
                            @click="targetLink">Установить</button>
                    {{-- </div> --}}
                </template>
                <template v-if="amocrm">
                    <div class="form-group"
                        :class="{'has-error': errors.host}">
                        <div class="input-group">
                            <span class="input-group-addon">https://</span>
                            <input type="text" class="form-control" placeholder="Введите host AmoCrm (your.amocrm.ru)" 
                                :value='integrationData.host' 
                                @input="updateHost">
                        </div>
                    </div>
                    
                    <div class="form-group" :class="{'has-error': errors.login}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-at"></i></span>
                            <input type="text" class="form-control" placeholder="Введите Ваш логин" 
                                :value='integrationData.login' 
                                @input="updateLogin">
                        </div>
                    </div>
                    <div class="form-group" :class="{'has-error': errors.token}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input type="text" class="form-control" placeholder="Введите Ваш API ключ" 
                                :value='integrationData.token' 
                                @input="updateToken">
                        </div>
                    </div>
                    {{-- <div class="box-footer">
                        <button type="submit" class="btn btn-primary" @click="targetLink">Установить</button>
                    </div> --}}
                </template>
                {{--Bitrix24--}}
                <template v-if="bitrix24">
                    <div class="form-group"
                         :class="{'has-error': errors.host}">
                        <div class="input-group">
                            <span class="input-group-addon">https://</span>
                            <input type="text" class="form-control"
                                   placeholder="Введите host Bitrix24 (your.bitrix24.ru)"
                                   :disabled="disabledHost"
                                   :value='integrationData.host'
                                   @input="updateHost">
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" 
                            v-if="integrationData.host"
                            class="btn btn-primary" 
                            @click="targetLink" 
                            :disabled="disabledHost">
                            Установить
                        </button>
                    </div>
                </template>
                {{--Bitrix24--}}
                <div class="form-group" v-if="megaplan&&integrationData.uuid">
                    <label>Api Token</label>
                    <input type="text" class="form-control" placeholder="" :value="integrationData.token" @keyup="integrationData.token = $event.target.value">
                    <label>UUID</label>
                    <input type="text" class="form-control" placeholder="" disabled="" :value="integrationData.uuid">
                </div>
                <template v-if="integrationData.crm">
                    <button type="submit" 
                        class="btn btn-primary" 
                        @click="saveIntegration" 
                        v-show="showSaveBtn && integrationData.crm != 'bitrix24'"
                        {{-- v-show="integrationData.uuid" --}}>
                        Сохранить
                    </button>
                    <button type="submit" class="btn btn-danger pull-right" v-if="integrationData.id" @click="deleteIntegration">Удалить</button>
                </template>
            </div>
        </div>

        <template v-if="amocrm">
            <div class="box collapsed-box">
                    <div class="box-header">
                        <h3 class="box-title">Виджет</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display: none;">
                        <h4>Для интеграции необходимо установить виджет КП10</h4>
                        <ol>
                            <li>Свяжитесь с нашим специалистом (support@kp10.pro) по поводу установки виджета</li>
                        </ol>
                        {{-- <ol>
                            <li>Перейдите на страницу <a :href="'https://'+integrationData.host+'/settings/widgets/'" target="_blank">Настроек Интеграции</a></li>
                            <li>В поиске введите <b>КП10</b></li>
                            <li>Установите виджет согласившись на обработку данных аккаунта amoCRM</li>
                        </ol> --}}
                    </div>
            </div>
            {{-- <div class="box collapsed-box">
                <div class="box-header">
                    <h3 class="box-title">Web Hooks</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body" style="display: none;">
                    <h4>Для того чтобы мы могли отображать актуальную информацию из amoCrm, необходимо создать WebHook</h4>
                    <ol>
                        <li>Перейдите на страницу <a :href="'https://'+integrationData.host+'/settings/widgets/'" target="_blank">Настроек Интеграции</a></li>
                        <li>В начале страницы в блоке Собственные интеграции нажмите на кнопку "WEB HOOKS"</li>
                        <li>После чего нажмите на кнопку "Добавить хук" и введите в поле "Укажите URL сайта" адрес: <b>{{env('APP_URL').'/integration/amocrm/events'}}</b></li>
                        <li>
                            В выпадающем списке выберите следующие события
                            <ul>
                                <li>Изменить сделку</li>
                                <li>Изменить контакт</li>
                                <li>Изменить компанию</li>
                                <li>Изменить покупателя</li>
                                <li>Удалить сделку</li>
                                <li>Удалить контакт</li>
                                <li>Удалить компанию</li>
                                <li>Удалить покупателя</li>
                                <li>Восстановить сделку</li>
                                <li>Восстановить контакт</li>
                                <li>Восстановить компанию</li>
                                <li>Смена статуса сделки</li>
                            </ul>
                        </li>
                        <li>И подтвердите изменения нажав на кнопку "Сохранить"</li>
                    </ol>
                </div>
            </div> --}}
        </template>
    </div>
    {{-- Megaplan --}}
    <div class="col-md-4">
        <template v-if="integrationData.uuid">
            {{-- Megaplan --}}
            <div class="box" v-if="megaplan">
                <div class="box-header with-border">
                    <h3 class="box-title">Настройка полей сделки</h3>
                    <div class="pull-right">
                        <button type="button" class="btn btn-default" @click="showField" :disabled="showFieldDisable">
                            <i class="fa fa-plus" v-if="showPlus"></i>
                            <i class="fa fa-minus" v-if="showMinus"></i>
                            @{{addFieldBtnName}}
                        </button>
                    </div>
                </div>
                {{-- <div class="box-body" v-if="!program.length"> --}}

                    {{-- </div>  --}}
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <span>Схема сделки</span>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <span>Поле сделки</span>
                                </div>
                            </div>
                            {{-- <div class="col-xs-3">
                                <div class="form-group">
                                    <span>Тип поля</span>
                                </div>
                            </div>    --}}
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <span></span>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="program.length">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <select class="form-control" @change="selectProgram" :disabled="disabledField">
                                        <option value="-1">Выберите поле</option>
                                        <option :value="field.val" v-for="field in program" :key="field.id"
                                                :data-contentType="field.contentType">@{{ field.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <select class="form-control" @change="selectField" :disabled="disabledField">
                                        <option value="-1">Выберите поле</option>
                                        <option :value="field.val" v-for="field in fields" :key="field.id"
                                                :data-contentType="field.contentType">@{{ field.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-xs-3">
                                <div class="form-group">
                                    <select class="form-control" @change="selectType" :disabled="disabledFieldType">
                                        <option value="-1">Выберите тип поля</option>
                                        <option :value="fieldType.val" v-for="fieldType in fieldType" :key="fieldType.id">@{{ fieldType.text }}</option>
                                    </select>
                                </div>
                            </div> --}}
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <button type="button" class="btn btn-default" :disabled="disabledAddBtn"
                                            @click="addRow"><i class="fa fa-plus"></i> Добавить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <template v-for="(field, index) in storedFields">
                            <div class="row" :key="index">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <input type='text' disabled='disabled' class='form-control input-sm'
                                               :value="field.program_name">
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <input type='text' disabled='disabled' class='form-control input-sm'
                                               :value="field.field_name">
                                    </div>
                                </div>
                                {{-- <div class="col-xs-3">
                                    <div class="form-group">
                                        <input type='text' disabled='disabled' class='form-control input-sm' :value="field.type_name">
                                    </div>
                                </div>    --}}
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-default btn-sm"
                                                @click="deleteRow(index, field)"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            {{-- amoCRM --}}
            <template v-if="amocrm&&integrationData.id">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Настройка полей сделки</h3>
                        <div class="pull-right">
                            <button type="button" class="btn btn-default" @click="showField"
                                    :disabled="showFieldDisable">
                                <i class="fa fa-plus" v-if="showPlus"></i>
                                <i class="fa fa-minus" v-if="showMinus"></i>
                                @{{addFieldBtnName}}
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <template v-if="fields.length||storedFields.length">
                            <div class="row">
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <span>Поле сделки</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Fields from api --}}
                            <div class="row" v-if="fields.length">
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectField">
                                            <option value="-1">Выберите поле</option>
                                            <option v-for="field in fields" :key="field.id" :value="field.id">@{{
                                                field.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-default" :disabled="disabledAddBtn"
                                                @click="addRow"><i class="fa fa-plus"></i> Добавить
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-for="(field, index) in storedFields" :key="index">
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <input type='text' disabled='disabled' class='form-control input-sm'
                                               :value="field.amocrm_field_name">
                                    </div>
                                </div>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-default btn-sm"
                                                @click="deleteRow(index, field)"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            {{-- Bitrix24 --}}
            <template v-if="integrationData.accessToken">
                {{-- Bitrix24 --}}
                <div class="box" v-if="bitrix24">
                    <div class="box-header">
                        <h3 class="box-title">Настройка полей сделки</h3>
                        <div class="pull-right">
                            <button type="button" class="btn btn-default" @click="showField"
                                    :disabled="showFieldDisable">
                                <i class="fa fa-plus" v-if="showPlus"></i>
                                <i class="fa fa-minus" v-if="showMinus"></i>
                                @{{addFieldBtnName}}
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <template v-if="fields.length||storedFields.length">
                            <div class="row">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <span>Поле сделки</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Fields from api --}}
                            <div class="row" v-if="fields.length">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <select class="form-control" @change="selectField">
                                            <option value="-1">Выберите поле</option>
                                            <option v-for="field in fields" :key="field.id" :value="field.id">@{{
                                                field.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-default" :disabled="disabledAddBtn"
                                                @click="addRow"><i class="fa fa-plus"></i> Добавить
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-for="(field, index) in storedFields" :key="index">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <input type='text' disabled='disabled' class='form-control input-sm'
                                               :value="field.bitrix24_field_name">
                                    </div>
                                </div>
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-default btn-sm"
                                                @click="deleteRow(index, field)"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
@stop