<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">NAVEGACIÓN PRINCIPAL</li>

            @if (auth()->check() && auth()->user()->rol === 'admin')
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @endif

            @if (auth()->check() && auth()->user()->rol === 'caja')
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('caja.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @endif
            @if (auth()->check() && auth()->user()->rol === 'recepcion')
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('recepcion.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @endif

            <li
                class="treeview {{ request()->routeIs(['ciclos.index', 'niveles.index', 'grados.index', 'grupos.index']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Configuración Escolar</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('ciclos.index') ? 'active' : '' }}"><a href="{{ route('ciclos.index') }}"><i
                                class="fa fa-circle-o"></i>Ciclos Escolares</a></li>
                    <li class="{{ request()->routeIs('niveles.index') ? 'active' : '' }}"><a href="{{ route('niveles.index') }}"><i class="fa fa-circle-o"></i> Niveles Escolares</a></li>
                    <li class="{{ request()->routeIs('grados.index') ? 'active' : '' }}"><a href="{{ route('grados.index') }}"><i class="fa fa-circle-o"></i> Grados</a></li>
                    <li class="{{ request()->routeIs('grupos.index') ? 'active' : '' }}"><a href="{{ route('grupos.index') }}"><i class="fa fa-circle-o"></i> Grupos</a></li>
                </ul>
            </li>
            <li class="treeview {{ request()->routeIs(['alumnos.index', 'alumnos.create']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Alumnos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('alumnos.index') ? 'active' : '' }}"><a href="{{ route('alumnos.index') }}"><i class="fa fa-circle-o"></i> Lista de Alumnos</a></li>
                    <li class="{{ request()->routeIs('alumnos.create') ? 'active' : '' }}"><a href="{{ route('alumnos.create') }}"><i class="fa fa-circle-o"></i> Registrar Nuevo</a></li>
                </ul>
            </li>
            <li
                class="treeview {{ request()->routeIs(['tables', 'datatables', 'ui-elements', 'forms', 'icons', 'widgets']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Componentes</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('tables') ? 'active' : '' }}"><a href="{{ route('tables') }}"><i
                                class="fa fa-circle-o"></i> Tablas</a></li>
                    <li class="{{ request()->routeIs('datatables') ? 'active' : '' }}"><a
                            href="{{ route('datatables') }}"><i class="fa fa-circle-o"></i> Data Tables</a></li>
                    <li><a href="{{ route('ui-elements') }}"><i class="fa fa-circle-o"></i> UI General</a></li>
                    <li><a href="{{ route('forms') }}"><i class="fa fa-circle-o"></i> Forms</a></li>
                    <li><a href="{{ route('icons') }}"><i class="fa fa-circle-o"></i> Icons</a></li>
                    <li><a href="{{ route('widgets') }}"><i class="fa fa-circle-o"></i> Widgets</a></li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('prospectos.index') ? 'active' : '' }}">
                <a href="{{route('prospectos.index')}}">
                    <i class="fa fa-th"></i> <span>Admisiones</span>
                    <span class="pull-right-container">
                    </span>
                </a>
            </li>
        </ul>
    </section>
</aside>
