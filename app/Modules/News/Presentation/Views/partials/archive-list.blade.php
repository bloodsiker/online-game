@foreach($newsPosts as $newsPost)
    <div class="b-news-item">
        <div class="b-post__container">
            <article class="b-post b-post--index">
                <div class="b-post__header">
                    <h2 class="b-post__footer news-archive__heading">
                        <a class="b-post__title-link c-red" href="{{ route('index', ['n' => $newsPost->id]) }}"><b>{{ $newsPost->title }}</b></a>
                        <span class="news-archive__meta">
                            <time datetime="{{ $newsPost->created_at?->toDateString() }}">{{ $newsPost->created_at?->format('d.m.Y') }}</time>
                        </span>
                    </h2>
                </div>
            </article>
        </div>
    </div>
    <div class="b-divider-2" aria-hidden="true">
        <div class="b-divider-2__l"><div class="b-divider-2__l-inner"></div></div>
        <div class="b-divider-2__r"><div class="b-divider-2__r-inner"></div></div>
        <div class="b-divider-2__c"></div>
    </div>
@endforeach
