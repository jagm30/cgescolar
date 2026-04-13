@if ($paginator->hasPages())
    <nav aria-label="Paginación" class="text-center">
        <ul class="pagination pagination-sm" style="margin: 10px 0;">
            @if ($paginator->onFirstPage())
                <li class="disabled"><span aria-hidden="true">&laquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">&laquo;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">&raquo;</a></li>
            @else
                <li class="disabled"><span aria-hidden="true">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
