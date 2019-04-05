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
    <!-- Yandex.Metrika counter -->
	<script type="text/javascript">
		(function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter50570539 = new Ya.Metrika2({
                    id:50570539,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/tag.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks2");
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/50570539" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

</head>
<body class="hold-transition login-page">

    <div class="register-box">
        <div class="register-logo">
            <a href="/"><b>КП</b>10</a>
        </div>

        <div class="register-box-body">
            <p class="login-box-msg">Регистрация нового пользователя</p>

            <form method="POST" action="{{ route('register') }}">
                {{ csrf_field() }}

                {{-- <div class="form-group has-feedback{{ $errors->has('domain') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" maxlength="100" placeholder="Домен" name="domain"
                           value="{{ old('domain') }}">
                    <span class="glyphicon glyphicon-globe form-control-feedback"></span>
                    @if ($errors->has('domain'))
                        <span class="help-block">
                            <strong>{{ $errors->first('domain') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group has-feedback{{ $errors->has('') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" placeholder="Фамилия" name="surname" value="{{ old('surname') }}">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    @if ($errors->has('surname'))
                        <span class="help-block">
                            <strong>{{ $errors->first('surname') }}</strong>
                        </span>
                    @endif
                </div> --}}
                <div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" placeholder="Имя" name="name" value="{{ old('name') }}">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    @if ($errors->has('name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
                {{-- <div class="form-group has-feedback{{ $errors->has('middle-name') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" placeholder="Отчество" name="middle-name" value="{{ old('middle-name') }}">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    @if ($errors->has('middle-name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('middle-name') }}</strong>
                        </span>
                    @endif
                </div> --}}
                <div class="form-group has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group has-feedback{{ $errors->has('phone') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" placeholder="Телефон" name="phone" value="{{ old('phone') }}">
                    <span class="glyphicon glyphicon-phone form-control-feedback"></span>
                    @if ($errors->has('phone'))
                        <span class="help-block">
                            <strong>{{ $errors->first('phone') }}</strong>
                        </span>
                    @endif
                </div>
                {{-- <div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
                    <input type="password" class="form-control" placeholder="Пароль" name="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Повторите пароль" name="password_confirmation">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                </div> --}}
                <div class="row">
                    <div class="col-xs-7"></div>
                    <!-- /.col -->
                    <div class="col-xs-5">
                        <button type="submit" class="btn btn-primary btn-block btn-flat" onclick="yaCounter50570539.reachGoal('registr'); return true;">Регистрация</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            {{--<a href="{{route('login')}}" class="text-center">I already have a membership</a>--}}
        </div>
        <!-- /.form-box -->
    </div>
    <!-- /.register-box -->

    <script src="{{asset('/js/app.js')}}"></script>

</body>
</html>