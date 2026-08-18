<header class="main-header">

    {{-- Logo --}}
    <a href="#" class="logo">
        {{-- MINI LOGO: Aparece cuando el sidebar se colapsa --}}
        <span class="logo-mini">
            <img src="{{ asset('dist/img/logo_genki_h.png') }}" alt="Genki School" style="height: 35px; width: auto;">
        </span>

        {{-- LOGO NORMAL: Aparece cuando está expandido --}}
        <span class="logo-lg"><img src="{{ asset('dist/img/logo_genki_h.png') }}" alt="Genki School"
                style="height: 45px; width: auto;"></span>
    </a>
    {{-- Navbar --}}
    <nav class="navbar navbar-static-top">

        {{-- Botón toggle sidebar --}}
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                {{-- Selector de ciclo escolar (solo usuarios internos) --}}
                @if (auth()->check() && auth()->user()->esInterno())
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-calendar"></i>
                            {{ $cicloActual?->nombre ?? 'Sin ciclo' }}
                            <span
                                class="label label-{{ $cicloActual?->estado === 'activo' ? 'success' : 'warning' }} hidden-xs">
                                {{ ucfirst($cicloActual?->estado ?? '') }}
                            </span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="dropdown-header">Cambiar ciclo de trabajo</li>
                            @forelse($ciclosDisponibles as $ciclo)
                                <li class="{{ $cicloActual?->id === $ciclo->id ? 'active' : '' }}">
                                    <a href="#" class="cambiar-ciclo" data-id="{{ $ciclo->id }}">
                                        <i
                                            class="fa fa-{{ $cicloActual?->id === $ciclo->id ? 'check-circle text-green' : 'circle-o' }}"></i>
                                        {{ $ciclo->nombre }}
                                        <span
                                            class="label label-{{ $ciclo->estado === 'activo' ? 'success' : ($ciclo->estado === 'configuracion' ? 'warning' : 'default') }}">
                                            {{ ucfirst($ciclo->estado) }}
                                        </span>
                                    </a>
                                </li>
                            @empty
                                <li><a href="#" style="text-align:center; color:#999;">Sin ciclos registrados</a>
                                </li>
                            @endforelse
                        </ul>
                    </li>
                @endif

                {{-- Menú de usuario --}}
                {{-- Los usuarios del sistema no tienen foto.
                     La foto es exclusiva de contacto_familiar (credenciales) y alumno. --}}
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                       style="padding: 8px 14px; display:flex; align-items:center; gap:8px;">
                        <span style="
                            width: 32px; height: 32px; border-radius: 50%;
                            background: rgba(255,255,255,.15);
                            border: 2px solid rgba(255,255,255,.4);
                            display: flex; align-items: center; justify-content: center;
                            font-size: 14px; color: #fff; font-weight: 700; flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}
                        </span>
                        <span class="hidden-xs" style="font-size:13px; color:#fff; font-weight:600;">
                            {{ auth()->user()->nombre }}
                        </span>
                        <i class="fa fa-angle-down hidden-xs" style="color:rgba(255,255,255,.6); font-size:11px;"></i>
                    </a>

                    <ul class="dropdown-menu" style="
                        width: 260px;
                        border: none;
                        border-radius: 8px;
                        box-shadow: 0 8px 24px rgba(0,0,0,.15);
                        padding: 0;
                        overflow: hidden;
                        margin-top: 4px;">

                        {{-- Encabezado --}}
                        <li style="
                            background: linear-gradient(135deg, #0b3c50 0%, #1a6b8a 100%);
                            padding: 18px 16px;
                            display: flex;
                            align-items: center;
                            gap: 12px;">
                            <span style="
                                width: 46px; height: 46px; border-radius: 50%;
                                background: rgba(255,255,255,.2);
                                border: 2px solid rgba(255,255,255,.5);
                                display: flex; align-items: center; justify-content: center;
                                font-size: 20px; font-weight: 700; color: #fff; flex-shrink: 0;">
                                {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}
                            </span>
                            <div style="min-width:0;">
                                <div style="font-size:14px; font-weight:700; color:#fff;
                                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ auth()->user()->nombre }}
                                </div>
                                <div style="font-size:11px; color:rgba(255,255,255,.7); margin-top:2px;
                                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ auth()->user()->email }}
                                </div>
                                <span style="
                                    display: inline-block; margin-top: 5px;
                                    background: rgba(72,196,161,.3);
                                    border: 1px solid rgba(72,196,161,.6);
                                    color: #48c4a1;
                                    font-size: 10px; font-weight: 700;
                                    padding: 1px 8px; border-radius: 10px;
                                    letter-spacing: .04em; text-transform: uppercase;">
                                    {{ ucfirst(auth()->user()->rol) }}
                                </span>
                            </div>
                        </li>

                        {{-- Separador --}}
                        <li style="border-top: 1px solid #f0f0f0;"></li>

                        {{-- Perfil --}}
                        <li>
                            <a href="{{ route('usuarios.perfil') }}" style="
                                display: flex; align-items: center; gap: 10px;
                                padding: 11px 16px; color: #333; font-size: 13px;
                                text-decoration: none; transition: background .12s;">
                                <span style="
                                    width: 28px; height: 28px; border-radius: 6px;
                                    background: #e8f4fb; color: #1a6b8a;
                                    display: flex; align-items: center; justify-content: center;
                                    font-size: 13px; flex-shrink: 0;">
                                    <i class="fa fa-user"></i>
                                </span>
                                Mi perfil
                            </a>
                        </li>

                        {{-- Separador --}}
                        <li style="border-top: 1px solid #f0f0f0;"></li>

                        {{-- Cerrar sesión --}}
                        <li>
                            <a href="{{ route('logout') }}" style="
                                display: flex; align-items: center; gap: 10px;
                                padding: 11px 16px; color: #c0392b; font-size: 13px;
                                text-decoration: none; transition: background .12s;"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <span style="
                                    width: 28px; height: 28px; border-radius: 6px;
                                    background: #fdecea; color: #c0392b;
                                    display: flex; align-items: center; justify-content: center;
                                    font-size: 13px; flex-shrink: 0;">
                                    <i class="fa fa-sign-out"></i>
                                </span>
                                Cerrar sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        </li>

                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
