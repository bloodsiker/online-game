@extends('forum::layout')

@section('title', 'Поиск по форуму')
@section('panel-title', 'Поиск')

@section('content')
    <div class="forum-page">
        @include('forum::_heading', ['searchQuery' => $query])

        <div class="forum-line2-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>
        <nav class="forum-breadcrumbs" aria-label="Хлебные крошки">
            <a href="{{ route('forum.index') }}">Форум</a>
            <span aria-hidden="true">→</span>
            <span>Результаты поиска</span>
        </nav>
        <div class="forum-line2-separator forum-line2-separator--bottom" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>

        @if($topics->isEmpty())
            <div class="forum-empty">По запросу «{{ $query }}» ничего не найдено.</div>
        @else
            <div class="forum-search-summary">Результаты поиска по запросу: <b>«{{ $query }}»</b></div>
            <div class="forum-topics">
                @foreach($topics as $topic)
                    @include('forum::_topic-row', ['topic' => $topic])
                @endforeach
            </div>
            @include('forum::_pagination', ['paginator' => $topics])
        @endif
    </div>
@endsection
