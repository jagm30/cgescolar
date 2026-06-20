<aside class="main-sidebar">
    <section class="sidebar">

        {{-- Info del usuario en sidebar --}}
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ auth()->user()->foto_url }}" class="img-circle" alt="Usuario">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->nombre }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> En línea</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">

            {{-- ── Dashboard según rol ──────────────────── --}}
            @if (auth()->user()->esAdministrador())
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
            @elseif(auth()->user()->esPadre())
                <li class="{{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('portal.dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Portal</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->esPadre())
                <li class="header">FAMILIA</li>

                <li
                    class="{{ request()->routeIs('portal.hijos', 'portal.estado-cuenta', 'portal.historial-pagos') ? 'active' : '' }}">
                    <a href="{{ route('portal.hijos') }}">
                        <i class="fa fa-users"></i> <span>Mis hijos</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('portal.razones-sociales') ? 'active' : '' }}">
                    <a href="{{ route('portal.razones-sociales') }}">
                        <i class="fa fa-building-o"></i> <span>Datos fiscales</span>
                    </a>
                </li>
            @endif

            {{-- ── SECCIÓN: Configuración ───────────────── --}}
            @if (auth()->user()->esAdministrador() || auth()->user()->esCajero() || auth()->user()->esRecepcion())
                <li class="header">CONFIGURACIÓN</li>

                <li
                    class="treeview {{ request()->routeIs(['ciclos.*', 'niveles.*', 'grados.*', 'grupos.*']) ? 'active menu-open' : '' }}">
                    <a href="#">
                        <i class="fa fa-cogs"></i>
                        <span>Estructura escolar</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (auth()->user()->esAdministrador())
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
                        @endif
                        <li class="{{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                            <a href="{{ route('grupos.index') }}">
                                <i class="fa fa-circle-o"></i> Grupos
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (auth()->user()->esAdministrador())
                <li
                    class="treeview {{ request()->routeIs(['conceptos.*', 'planes.*', 'becas.*', 'condonaciones.*']) ? 'active menu-open' : '' }}">
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
                        <li
                            class="{{ request()->routeIs('planes.*') && !request()->routeIs('planes.asignar.*') ? 'active' : '' }}">
                            <a href="{{ route('planes.index') }}">
                                <i class="fa fa-circle-o"></i> Planes de pago
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('becas.*') ? 'active' : '' }}">
                            <a href="{{ route('becas.index') }}">
                                <i class="fa fa-circle-o"></i> Becas
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('condonaciones.*') ? 'active' : '' }}">
                            <a href="{{ route('condonaciones.index') }}">
                                <i class="fa fa-circle-o"></i> Condonaciones
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('planes.asignar.*') ? 'active' : '' }}">
                            <a href="{{ route('planes.asignar.form') }}">
                                <i class="fa fa-circle-o"></i> Asignar plan
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- ── SECCIÓN: Alumnos ─────────────────────── --}}
            @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion() || auth()->user()->esCajero())
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
                        @if (auth()->user()->esAdministrador())
                            <li class="{{ request()->routeIs('familias.create') ? 'active' : '' }}">
                                <a href="{{ route('familias.create') }}">
                                    <i class="fa fa-circle-o"></i> Nueva familia
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li class="treeview {{ request()->routeIs(['alumnos.*', 'reinscripciones.*']) ? 'active menu-open' : '' }}">
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
                        @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <li class="{{ request()->routeIs('alumnos.create') ? 'active' : '' }}">
                            <a href="{{ route('alumnos.create') }}">
                                <i class="fa fa-circle-o"></i> Registrar alumno
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('reinscripciones.*') ? 'active' : '' }}">
                            <a href="{{ route('reinscripciones.index') }}">
                                <i class="fa fa-circle-o"></i> Reinscripciones
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('alumnos.bajas') ? 'active' : '' }}">
                            <a href="{{ route('alumnos.bajas') }}">
                                <i class="fa fa-circle-o"></i> Reporte de bajas
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                <li class="{{ request()->routeIs('prospectos.*') ? 'active' : '' }}">
                    <a href="{{ route('prospectos.index') }}">
                        <i class="fa fa-user-plus"></i> <span>Admisiones</span>
                    </a>
                </li>
                @endif
            @endif

            {{-- ── SECCIÓN: Cobranza ────────────────────── --}}
            @if (auth()->user()->esAdministrador() || auth()->user()->esCajero())
                <li class="header">COBRANZA</li>

                <li class="{{ request()->routeIs('cobros.index') ? 'active' : '' }}">
                    <a href="{{ route('cobros.index') }}">
                        <i class="fa fa-shopping-cart"></i> <span>Cobros</span>
                    </a>
                </li>

                <li
                    class="treeview {{ request()->routeIs(['pagos.*', 'reportes.deudores']) ? 'active menu-open' : '' }}">
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
                        <li class="{{ request()->routeIs('pagos.corte') ? 'active' : '' }}">
                            <a href="{{ route('pagos.corte') }}">
                                <i class="fa fa-circle-o"></i> Corte del día
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pagos.detalle-ingresos') ? 'active' : '' }}">
                            <a href="{{ route('pagos.detalle-ingresos') }}">
                                <i class="fa fa-circle-o"></i> Detalle de ingresos
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('reportes.deudores') ? 'active' : '' }}">
                            <a href="{{ route('reportes.deudores') }}">
                                <i class="fa fa-circle-o"></i> Reporte de deudores
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="{{ request()->routeIs('facturas.index') ? 'active' : '' }}">
                    <a href="{{ route('facturas.index') }}">
                        <i class="fa fa-file-text-o"></i> <span>Facturas</span>
                    </a>
                </li>
            @endif

            {{-- ── SECCIÓN: Administración ──────────────── --}}
            @if (auth()->user()->esAdministrador())
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

                        <li class="{{ request()->routeIs('usuarios.pendientes-portal') ? 'active' : '' }}">
                            <a href="{{ route('usuarios.pendientes-portal') }}">
                                <i class="fa fa-circle-o"></i> Pendientes portal
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (auth()->user()->esAdministrador())
                <li>
                    <a href="{{ route('settings.index') }}">
                        <i class="fa fa-gear"></i> <span>Configuración</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('credenciales.index') }}">
                        <i class="fa fa-id-card"></i> <span>Editor de Credenciales</span>
                    </a>
                </li>
            @endif

        </ul>
    </section>
</aside>
