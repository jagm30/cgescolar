@if ($paginator->hasPages())
    <nav aria-label="Paginación" class="text-center">
        <ul class="pagination pagination-sm" style="margin: 10px 0;">
            @if ($paginator->onFirstPage())
                <li class="disabled"><span>&laquo; Anterior</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Anterior</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente &raquo;</a></li>
            @else
                <li class="disabled"><span>Siguiente &raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
