@extends('layouts.app') 
@section('scripts')
<script src="{{asset('js/pages/admin/helps.min.js')}}"></script>
@stop 
@section('content')
<div id="app">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Помощь</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-success btn-sm" @click="showCreateModal">Добавить</button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body table-responsive no-padding">
            <template>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Название</th>
                            <th>Раздел</th>
                            <th>Видео</th>
                            <th>Внешняя ссылка (Подробнее)</th>
                            <th>Создатель</th>
                            <th>Редактор</th>
                            <th>Дата создания</th>
                            <th>Дата обновления</th>
                        </tr>
                    </thead>
                    <tbody>
                        <transition name="fade">
                            <tr v-for="(help, index) in helps" @mouseover="showByIndex = index" @mouseout="showByIndex = null">
                                <td>@{{help.id}}</td>
                                <td>@{{help.name}}</td>
                                <td>@{{help.section ? help.section.name: help.section_id}}</td>
                                <td>@{{help.video}}</td>
                                <td>@{{help.external_link}}</td>
                                <td>@{{help.creator ? help.creator.name + ' ' + help.creator.surname + ' (' + help.creator.domain + ')': help.creator_user_id}}</td>
                                <td>@{{help.editor ? help.editor.name + ' ' + help.editor.surname + ' (' + help.editor.domain + ')': help.editor_user_id}}</td>
                                <td>@{{help.created_at}}</td>
                                <td>
                                    <span>@{{help.updated_at}}</span>
                                    <div class="functional-panel" v-show="showByIndex === index">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-default btn-sm" data-toggle="tooltip" title="Редактировать"
                                                @click.prevent="editHelp(help, index)">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="#" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Удалить"
                                                @click.prevent="showConfirmDeleteModal(help, index)">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </transition>
                    </tbody>
                </table>
            </template>
        </div>
    </div>

    <div class="modal fade" ref="modalHelp">
        <div class="modal-dialog" :class="{'modal-disabled': modalAjax}">
            <div class="modal-content">
                <div class="modal-header"><h4>Создание видео инструкции</h4></div>
                <div class="modal-body">
                    <div class="form-group" :class="{'has-error': error.name}">
                        <label for="name">Название</label>
                        <input type="text" class="form-control" id="name" placeholder="Введите название" v-model="help.name">
                        <span class="help-block" v-if="error.name">Введите название</span>
                    </div>
                    <div class="form-group" :class="{'has-error': error.section}">
                        <label for="section">Раздел</label>
                        <select id="section" class="form-control" v-if="sections.length" v-model="help.sectionId">
                            <option value="-1">Выберите раздел</option>
                            <option v-for="(section, index) in sections" :key="index" :value="section.id">@{{section.name}}</option>
                        </select>
                        <span class="help-block" v-if="error.section">Выберите раздел</span>
                    </div>
                    <div class="form-group" :class="{'has-error': error.video}">
                        <label for="video">Видео</label>
                        <input type="text" class="form-control" id="video" placeholder="Ссылка формат: https://www.youtube.com/embed/КОДВИДЕО" v-model="help.video">
                        <span class="help-block" v-if="error.video">Введите ссылку формат: https://www.youtube.com/embed/КОДВИДЕО</span>
                    </div>
                    <div class="form-group">
                        <label for="external-link">Внешняя ссылка (Подробнее)</label>
                        <input type="text" class="form-control" id="external-link" placeholder="Ссылка на Tilda" v-model="help.externalLink">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default pull-left btn-cancel">Отмена</button>
                    <button type="button" class="btn btn-primary" @click="saveHelp">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" ref="modalConfirmDelete">
        <div class="modal-dialog" :class="{'modal-disabled': modalAjax}">
            <div class="modal-content">
                <div class="modal-header"><h4>Вы уверены что хотите удалить инструкцию <b>@{{help.name}}</b>?</h4></div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default pull-left btn-cancel">Отмена</button>
                    <button type="button" class="btn btn-primary" @click="removeHelp">Да</button>
                </div>
            </div>
        </div>
    </div>
</div>

@stop