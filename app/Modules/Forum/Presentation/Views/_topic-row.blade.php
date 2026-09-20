@php
    $lastAuthor = $topic->lastPostAuthor ?? $topic->author;
    $replyCount = max(0, (int) $topic->posts_count - 1);
    $authorClan = $topic->author?->clanMembership?->clan;
@endphp

<div class="forum-topic-row">
    <div class="forum-topic-rating">
        <img class="forum-topic-rating__crown" src="{{ asset('main/images/theme_old/tcrown0.png') }}" width="15" height="13" alt="">
        <form action="{{ route('forum.vote', ['id' => $topic->id]) }}" method="post" class="forum-topic-rating__control">
            @csrf
            <button class="forum-topic-rating__button forum-topic-rating__button--up" type="submit" name="value" value="1" title="Повысить рейтинг" @guest disabled @endguest>+</button>
            <span class="forum-topic-rating__value">{{ $topic->rating }}</span>
            <button class="forum-topic-rating__button forum-topic-rating__button--down" type="submit" name="value" value="-1" title="Понизить рейтинг" @guest disabled @endguest>−</button>
        </form>
    </div>

    <div class="forum-topic-row__body">
        <div class="forum-topic-row__top">
            <div>
                @if($topic->is_locked)<span class="forum-topic-state" title="Тема закрыта">[закрыто]</span>@endif
                <a class="forum-topic-title {{ $topic->is_pinned ? 'is-pinned' : '' }}" href="{{ route('forum.topic', ['id' => $topic->id]) }}">
                    {{ $topic->title }}
                </a>
            </div>

            <span class="forum-topic-author">
                @if($authorClan?->icon)
                    <a class="forum-author-clan" href="{{ route('clan.public', ['clan' => $authorClan->id]) }}" target="_blank" title="Информация о клане {{ $authorClan->name }}">
                        <img src="{{ Storage::disk('public')->url($authorClan->icon) }}" width="13" height="13" alt="{{ $authorClan->name }}">
                    </a>
                @endif
                <b>{{ $topic->author_name }}@if($topic->author?->player?->lvl) [{{ $topic->author->player->lvl }}]@endif</b>
                @if($topic->author)
                    <a class="forum-author-info" href="{{ route('info.user', ['id' => $topic->author->id]) }}" target="_blank" title="Информация о персонаже">
                        <img class="forum-user-info-icon" src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" alt="i">
                    </a>
                @endif
            </span>
        </div>

        <div class="forum-topic-meta">
            Просмотров: <b>{{ $topic->views_count }}</b>
            &nbsp; Ответов: <b>{{ $replyCount }}</b>
            @if($lastAuthor)
                &nbsp; Последний ответ:
                <a href="{{ route('forum.topic', ['id' => $topic->id]) }}">
                    {{ $lastAuthor->name }}@if($lastAuthor->player?->lvl) [{{ $lastAuthor->player->lvl }}]@endif
                </a>
            @endif
        </div>
    </div>
</div>
