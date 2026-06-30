<style>
    /* ==========================================================
       CORRECCIONES PARA EL SIDEBAR CONTRAÍDO (MINI SIDEBAR)
       ========================================================== */
    @media (min-width: 768px) {

        /* 1. Centrar y reducir la foto de perfil */
        body.sidebar-collapse .user-panel {
            padding: 15px 0 !important;
            display: flex;
            justify-content: center;
        }

        body.sidebar-collapse .user-panel .image {
            width: 100% !important;
            float: none !important;
            text-align: center;
        }

        body.sidebar-collapse .user-panel .image img {
            width: 30px !important;
            height: 30px !important;
            max-width: 30px !important;
            margin: 0 auto !important;
            object-fit: cover;
        }

        body.sidebar-collapse .user-panel .info {
            display: none !important;
        }

        /* 2. ELIMINAR EL FONDO SOBRESALIENTE DE ADMINLTE */
        body.sidebar-collapse .sidebar-menu>li:hover>a {
            background: transparent !important;
            /* Quita el rectángulo gris/blanco feo */
            border: none !important;
        }

        /* Ocultar la flechita derecha al contraer para que no estorbe */
        body.sidebar-collapse .sidebar-menu>li>a>.pull-right-container {
            display: none !important;
        }

        /* 3. CAJA DEL TÍTULO FLOTANTE (Ej: "Familias") */
        body.sidebar-collapse .sidebar-menu>li:hover>a>span:not(.pull-right-container) {
            display: flex !important;
            align-items: center !important;
            position: absolute !important;
            top: 0 !important;
            left: 55px !important;
            /* Separado 5px del sidebar para un diseño más limpio */
            width: 250px !important;
            /* Ancho unificado */
            height: 45px !important;
            /* Altura estricta */
            margin: 0 !important;
            padding: 0 20px !important;
            background-color: #061f2a !important;
            /* Azul oscuro corporativo */
            border-radius: 6px 6px 0 0 !important;
            /* Redondear esquinas superiores */
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.15) !important;
            /* Sombra suave envolvente */
            border: none !important;
            box-sizing: border-box !important;
            z-index: 1050 !important;
        }

        /* 4. CAJA DE LOS ENLACES (Ej: "Lista de familias") */
        body.sidebar-collapse .sidebar-menu>li:hover>.treeview-menu {
            display: block !important;
            position: absolute !important;
            top: 45px !important;
            /* Empieza EXACTAMENTE donde termina el título */
            left: 55px !important;
            /* Misma separación (55px) */
            width: 250px !important;
            /* Mismo ancho (250px) */
            margin: 0 !important;
            padding: 0 0 10px 0 !important;
            /* Sin padding arriba para unir piezas */
            background-color: #061f2a !important;
            /* MISMO FONDO */
            border-radius: 0 0 6px 6px !important;
            /* Redondear esquinas inferiores */
            box-shadow: 5px 8px 15px rgba(0, 0, 0, 0.15) !important;
            /* Sombra que continúa hacia abajo */
            border: none !important;
            box-sizing: border-box !important;
            z-index: 1049 !important;
        }

        /* 5. Ajuste de los enlaces dentro del menú flotante */
        body.sidebar-collapse .sidebar-menu .treeview-menu>li>a {
            white-space: normal !important;
            /* Permite salto de línea si es muy largo */
            padding: 10px 20px !important;
            /* Alineado con el título */
            margin: 0 !important;
            color: #8aa4af !important;
            display: block !important;
            line-height: 1.4 !important;
            width: 100% !important;
        }

        body.sidebar-collapse .sidebar-menu .treeview-menu>li>a:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
            /* Hover sutil */
        }
    }
</style>

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
            @elseif(auth()->user()->esAdmisiones())
                <li class="{{ request()->routeIs('prospectos.metricas') ? 'active' : '' }}">
                    <a href="{{ route('prospectos.metricas') }}">
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

                <li
                    class="treeview {{ request()->routeIs(['alumnos.*', 'reinscripciones.*']) ? 'active menu-open' : '' }}">
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
            @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                <li class="header">ADMINISTRACIÓN</li>

                <li class="{{ request()->routeIs('personal.*') ? 'active' : '' }}">
                    <a href="{{ route('personal.index') }}">
                        <i class="fa fa-id-badge"></i> <span>Personal</span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->esAdministrador())
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

            {{-- ── SECCIÓN: Admisiones (rol admisiones) ─── --}}
            @if (auth()->user()->esAdmisiones())
                <li class="header">ADMISIONES</li>

                <li class="{{ request()->routeIs('prospectos.*') ? 'active' : '' }}">
                    <a href="{{ route('prospectos.index') }}">
                        <i class="fa fa-user-plus"></i> <span>Prospectos</span>
                    </a>
                </li>

                <li class="header">ALUMNOS</li>

                <li class="{{ request()->routeIs('alumnos.index', 'alumnos.show') ? 'active' : '' }}">
                    <a href="{{ route('alumnos.index') }}">
                        <i class="fa fa-users"></i> <span>Lista de alumnos</span>
                    </a>
                </li>
            @endif

        </ul>
    </section>
</aside>
