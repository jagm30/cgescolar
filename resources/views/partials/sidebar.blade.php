<aside class="main-sidebar">
    <section class="sidebar">

        {{-- Info del usuario en sidebar --}}
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('dist/img/avatar5.png') }}"
                     class="img-circle" alt="Usuario">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->nombre }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> En línea</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">

            {{-- ── Dashboard según rol ──────────────────── --}}
            @if(auth()->user()->esAdministrador())
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @elseif(auth()->user()->esCajero())
                <li class="{{ request()->routeIs('caja.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('caja.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @elseif(auth()->user()->esRecepcion())
                <li class="{{ request()->routeIs('recepcion.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('recepcion.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @endif

            {{-- ── SECCIÓN: Configuración ───────────────── --}}
            @if(auth()->user()->esAdministrador())
            <li class="header">CONFIGURACIÓN</li>

            <li class="treeview {{ request()->routeIs(['ciclos.*','niveles.*','grados.*','grupos.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-cogs"></i>
                    <span>Estructura escolar</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('ciclos.*') ? 'active' : '' }}">
                        <a href="{{ route('ciclos.index') }}">
                            <i class="fa fa-circle-o"></i> Ciclos escolares
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('niveles.*') ? 'active' : '' }}">
                        <a href="{{ route('niveles.index') }}">
                            <i class="fa fa-circle-o"></i> Niveles
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('grados.*') ? 'active' : '' }}">
                        <a href="{{ route('grados.index') }}">
                            <i class="fa fa-circle-o"></i> Grados
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                        <a href="{{ route('grupos.index') }}">
                            <i class="fa fa-circle-o"></i> Grupos
                        </a>
                    </li>
                </ul>
            </li>

            <li class="treeview {{ request()->routeIs(['conceptos.*', 'planes.*', 'becas.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-money"></i>
                    <span>Planes y conceptos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('conceptos.*') ? 'active' : '' }}">
                        <a href="{{ route('conceptos.index') }}">
                            <i class="fa fa-circle-o"></i> Conceptos de cobro
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('planes.*') ? 'active' : '' }}">
                        <a href="{{ route('planes.index') }}">
                            <i class="fa fa-circle-o"></i> Planes de pago
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('becas.*') ? 'active' : '' }}">
                        <a href="{{ route('becas.index') }}">
                            <i class="fa fa-circle-o"></i> Becas
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('planes.asignar.form') ? 'active' : '' }}">
                        <a href="{{ route('planes.asignar.form') }}">
                            <i class="fa fa-circle-o"></i> Asignar plan
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ── SECCIÓN: Alumnos ─────────────────────── --}}
            @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <li class="header">ALUMNOS</li>

            <li class="treeview {{ request()->routeIs(['familias.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-home"></i>
                    <span>Familias</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('familias.index') ? 'active' : '' }}">
                        <a href="{{ route('familias.index') }}">
                            <i class="fa fa-circle-o"></i> Lista de familias
                        </a>
                    </li>
                    @if(auth()->user()->esAdministrador())
                    <li class="{{ request()->routeIs('familias.create') ? 'active' : '' }}">
                        <a href="{{ route('familias.create') }}">
                            <i class="fa fa-circle-o"></i> Nueva familia
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            <li class="treeview {{ request()->routeIs(['alumnos.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Alumnos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('alumnos.index') ? 'active' : '' }}">
                        <a href="{{ route('alumnos.index') }}">
                            <i class="fa fa-circle-o"></i> Lista de alumnos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('alumnos.create') ? 'active' : '' }}">
                        <a href="{{ route('alumnos.create') }}">
                            <i class="fa fa-circle-o"></i> Registrar alumno
                        </a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('prospectos.*') ? 'active' : '' }}">
                <a href="{{ route('prospectos.index') }}">
                    <i class="fa fa-user-plus"></i> <span>Admisiones</span>
                </a>
            </li>
            @endif

            {{-- ── SECCIÓN: Cobranza ────────────────────── --}}
            @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
            <li class="header">COBRANZA</li>

            <li class="treeview {{ request()->routeIs(['cargos.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-file-text-o"></i>
                    <span>Cargos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('cargos.index') ? 'active' : '' }}">
                        <a href="{{ route('cargos.index') }}">
                            <i class="fa fa-circle-o"></i> Ver cargos
                        </a>
                    </li>
                    @if(auth()->user()->esAdministrador())
                    <li>
                        <a href="{{ route('cargos.index', ['mostrar_generador' => 1]) }}" id="btn-generar-cargos">
                            <i class="fa fa-circle-o"></i> Generar cargos
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            <li class="treeview {{ request()->routeIs(['pagos.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-credit-card"></i>
                    <span>Pagos</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('pagos.index') ? 'active' : '' }}">
                        <a href="{{ route('pagos.index') }}">
                            <i class="fa fa-circle-o"></i> Historial de pagos
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('pagos.create') ? 'active' : '' }}">
                        <a href="{{ route('pagos.create') }}">
                            <i class="fa fa-circle-o"></i> Registrar pago
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pagos.corte') }}">
                            <i class="fa fa-circle-o"></i> Corte del día
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ── SECCIÓN: Administración ──────────────── --}}
            @if(auth()->user()->esAdministrador())
            <li class="header">ADMINISTRACIÓN</li>

            <li class="treeview {{ request()->routeIs(['usuarios.*']) ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-lock"></i>
                    <span>Usuarios</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->routeIs('usuarios.index') ? 'active' : '' }}">
                        <a href="{{ route('usuarios.index') }}">
                            <i class="fa fa-circle-o"></i> Lista de usuarios
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('usuarios.create') ? 'active' : '' }}">
                        <a href="{{ route('usuarios.create') }}">
                            <i class="fa fa-circle-o"></i> Nuevo usuario
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('usuarios.pendientes-portal') ? 'active' : '' }}">
                        <a href="{{ route('usuarios.pendientes-portal') }}">
                            <i class="fa fa-circle-o"></i> Pendientes portal
                        </a>
                    </li>
                </ul>
            </li>
            @endif

        </ul>
    </section>
</aside>
