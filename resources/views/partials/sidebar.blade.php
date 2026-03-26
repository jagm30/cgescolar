<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">NAVEGACIÓN PRINCIPAL</li>
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="treeview {{ request()->routeIs(['tables','datatables','ui-elements','forms','icons','widgets']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Configuración Escolar</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('ciclos.index') ? 'active' : '' }}"><a href="{{ route('ciclos.index') }}"><i class="fa fa-circle-o"></i> Ciclos Escolares</a></li>
                    <li class="{{ request()->routeIs('datatables') ? 'active' : '' }}"><a href="{{ route('datatables') }}"><i class="fa fa-circle-o"></i> Niveles Escolares</a></li>
                    <li><a href="{{ route('ui-elements') }}"><i class="fa fa-circle-o"></i>Grados</a></li>
                    <li><a href="{{ route('forms') }}"><i class="fa fa-circle-o"></i> Grupos></li>                    
                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Alumnos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Lista de Alumnos</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Registrar Nuevo</a></li>
                </ul>
            </li>
            <li class="treeview {{ request()->routeIs(['tables','datatables','ui-elements','forms','icons','widgets']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Componentes</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('tables') ? 'active' : '' }}"><a href="{{ route('tables') }}"><i class="fa fa-circle-o"></i> Tablas</a></li>
                    <li class="{{ request()->routeIs('datatables') ? 'active' : '' }}"><a href="{{ route('datatables') }}"><i class="fa fa-circle-o"></i> Data Tables</a></li>
                    <li><a href="{{ route('ui-elements') }}"><i class="fa fa-circle-o"></i> UI General</a></li>
                    <li><a href="{{ route('forms') }}"><i class="fa fa-circle-o"></i> Forms</a></li>
                    <li><a href="{{ route('icons') }}"><i class="fa fa-circle-o"></i> Icons</a></li>
                    <li><a href="{{ route('widgets') }}"><i class="fa fa-circle-o"></i> Widgets</a></li>
                </ul>
            </li>
        </ul>
    </section>
</aside>