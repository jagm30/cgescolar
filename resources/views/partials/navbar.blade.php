<nav class="app-header navbar navbar-expand bg-body">
    <header class="main-header">
        <a href="/" class="logo">
            <span class="logo-mini"><b>C</b>GE</span>
            <span class="logo-lg"><b>CGes</b>Escolar</span>
        </a>

        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">


                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('dist/img/avatar5.png') }}" class="user-image" alt="User Image">
                            <span class="hidden-xs">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <img src="{{ asset('dist/img/avatar5.png') }}" class="img-circle" alt="User Image">
                                <p>
                                    {{ auth()->user()->nombre }}
                                    <small>{{ auth()->user()->email }}</small>

                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat">Perfil</a>
                                </div>
                                <div class="pull-right">
                                    <a href="{{ route('logout') }}" class="btn btn-default btn-flat"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Cerrar Sesión
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
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
