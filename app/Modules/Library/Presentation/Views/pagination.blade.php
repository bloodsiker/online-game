@if($paginator->hasPages())
    <nav aria-label="Страницы">
        @if($paginator->onFirstPage())
            <span class="library-page-link disabled">&laquo;</span>
        @else
            <a class="library-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
        @endif

        @foreach($elements as $element)
            @if(is_string($element))
                <span class="library-page-link disabled">{{ $element }}</span>
            @endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page === $paginator->currentPage())
                        <span class="library-page-link active">{{ $page }}</span>
                    @else
                        <a class="library-page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a class="library-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
        @else
            <span class="library-page-link disabled">&raquo;</span>
        @endif
    </nav>
@endif
