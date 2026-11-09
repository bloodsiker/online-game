@if($paginator->hasPages())
    <nav class="homepage-news-pagination" aria-label="{{ __('homepage.news_pagination') }}">
        <div class="b-pager b-pager--default">
            <div class="b-pager__container">
                <ul class="b-pager__list">
                    @unless($paginator->onFirstPage())
                        <li class="b-pager__item"><a class="b-pager__link" href="{{ $paginator->currentPage() === 2 ? route('news.index') : $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">«</a></li>
                    @endunless
                    @foreach($elements as $element)
                        @if(is_string($element))
                            <li class="b-pager__space" aria-hidden="true">{{ $element }}</li>
                        @else
                            @foreach($element as $page => $url)
                                <li class="b-pager__item {{ $page === $paginator->currentPage() ? 'b-pager__item--active' : '' }}">
                                    @if($page === $paginator->currentPage())
                                        <span class="b-pager__link" aria-current="page">{{ $page }}</span>
                                    @else
                                        <a class="b-pager__link" href="{{ $page === 1 ? route('news.index') : $url }}">{{ $page }}</a>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                    @if($paginator->hasMorePages())
                        <li class="b-pager__item"><a class="b-pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">»</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
