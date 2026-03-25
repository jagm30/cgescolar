<aside class="main-sidebar">
    <section class="sidebar">
        


        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">NAVEGACIÓN PRINCIPAL</li>
            
            <li class="active">
                <a href="/">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
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
                        <li class="treeview">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Componentes</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('tables') }}"><i class="fa fa-circle-o"></i> Tablas</a></li>
                    <li><a href="{{ route('datatables') }}"><i class="fa fa-circle-o"></i> Data Tables</a></li>
                    <li><a href="{{ route('ui-elements') }}"><i class="fa fa-circle-o"></i> UI General</a></li>
                    <li><a href="{{ route('forms') }}"><i class="fa fa-circle-o"></i> Forms</a></li>
                    <li><a href="{{ route('icons') }}"><i class="fa fa-circle-o"></i> Icons</a></li>
                    <li><a href="{{ route('widgets') }}"><i class="fa fa-circle-o"></i> Widgets</a></li>
                </ul>
            </li>

            </ul>
    </section>
</aside>