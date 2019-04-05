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
	
	<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">

	<link rel="stylesheet" href="{{asset('css/app.css')}}">
	<!-- Ionicons -->
	{{--
	<link rel="stylesheet" href="{{asset('bower_components/Ionicons/css/ionicons.min.css')}}"> --}}
	<!-- Pace style -->
	<link rel="stylesheet" href="{{asset('plugins/pace/pace.min.css')}}">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- Custom styles -->
	@yield('styles')

	<!-- Theme style -->
	<link rel="stylesheet" href="{{asset('plugins/admin-lte/dist/css/AdminLTE.min.css')}}">
	<!-- AdminLTE Skins. We have chosen the skin-blue for this starter
		  page. However, you can choose any other skin. Make sure you
		  apply the skin class to the body tag so the changes take effect. -->
	<link rel="stylesheet" href="{{asset('plugins/admin-lte/dist/css/skins/skin-blue.min.css')}}">
@if(!App::environment('local'))
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
	<!-- BEGIN JIVOSITE CODE {literal} -->
	<script type='text/javascript'>
		(function(){ var widget_id = '6YpjdUQCxN';var d=document;var w=window;function l(){var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true;s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
		function jivo_onLoadCallback(){
			@auth
			jivo_api.setContactInfo({
				"name": "{{Auth::user()->surname.' '.Auth::user()->name}}",
				"email": "{{Auth::user()->admin->email}}",
				"description": `Имя сотрудника: {{Auth::user()->surname.' '.Auth::user()->name}} 
					Емейл сотрудника: {{Auth::user()->email}}
					Компания: {{Auth::user()->admin->email}}`
			}); 
			@endauth
		}
	</script>
	<!-- {/literal} END JIVOSITE CODE -->
@endif
</head>
<body class="hold-transition skin-blue sidebar-mini">
	<div class="wrapper">
		<!-- Main Header -->
		@include('layouts.header')
		<!-- Left side column. contains the logo and sidebar -->
		@include('layouts.left-side')
			
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<section class="content-header">
					{{-- <h1>
					@yield('title')
					<small>@yield('description')</small>
				</h1>
				
				<ol class="breadcrumb">
					<li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
					<li class="active">Here</li>
				</ol>--}}
			</section>

			<!-- Main content -->
			<section class="content container-fluid">
				@yield('content')
			</section>
		</div>
		<!-- /.content-wrapper -->

		<!-- Main Footer -->
		{{-- <footer class="main-footer">
			<!-- To the right -->
			<div class="pull-right hidden-xs">
				Anything you want
			</div>
			<!-- Default to the left -->
			<strong>Copyright &copy; 2017 <a href="#">Company</a>.</strong> All rights reserved.
		</footer>
		-- }}
		<!-- Control Sidebar -->
		{{-- @include('layouts.control-sidebar') --}}

	</div>
	<!-- ./wrapper -->
	@auth
	<script>
	// Global variables
	window.laravel = <?=json_encode([
		'user' => [ 'id' => Auth::user()->id, 'email' => Auth::user()->email, 'permissions' => Auth::user()->getAllPermissions()->pluck('name'), 'roles' => Auth::user()->roles->pluck('name') ],
		'dadata' => [
			'apiKey' => env('DADATA_API_KEY')
		]
	])?>
	</script>
	@endauth
	<!-- REQUIRED JS SCRIPTS -->
	<script src="{{asset('/js/app.js')}}"></script>
	<!-- PACE -->
	<script src="{{asset('plugins/pace/pace.min.js')}}"></script>
	<!-- SlimScroll -->
	<script src="{{asset('plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
	<!-- FastClick -->
	<script src="{{asset('plugins/fastclick/lib/fastclick.js')}}"></script>
	<!-- AdminLTE App -->
	<script src="{{asset('plugins/admin-lte/dist/js/adminlte.min.js')}}"></script>

	@yield('scripts')
	@include('layouts.modals-dialog')
	<!-- Notifications -->
	@include('layouts.notifications')

</body>

</html>