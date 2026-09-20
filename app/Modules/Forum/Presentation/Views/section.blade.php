@extends('forum::layout')

@section('title', $section->name.' — Форум')
@section('panel-title', 'Темы')

@section('content')
    <div class="forum-page">
        @if(session('message'))
            <div class="forum-flash">{{ session('message') }}</div>
        @endif

        @include('forum::_heading')

        <div class="forum-line2-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>
        <nav class="forum-breadcrumbs" aria-label="Хлебные крошки">
            <a href="{{ route('forum.index') }}">Форум</a>
            @if($section->parent)
                <span aria-hidden="true">→</span>
                <span class="forum-breadcrumbs__parent">{{ $section->parent->name }}</span>
            @endif
            <span aria-hidden="true">→</span>
            <span>{{ $section->name }}</span>
        </nav>
        <div class="forum-line2-separator forum-line2-separator--bottom" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>

        @if($section->description)
            <div class="forum-notice">{{ $section->description }}</div>
        @endif

        <div class="forum-topics">
            @forelse($topics as $topic)
                @include('forum::_topic-row', ['topic' => $topic])
            @empty
                <div class="forum-empty">В этом разделе пока нет тем.</div>
            @endforelse
        </div>

        @include('forum::_pagination', ['paginator' => $topics])

        <div class="forum-news-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-l.gif') }}" width="130" height="26" alt="">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-r.gif') }}" width="130" height="26" alt="">
        </div>

        @auth
            @if($forumMute)
                @include('forum::_mute-notice')
            @elseif(!$canCreateTopic)
                <p class="forum-locked-note">Создание новых тем в этом разделе отключено.</p>
            @else
                <div class="forum-form-box">
                    <h3 class="forum-form-box__title">Новая тема</h3>
                    <form action="{{ route('forum.topic.store', ['slug' => $section->slug]) }}" method="post">
                        @csrf
                        <div class="forum-field">
                            <input type="text" name="title" value="{{ old('title') }}" maxlength="150" placeholder="Заголовок темы" required>
                            @error('title')<div class="forum-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="forum-field">
                            <textarea id="forum-editor" name="body">{{ old('body') }}</textarea>
                            @error('body')<div class="forum-error">{{ $message }}</div>@enderror
                        </div>
                        <span class="butt1 pointer forum-game-button">
                            <span><button class="butt1 shop" type="submit" style="width: 144px;">Создать тему</button></span>
                        </span>
                    </form>
                </div>
            @endif
            <div class="forum-news-separator" aria-hidden="true">
                <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-l.gif') }}" width="130" height="26" alt="">
                <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-r.gif') }}" width="130" height="26" alt="">
            </div>
            @if(!$forumMute && $canCreateTopic)
                @include('forum::_editor')
            @endif
        @else
            <p class="forum-login-hint"><a href="{{ route('index') }}">Войдите</a>, чтобы создавать темы.</p>
        @endauth
    </div>
@endsection
