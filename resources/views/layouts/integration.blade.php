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
</head>
<body class="hold-transition skin-blue sidebar-mini">
	@yield('content')
	
	<!-- ./wrapper -->
	@auth
	<script>
		// Global variables
	window.laravel = <?=json_encode([
		'user' => [ 'id' => auth()->user()->id, 'permissions' => auth()->user()->getAllPermissions()->pluck('name') ],
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

</body>

</html>