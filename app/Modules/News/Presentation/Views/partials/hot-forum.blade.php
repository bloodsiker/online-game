<section class="b-shape b-aside__shape homepage-forum">
    <div class="b-shape__header">
        <div class="b-shape__decor"></div>
        <div class="homepage-heading homepage-heading--reference">{{ __('homepage.hot_forum') }}</div>
    </div>
    <div class="homepage-forum__content">
        @forelse($hotForumTopics as $topic)
            @php($replies = max(0, $topic->posts_count - 1))
            <a class="homepage-forum__topic" href="{{ route('forum.topic', $topic->id) }}" title="{{ $topic->title }}">
                <span class="homepage-forum__title">{{ $topic->title }}</span>
                <span class="homepage-forum__meta">
                    <span>{{ $replies > 0 ? trans_choice('homepage.replies', $replies, ['count' => $replies]) : __('homepage.no_replies') }}</span>
                    <time datetime="{{ ($topic->last_post_at ?? $topic->created_at)?->toIso8601String() }}">{{ ($topic->last_post_at ?? $topic->created_at)?->format('d.m H:i') }}</time>
                </span>
            </a>
        @empty
            <p class="homepage-forum__empty">{{ __('homepage.no_forum_topics') }}</p>
        @endforelse
        <a class="homepage-forum__all" href="{{ route('forum.index') }}">{{ __('homepage.all_forum_topics') }} »</a>
    </div>
</section>
