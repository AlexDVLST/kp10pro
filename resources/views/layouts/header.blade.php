<header class="main-header">
	<!-- Logo -->
	<a href="/" class="logo">
		<!-- mini logo for sidebar mini 50x50 pixels -->
		<span class="logo-mini"><b>КП</b></span>
		<!-- logo for regular state and mobile devices -->
		<span class="logo-lg"><b>КП</b>10</span>
	</a>
	<!-- Header Navbar -->
	@auth
	<nav class="navbar navbar-static-top" role="navigation">
		<!-- Sidebar toggle button-->
		<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
			<span class="sr-only">Toggle navigation</span>
		</a>
		<!-- Navbar Right Menu -->
		<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<!-- Notifications: style can be found in dropdown.less -->
				{{-- <li class="dropdown notifications-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<i class="fa fa-bell-o"></i>
						<span class="label label-warning">10</span>
					</a>
					<ul class="dropdown-menu">
						<li class="header">You have 10 notifications</li>
						<li>
							<!-- inner menu: contains the actual data -->
							<ul class="menu">
								<li>
									<a href="#">
									  <i class="fa fa-users text-aqua"></i> 5 new members joined today
									</a>
								</li>
							</ul>
						</li>
						<li class="footer"><a href="#">View all</a></li>
					</ul>
				</li> --}}
				<!-- User Account Menu -->
				<li class="dropdown user user-menu">
					<!-- Menu Toggle Button -->
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<!-- The user image in the navbar-->
						<img src="{{Auth::user()->avatarUrl}}" class="user-image" alt="User Image">
						<!-- hidden-xs hides the username on small devices so only the image appears. -->
						<span class="hidden-xs">{{Auth::user()->name}}</span>
					</a>
					<ul class="dropdown-menu">
						<!-- The user image in the menu -->
						<li class="user-header">
							<img src="{{Auth::user()->avatarUrl}}" class="img-circle" alt="User Image">
							<!-- class="img-circle" -->

							<p>
								@auth {{Auth::user()->name}} - {{Auth::user()->position}}
								<small>Пользователь с {{Auth::user()->created_at->formatLocalized('%B %Y')}}</small> @endauth
							</p>
						</li>
						<!-- Menu Body -- >
						<li class="user-body">
							<div class="row">
								<div class="col-xs-4 text-center">
									<a href="#">Followers</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Sales</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Friends</a>
								</div>
							</div>
							<!-- /.row --
						</li -->
						<!-- Menu Footer-->
						<li class="user-footer">
							<div class="pull-left">
								<a href="{{url('/settings/employee/'.Auth::user()->id.'/edit')}}" class="btn btn-default btn-flat">Профиль</a>
							</div>
							<div class="pull-right">
								<a href="{{url('logout')}}" class="btn btn-default btn-flat" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">Выход</a>

								<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
									{{ csrf_field() }}
								</form>
							</div>
						</li>
					</ul>
				</li>
				<!-- Control Sidebar Toggle Button -->
				{{--
				<li>
					<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
				</li> --}}
			</ul>
		</div>
	</nav>
	@endauth
</header>