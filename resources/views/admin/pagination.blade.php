@once
    <style>
        .admin-pagination {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
        }
        .admin-pagination__info {
            color: #777;
            font-size: 12px;
        }
        .admin-pagination__list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .admin-pagination__link,
        .admin-pagination__disabled,
        .admin-pagination__active {
            align-items: center;
            border: 1px solid #d7d7d7;
            border-radius: 4px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            height: 30px;
            justify-content: center;
            line-height: 30px;
            min-width: 30px;
            padding: 0 9px;
        }
        .admin-pagination__link {
            background: #fff;
            color: #337ab7;
            text-decoration: none;
        }
        .admin-pagination__link:hover {
            background: #f3f7fb;
            border-color: #9fc5e8;
            color: #23527c;
            text-decoration: none;
        }
        .admin-pagination__active {
            background: #0088cc;
            border-color: #0088cc;
            color: #fff;
        }
        .admin-pagination__disabled {
            background: #f8f8f8;
            color: #b5b5b5;
        }
    </style>
@endonce

@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="admin-pagination__info">
            Показано {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} из {{ $paginator->total() }}
        </div>

        <ul class="admin-pagination__list">
            @if ($paginator->onFirstPage())
                <li><span class="admin-pagination__disabled">«</span></li>
            @else
                <li><a class="admin-pagination__link" href="{{ $paginator->url(1) }}" rel="first">«</a></li>
            @endif

            @if ($paginator->onFirstPage())
                <li><span class="admin-pagination__disabled">‹</span></li>
            @else
                <li><a class="admin-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="admin-pagination__disabled">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="admin-pagination__active">{{ $page }}</span></li>
                        @else
                            <li><a class="admin-pagination__link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="admin-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a></li>
            @else
                <li><span class="admin-pagination__disabled">›</span></li>
            @endif

            @if ($paginator->hasMorePages())
                <li><a class="admin-pagination__link" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last">»</a></li>
            @else
                <li><span class="admin-pagination__disabled">»</span></li>
            @endif
        </ul>
    </nav>
@elseif ($paginator->total() > 0)
    <div class="admin-pagination">
        <div class="admin-pagination__info">
            Показано {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} из {{ $paginator->total() }}
        </div>
    </div>
@endif
