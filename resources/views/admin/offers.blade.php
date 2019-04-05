@extends('layouts.app') 
@section('scripts')
<script src="{{asset('js/pages/admin/offers.min.js')}}"></script>
@stop 
@section('content')
<div class="box" id="app" v-cloak>
    <div class="box-header">
        <h3 class="box-title">Базовые шаблоны</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
            <div class="callout callout-warning">
                <h4>Важливо!</h4>
                <p>Перевіряти чи сгенерувався index.html для шаблону котрий оновлюємо. url таблиці offers повинен бути відповідним шаблону (напр. base)</p>
            </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Версия</th>
                    <th>Описание</th>
                    <th>Дата обновления шаблона</th>
                    <th>Статус обновления</th>
                    <th>Обновление на сервере</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(offer, index) in offers" :key="index">
                    <td>@{{offer.id}}</td>
                    <td>@{{offer.offer_name}}</td>
                    <td>@{{offer.template.version}}</td>
                    <td>@{{offer.template.description}}</td>
                    <td>@{{offer.updated_at}}</td>
                    <td>
                        <span class="badge" :class="{'bg-grey': uploadingId!=offer.id, 'bg-yellow': uploadingId==offer.id}">
                            <template v-if="uploadingId!=offer.id">Ожидание</template>
                            <template v-else>Обновление</template>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-default" @click="upload(offer.id)" :disabled="uploadingId>0">Обновить</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>




@stop