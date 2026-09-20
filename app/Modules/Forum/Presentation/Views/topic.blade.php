@extends('forum::layout')

@section('title', $topic->title.' — Форум')
@section('panel-title', $topic->section->name)

@section('content')
    <div class="forum-page">
        @if(session('message'))
            <div class="forum-flash">{{ session('message') }}</div>
        @endif

        @include('forum::_heading')

        <div class="forum-topic-summary">
            <div class="forum-topic-rating">
                <img class="forum-topic-rating__crown" src="{{ asset('main/images/theme_old/tcrown0.png') }}" width="15" height="13" alt="">
                <form action="{{ route('forum.vote', ['id' => $topic->id]) }}" method="post" class="forum-topic-rating__control">
                    @csrf
                    <button class="forum-topic-rating__button forum-topic-rating__button--up {{ $userVote === 1 ? 'is-active' : '' }}" type="submit" name="value" value="1" title="Повысить рейтинг" @guest disabled @endguest @disabled($userVote === 1)>+</button>
                    <span class="forum-topic-rating__value">{{ $topic->rating }}</span>
                    <button class="forum-topic-rating__button forum-topic-rating__button--down {{ $userVote === -1 ? 'is-active' : '' }}" type="submit" name="value" value="-1" title="Понизить рейтинг" @guest disabled @endguest @disabled($userVote === -1)>−</button>
                </form>
            </div>

            <div class="forum-topic-summary__content">
                <div>
                    @if($topic->is_locked)<span class="forum-topic-state">[закрыто]</span>@endif
                    <b class="forum-topic-title {{ $topic->is_pinned ? 'is-pinned' : '' }}">{{ $topic->title }}</b>
                </div>
                <div class="forum-topic-meta">Просмотров: <b>{{ $topic->views_count }}</b> &nbsp; Сообщений: <b>{{ $topic->posts_count }}</b></div>

                @if($canModerate)
                    <div class="forum-topic-summary__actions">
                        <form action="{{ route('forum.topic.pin', ['id' => $topic->id]) }}" method="post">
                            @csrf
                            <button type="submit" class="forum-mini">{{ $topic->is_pinned ? 'Открепить' : 'Закрепить' }}</button>
                        </form>
                        <form action="{{ route('forum.topic.lock', ['id' => $topic->id]) }}" method="post">
                            @csrf
                            <button type="submit" class="forum-mini">{{ $topic->is_locked ? 'Открыть' : 'Закрыть' }}</button>
                        </form>
                        <form action="{{ route('forum.topic.destroy', ['id' => $topic->id]) }}" method="post" onsubmit="return confirm('Удалить тему со всеми сообщениями?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="forum-mini danger">Удалить</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="forum-posts">
            @foreach($posts as $post)
                @php
                    $postNumber = ($posts->firstItem() ?? 1) + $loop->index;
                    $postUrlParameters = ['id' => $topic->id];
                    if ($posts->currentPage() > 1) {
                        $postUrlParameters['page'] = $posts->currentPage();
                    }
                    $postUrl = route('forum.topic', $postUrlParameters).'#post-'.$post->id;
                @endphp
                <article class="forum-post" id="post-{{ $post->id }}">
                    <header class="forum-post-head">
                        <span class="forum-post-author-block">
                            @php($authorClan = $post->author?->clanMembership?->clan)
                            @if($authorClan?->icon)
                                <a class="forum-author-clan" href="{{ route('clan.public', ['clan' => $authorClan->id]) }}" target="_blank" title="Информация о клане {{ $authorClan->name }}">
                                    <img src="{{ Storage::disk('public')->url($authorClan->icon) }}" width="13" height="13" alt="{{ $authorClan->name }}">
                                </a>
                            @endif
                            @if($post->author)
                                <a class="forum-post-author" href="{{ route('info.user', ['id' => $post->author->id]) }}" target="_blank"><b>{{ $post->author_name }}</b></a>
                            @else
                                <b class="forum-post-author">{{ $post->author_name }}</b>
                            @endif
                            @if($post->author?->player?->lvl)<span class="forum-post-level">[{{ $post->author->player->lvl }}]</span>@endif
                            @if($post->author)
                                <a class="forum-author-info" href="{{ route('info.user', ['id' => $post->author->id]) }}" target="_blank" title="Информация о персонаже">
                                    <img class="forum-user-info-icon" src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" alt="i">
                                </a>
                            @endif
                        </span>
                        <span class="forum-post-date">{{ $post->created_at->format('d.m.Y H:i') }}</span>
                        <a class="forum-post-permalink" href="{{ $postUrl }}" data-copy-post-link title="Скопировать ссылку на сообщение" aria-label="Скопировать ссылку на сообщение № {{ $postNumber }}">#{{ $postNumber }}</a>
                        @if(!$post->created_at->equalTo($post->updated_at))<span class="forum-post-edited">(изменено)</span>@endif
                    </header>
                    <div class="forum-post-body">{!! $post->body !!}</div>

                    @auth
                        @if(auth()->id() === (int) $post->user_id || $canModerate)
                            <div class="forum-post-tools">
                                <a href="{{ route('forum.post.edit', ['id' => $post->id]) }}">Изменить</a>
                                <form action="{{ route('forum.post.destroy', ['id' => $post->id]) }}" method="post" onsubmit="return confirm('Удалить сообщение?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="forum-link-button">Удалить</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </article>
            @endforeach
        </div>

        @include('forum::_pagination', ['paginator' => $posts])

        <div class="forum-news-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-l.gif') }}" width="130" height="26" alt="">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-r.gif') }}" width="130" height="26" alt="">
        </div>

        @auth
            @if($forumMute)
                @include('forum::_mute-notice')
            @elseif($topic->is_locked)
                <p class="forum-locked-note">Тема закрыта, ответы отключены.</p>
            @elseif(!$canComment)
                <p class="forum-locked-note">Комментарии в этом разделе отключены.</p>
            @else
                <div class="forum-form-box">
                    <h3 class="forum-form-box__title">Добавить комментарий:</h3>
                    <form action="{{ route('forum.post.store', ['id' => $topic->id]) }}" method="post">
                        @csrf
                        <div class="forum-field">
                            <textarea id="forum-editor" name="body">{{ old('body') }}</textarea>
                            @error('body')<div class="forum-error">{{ $message }}</div>@enderror
                        </div>
                        <span class="butt1 pointer forum-game-button">
                            <span><button class="butt1 shop" type="submit" style="width: 144px;">Отправить</button></span>
                        </span>
                    </form>
                </div>
                <div class="forum-news-separator" aria-hidden="true">
                    <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-l.gif') }}" width="130" height="26" alt="">
                    <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-r.gif') }}" width="130" height="26" alt="">
                </div>
                @include('forum::_editor')
            @endif
        @else
            <p class="forum-login-hint"><a href="{{ route('index') }}">Войдите</a>, чтобы отвечать.</p>
        @endauth
    </div>

    <script>
        (function () {
            function fallbackCopy(text) {
                var field = document.createElement('textarea');
                field.value = text;
                field.setAttribute('readonly', '');
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('[data-copy-post-link]');
                if (!link) return;

                event.preventDefault();
                var copy = navigator.clipboard && window.isSecureContext
                    ? navigator.clipboard.writeText(link.href).catch(function () {
                        fallbackCopy(link.href);
                    })
                    : Promise.resolve(fallbackCopy(link.href));

                copy.then(function () {
                    link.classList.add('is-copied');
                    window.setTimeout(function () {
                        link.classList.remove('is-copied');
                    }, 1400);
                });
            });
        })();
    </script>
@endsection
