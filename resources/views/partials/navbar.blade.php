<nav class="app-header navbar navbar-expand bg-body">
    <header class="main-header">
        <a href="/" class="logo">
            <span class="logo-mini"><b>C</b>GE</span>
            <span class="logo-lg"><b>CGes</b>Escolar</span>
        </a>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                <li><a href="#">Ciclos Escolar</a></li>
                    <li class="dropdown active">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $cicloActual?->nombre ?? 'Sin ciclo' }} <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            @if(auth()->check() && auth()->user()->esInterno())
                                @forelse($ciclosDisponibles as $ciclo)
                                <li value="{{ $ciclo->id }}" class="{{ $cicloActual?->id === $ciclo->id ? 'active' : '' }}"><a href="#"  class="cambiar-ciclo" data-id="{{ $ciclo->id }}" style="color:#140947;" ><b>{{ $ciclo->nombre }} ({{ ucfirst($ciclo->estado) }})</b></a></li>
                                @empty
                                <li class="active"><a href="#" style="text-align: center;" >Sin ciclos activos</a></li>
                                @endforelse
                            @endif
                        </ul>
                    </li>

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

@push('scripts')
<script>
    $(document).on('click', '.cambiar-ciclo', function (e) {
    e.preventDefault();

    const id     = $(this).data('id');
    const nombre = $(this).text();

    $.ajax({
        url: '/ciclos/'+id+'/seleccionar',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            location.reload();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al cambiar de ciclo.');
        }
    });
});
</script>
@endpush