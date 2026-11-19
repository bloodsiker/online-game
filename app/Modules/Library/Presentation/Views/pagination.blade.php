@if($paginator->hasPages())
    {{-- Первая страница — без ?page=1, чтобы не плодить дубль с canonical на адрес без page. --}}
    @php($firstPageUrl = rtrim(preg_replace('/([?&])page=1(&|$)/', '$1', $paginator->url(1)), '?&'))
    <nav aria-label="Страницы">
        @if($paginator->onFirstPage())
            <span class="library-page-link disabled">&laquo;</span>
        @else
            <a class="library-page-link" href="{{ $paginator->currentPage() === 2 ? $firstPageUrl : $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
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
                        <a class="library-page-link" href="{{ $page === 1 ? $firstPageUrl : $url }}">{{ $page }}</a>
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
