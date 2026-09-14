@extends('library.layout')

@section('title', $selectedCategory?->name ?? 'Все статьи')
@section('panel-title', $selectedCategory?->name ?? 'Библиотека')

@section('content')
    @if(!$selectedCategory && !request()->filled('q'))
        <div class="library-home-grid">
            @forelse($categories as $category)
                <a class="library-home-tile {{ $category->image ? '' : 'library-home-tile--empty' }}" href="{{ route('library.index', ['category' => $category->slug]) }}">
                    @if($category->image)<img src="{{ asset($category->image) }}" alt="{{ $category->name }}">@endif
                    <span>{{ $category->name }}</span>
                </a>
            @empty
                <div class="library-empty">Библиотека пока не наполнена.</div>
            @endforelse
        </div>
    @else
        <div class="library-breadcrumbs">
            <a href="{{ route('library.index') }}">Библиотека</a>
            @if($selectedCategory) → {{ $selectedCategory->name }} @endif
        </div>

        @if($selectedCategory?->description)<p>{{ $selectedCategory->description }}</p>@endif

        <form method="get">
            @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory->slug }}">@endif
            <table class="library-search-table"><tr><td><b>Название или текст</b><br><input class="library-search-field" name="q" value="{{ request('q') }}"></td></tr></table>
            <div class="library-actions">
                @if(request()->filled('q'))<a class="library-game-button" href="{{ $selectedCategory ? route('library.index', ['category' => $selectedCategory->slug]) : route('library.index') }}">Сбросить</a>@endif
                <button class="library-game-button">Найти</button>
            </div>
        </form>

        <table class="library-table">
            <colgroup><col width="78"><col><col width="130"></colgroup>
            <thead><tr><th>Изображение</th><th>Название</th><th>Раздел</th></tr></thead>
            <tbody>
            @forelse($articles as $article)
                <tr>
                    <td align="center"><a href="{{ route('library.show', $article->slug) }}">@if($article->cover_image)<img class="library-list-image" src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">@else<span class="library-muted">нет</span>@endif</a></td>
                    <td>
                        <a class="library-list-title" href="{{ route('library.show', $article->slug) }}">{{ $article->title }}</a>
                        @if($article->excerpt)<div class="library-list-excerpt">{{ $article->excerpt }}</div>@endif
                        <div class="library-muted">Просмотров: {{ $article->views_count }}</div>
                    </td>
                    <td align="center"><b>{{ $article->category?->name }}</b></td>
                </tr>
            @empty
                <tr><td colspan="3" class="library-empty">Материалы не найдены.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="library-pagination">{{ $articles->links('library.pagination') }}</div>
    @endif
@endsection
