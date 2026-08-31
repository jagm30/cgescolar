<style>
    /* Reutiliza los mismos estilos del sidebar principal para el mini-sidebar */
    @media (min-width: 768px) {
        body.sidebar-collapse .user-panel { padding: 15px 0 !important; display: flex; justify-content: center; }
        body.sidebar-collapse .user-panel .image { width: 100% !important; float: none !important; text-align: center; }
        body.sidebar-collapse .user-panel .image img { width: 30px !important; height: 30px !important; max-width: 30px !important; margin: 0 auto !important; object-fit: cover; }
        body.sidebar-collapse .user-panel .info { display: none !important; }
        body.sidebar-collapse .sidebar-menu>li:hover>a { background: transparent !important; border: none !important; }
        body.sidebar-collapse .sidebar-menu>li>a>.pull-right-container { display: none !important; }
        body.sidebar-collapse .sidebar-menu>li:hover>a>span:not(.pull-right-container) {
            display: flex !important; align-items: center !important; position: absolute !important;
            top: 0 !important; left: 55px !important; width: 250px !important; height: 45px !important;
            margin: 0 !important; padding: 0 20px !important; background-color: #061f2a !important;
            border-radius: 6px 6px 0 0 !important; box-shadow: 5px 5px 15px rgba(0,0,0,.15) !important;
            border: none !important; box-sizing: border-box !important; z-index: 1050 !important;
        }
        body.sidebar-collapse .sidebar-menu>li:hover>.treeview-menu {
            display: block !important; position: absolute !important; top: 45px !important; left: 55px !important;
            width: 250px !important; margin: 0 !important; padding: 0 0 10px 0 !important;
            background-color: #061f2a !important; border-radius: 0 0 6px 6px !important;
            box-shadow: 5px 8px 15px rgba(0,0,0,.15) !important; border: none !important;
            box-sizing: border-box !important; z-index: 1049 !important;
        }
        body.sidebar-collapse .sidebar-menu .treeview-menu>li>a {
            white-space: normal !important; padding: 10px 20px !important; margin: 0 !important;
            color: #8aa4af !important; display: block !important; line-height: 1.4 !important; width: 100% !important;
        }
        body.sidebar-collapse .sidebar-menu .treeview-menu>li>a:hover { color: #fff !important; background-color: rgba(255,255,255,.05) !important; }
    }
</style>

<aside class="main-sidebar">
    <section class="sidebar">

        {{-- Info del usuario --}}
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

            {{-- ── Dashboard ───────────────────────────── --}}
            <li class="{{ request()->routeIs('educativo.dashboard') ? 'active' : '' }}">
                <a href="{{ route('educativo.dashboard') }}">
                    <i class="fa fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- ── SECCIÓN: SICAP ───────────────────────── --}}
            <li class="header">SICAP</li>

            @if(auth()->user()->esAdministrador())
                {{-- Catálogos educativos: solo administrador configura --}}
                <li class="treeview {{ request()->routeIs('educativo.sicap.escalas.*', 'educativo.sicap.planes.*') ? 'active menu-open' : '' }}">
                    <a href="#">
                        <i class="fa fa-graduation-cap"></i>
                        <span>Catálogos</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="{{ request()->routeIs('educativo.sicap.escalas.*') ? 'active' : '' }}">
                            <a href="{{ route('educativo.sicap.escalas.index') }}">
                                <i class="fa fa-circle-o"></i> Escalas de evaluación
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('educativo.sicap.planes.*') ? 'active' : '' }}">
                            <a href="{{ route('educativo.sicap.planes.index') }}">
                                <i class="fa fa-circle-o"></i> Planes de estudio
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Períodos evaluativos --}}
                <li class="{{ request()->routeIs('educativo.sicap.periodos.*') ? 'active' : '' }}">
                    <a href="{{ route('educativo.sicap.periodos.index') }}">
                        <i class="fa fa-calendar"></i>
                        <span>Períodos evaluativos</span>
                    </a>
                </li>

                {{-- Asignaciones docente-materia --}}
                <li class="{{ request()->routeIs('educativo.sicap.asignaciones.*') ? 'active' : '' }}">
                    <a href="{{ route('educativo.sicap.asignaciones.index') }}">
                        <i class="fa fa-user-plus"></i>
                        <span>Asignaciones docentes</span>
                    </a>
                </li>
            @endif

            {{-- Captura de calificaciones --}}
            <li class="{{ request()->routeIs('educativo.captura.*') ? 'active' : '' }}">
                <a href="{{ route('educativo.captura.index') }}">
                    <i class="fa fa-pencil-square-o"></i>
                    <span>Captura de calificaciones</span>
                </a>
            </li>

            @if(auth()->user()->esAdministrador())
                {{-- Boletas --}}
                <li class="{{ request()->routeIs('educativo.boleta.*') ? 'active' : '' }}">
                    <a href="{{ route('educativo.boleta.index') }}">
                        <i class="fa fa-file-text-o"></i>
                        <span>Boletas</span>
                    </a>
                </li>
            @endif

        </ul>
    </section>
</aside>
