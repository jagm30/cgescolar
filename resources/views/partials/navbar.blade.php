<header class="main-header">

    {{-- Logo --}}
    <a href="#" class="logo">
        <span class="logo-mini"><b>C</b>GE</span>
        <span class="logo-lg"><b>CGes</b>Escolar</span>
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
                @if(auth()->check() && auth()->user()->esInterno())
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-calendar"></i>
                        {{ $cicloActual?->nombre ?? 'Sin ciclo' }}
                        <span class="label label-{{ $cicloActual?->estado === 'activo' ? 'success' : 'warning' }} hidden-xs">
                            {{ ucfirst($cicloActual?->estado ?? '') }}
                        </span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-header">Cambiar ciclo de trabajo</li>
                        @forelse($ciclosDisponibles as $ciclo)
                            <li class="{{ $cicloActual?->id === $ciclo->id ? 'active' : '' }}">
                                <a href="#"
                                   class="cambiar-ciclo"
                                   data-id="{{ $ciclo->id }}">
                                    <i class="fa fa-{{ $cicloActual?->id === $ciclo->id ? 'check-circle text-green' : 'circle-o' }}"></i>
                                    {{ $ciclo->nombre }}
                                    <span class="label label-{{ $ciclo->estado === 'activo' ? 'success' : ($ciclo->estado === 'configuracion' ? 'warning' : 'default') }}">
                                        {{ ucfirst($ciclo->estado) }}
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li><a href="#" style="text-align:center; color:#999;">Sin ciclos registrados</a></li>
                        @endforelse
                    </ul>
                </li>
                @endif

                {{-- Menú de usuario --}}
                {{-- Los usuarios del sistema no tienen foto.
                     La foto es exclusiva de contacto_familiar (credenciales) y alumno. --}}
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ asset('dist/img/avatar5.png') }}"
                             class="user-image" alt="Usuario">
                        <span class="hidden-xs">{{ auth()->user()->nombre }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        {{-- Encabezado del menú --}}
                        <li class="user-header">
                            <img src="{{ asset('dist/img/avatar5.png') }}"
                                 class="img-circle" alt="Usuario">
                            <p>
                                {{ auth()->user()->nombre }}
                                <small>{{ auth()->user()->email }}</small>
                                <small>
                                    <span class="label label-primary">
                                        {{ ucfirst(auth()->user()->rol) }}
                                    </span>
                                </small>
                            </p>
                        </li>

                        {{-- Footer del menú --}}
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{ route('usuarios.perfil') }}" class="btn btn-default btn-flat">
                                    <i class="fa fa-user"></i> Perfil
                                </a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ route('logout') }}"
                                   class="btn btn-default btn-flat"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out"></i> Cerrar sesión
                                </a>
                                <form id="logout-form"
                                      action="{{ route('logout') }}"
                                      method="POST"
                                      style="display:none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
