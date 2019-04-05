@extends('layouts.app')

@section('content')

<div class="error-page">
    <h2 class="headline text-yellow"> 404</h2>

    <div class="error-content">
      <h3><i class="fa fa-warning text-yellow"></i> Страница не найдена.</h3>

      <p>
        Мы не смогли найти запрашиваемую страницу.
        Вы можете перейти на <a href="{{url('/')}}">Главную</a> страницу.
      </p>

    </div>
    <!-- /.error-content -->
  </div>

@endsection
