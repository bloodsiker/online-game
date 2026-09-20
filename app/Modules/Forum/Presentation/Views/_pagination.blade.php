@if($paginator->hasPages())
    @php
        $firstPage = max(1, $paginator->currentPage() - 3);
        $lastPage = min($paginator->lastPage(), $paginator->currentPage() + 3);
    @endphp
    <nav class="forum-pagination" aria-label="Навигация по страницам">
        @if($paginator->onFirstPage())
            <span class="disabled">«</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a>
        @endif

        @for($page = $firstPage; $page <= $lastPage; $page++)
            @if($page === $paginator->currentPage())
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">»</a>
        @else
            <span class="disabled">»</span>
        @endif
    </nav>
@endif
