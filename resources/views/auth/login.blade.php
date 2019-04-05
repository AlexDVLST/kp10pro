<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Laravel') }}</title>

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="{{asset('css/app.css')}}">
	<!-- Theme style -->
	<link rel="stylesheet" href="{{asset('plugins/admin-lte/dist/css/AdminLTE.min.css')}}">
	<link rel="stylesheet" href="{{asset('plugins/admin-lte/dist/css/skins/skin-blue.min.css')}}">
	<!-- iCheck -->
	<link rel="stylesheet" href="{{asset('plugins/iCheck/square/blue.css')}}">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>
<body class="hold-transition login-page">

<div class="login-box">
	<div class="login-logo">
		<a href="/"><b>КП</b>10</a>
	</div>
	<!-- /.login-logo -->
	<div class="login-box-body">
		<p class="login-box-msg">Войдите, чтобы начать сеанс</p>

		@if (session('license'))
			<div class="alert alert-warning">
				{{ session('license') }}
			</div>
		@endif

		<form method="POST" action="{{ route('login') }}">
			{{ csrf_field() }}
			@if($account)
				<input type="hidden" class="form-control" placeholder="Domain" name="domain"
				       value="{{ $account }}">
			@else
				<div class="form-group has-feedback{{ $errors->has('domain') ? ' has-error' : '' }}">
					<input type="text" class="form-control" maxlength="100" placeholder="Domain" name="domain"
					       value="{{ old('domain') }}">
					<span class="glyphicon glyphicon-globe form-control-feedback"></span>
					@if ($errors->has('domain'))
						<span class="help-block">
                            <strong>{{ $errors->first('domain') }}</strong>
                        </span>
					@endif
				</div>
			@endif
			<div class="form-group has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
				<input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
				<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
				@if ($errors->has('email'))
					<span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
				@endif
			</div>
			<div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
				<input type="password" class="form-control" placeholder="Password" name="password">
				<span class="glyphicon glyphicon-lock form-control-feedback"></span>
				@if ($errors->has('password'))
					<span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
				@endif
			</div>
			<div class="row">
				<div class="col-xs-8">
					<input type="hidden" name="remember" value="true">
					{{-- <div class="checkbox icheck">
						<label>
							<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Запомнить
						</label>
					</div> --}}
				</div>
				<!-- /.col -->
				<div class="col-xs-4">
					<button type="submit" class="btn btn-primary btn-block btn-flat">Войти</button>
				</div>
				<!-- /.col -->
			</div>
		</form>

		<a href="{{ route('password.request') }}">Забыли пароль?</a><br>
		{{-- @if(!$account)
			<a href="{{ route('register') }}" class="text-center">Register a new membership</a>
		@endif --}}
	</div>
	<!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<script src="{{asset('/js/app.js')}}"></script>

<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<script>
	$(function () {
		$('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
	});
</script>
</body>
</html>