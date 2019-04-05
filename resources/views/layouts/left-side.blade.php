@auth
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{Auth::user()->avatarUrl}}"
                     alt="User Image"> <!-- class="img-circle" -->
            </div>
            <div class="pull-left info">
                <p>{{Auth::user()->name}}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form (Optional) --
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">Главное меню</li>
            @if(Auth::user()->hasAnyPermission(['view offer', 'view-own offer']))
            <li class="{{request()->is('offers*')?'active':''}}">
                <a href="{{url('/offers')}}"><i class="fa fa-money"></i> <span>КП</span></a>
                {{-- <ul class="treeview-menu">
                    <li class="">
                        <a href="#"><i class="fa fa-circle-o text-orange"></i> <span>Создана</span></a>
                    </li>
                </ul>   --}}
            </li>
            @endif
            @can('view product')
            <li class="{{request()->is('products')?'active':''}}"><a href="{{url('/products')}}"><i class="fa fa-shopping-cart"></i> <span>Товары</span></a></li>
            @endcan
            @can('view file-manager')
            <li class="{{request()->is('file-manager*')?'active':''}}"><a href="{{url('/file-manager')}}"><i class="fa fa-image"></i> <span>Фотографии</span></a></li>
            @endcan
            @if(Auth::user()->hasAnyPermission(['view client', 'view-own client']))
            <li class="{{request()->is('client*')?'active':''}}"><a href="{{url('/client')}}"><i class="fa fa-users"></i> <span>Клиенты</span></a></li>
            @endif
            @can('view settings')
            <li class="treeview {{request()->is('settings/*')?'active':''}}">
                <a href="#"><i class="fa fa-cog"></i> <span>Настройки</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a> 
                <ul class="treeview-menu">
                    <li class="{{request()->is('settings/currencies')?'active':''}}"><a href="/settings/currencies"><i class="fa fa-circle-o"></i> Валюты</a></li>
                    <li class="{{request()->is('settings/employee')?'active':''}}"><a href="/settings/employee"><i class="fa fa-circle-o"></i> Сотрудники</a></li>
                    <li class="{{request()->is('settings/offers/removed')?'active':''}}"><a href="{{url('/settings/offers/removed')}}"><i class="fa fa-circle-o"></i> Удалённые КП</a></li>
                    <li class="{{request()->is('settings/product-custom-fields')?'active':''}}"><a href="{{url('/settings/product-custom-fields')}}"><i class="fa fa-circle-o"></i> Доп. поля товаров</a></li>
                    <li class="{{request()->is('settings/role*')?'active':''}}"><a href="{{url('/settings/role')}}"><i class="fa fa-circle-o"></i> Роли</a></li>
                    <li class="{{request()->is('settings/order*')?'active':''}}"><a href="{{url('/settings/order')}}"><i class="fa fa-circle-o"></i> Оплата сервиса</a></li>
                    <li class="{{request()->is('settings/integration/crm*')?'active':''}}"><a href="{{url('/settings/integration/crm')}}"><i class="fa fa-circle-o"></i><span> Интеграция с CRM</span></a></li>
{{--                    <li class="{{request()->is('settings/scenario*')?'active':''}}"><a href="{{url('/settings/scenario')}}"><i class="fa fa-circle-o"></i><span> Сценарии</span></a></li>--}}
                </ul>
            </li>
            @endcan
            {{-- Admin section --}}
            @if(Auth::user()->hasAnyPermission(['admin offer', 'admin help']))
            <li class="treeview {{request()->is('admin/*')?'active':''}}">
                <a href="#"><i class="fa fa-exclamation-circle"></i>
                    <span>Админка</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    @can('admin offer')
                    <li class="{{request()->is('admin/offers')?'active':''}}"><a href="{{url('/admin/offers')}}"><i class="fa fa-circle-o"></i> КП</a></li>
                    @endcan
                    @can('admin help')
                    <li class="{{request()->is('admin/help')?'active':''}}"><a href="{{url('/admin/help')}}"><i class="fa fa-circle-o"></i> Помощь</a></li>
                    @endcan
                </ul>
            </li>
            @endif
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
@endauth