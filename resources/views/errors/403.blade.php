@extends('layouts.app')

@section('content')

<div class="error-page">
    <h2 class="headline text-aqua"> 403</h2>

    <div class="error-content">
      <h3><i class="fa fa-warning text-aqua"></i> Нет доступа к странице.</h3>

      <p>
        В доступе отказано, у вас нет необходимых прав. Обратитесь к руководителю для решения вопроса
      </p>

    </div>
    <!-- /.error-content -->
  </div>

@endsection
