@if (session()->has('success') || session()->has('error') || session()->has('warning'))
    @php
        // Determina el color y el icono según el tipo de mensaje
        $tipo = session()->has('success') ? 'success' : (session()->has('error') ? 'danger' : 'warning');
        $icono = $tipo === 'success' ? 'fa-check' : ($tipo === 'danger' ? 'fa-ban' : 'fa-exclamation-triangle');
        $titulo = $tipo === 'success' ? '¡Éxito!' : ($tipo === 'danger' ? 'Error' : 'Atención');
        $mensaje = session('success') ?? session('error') ?? session('warning');
    @endphp

    <div id="toast-component" class="alert alert-{{ $tipo }} alert-dismissible" 
         style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: none; min-width: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa {{ $icono }}"></i> {{ $titulo }}</h4>
        {{ $mensaje }}
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#toast-component').fadeIn('fast').delay(2000).fadeOut('slow');
        });
    </script>
@endif