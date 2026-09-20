@extends('library::layout')

@section('title', $selectedCategory->name)
@section('panel-title', $selectedCategory->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/library_maps.css') }}?v={{ filemtime(public_path('css/library_maps.css')) }}">
@endpush

@section('content')
    <div class="library-breadcrumbs">
        <a href="{{ route('library.index') }}">Библиотека</a> → {{ $selectedCategory->name }}
    </div>

    @if($selectedCategory->description)
        <p class="library-map-description">{{ $selectedCategory->description }}</p>
    @endif

    <form class="library-map-location-search" method="get" action="{{ route('library.index') }}">
        <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">
        <label>
            <span>Номер локации</span>
            <input class="library-search-field"
                   type="number"
                   name="location_id"
                   value="{{ $locationSearchId }}"
                   min="1"
                   placeholder="Например: 6">
        </label>
        <div class="library-map-location-search__actions">
            @if($locationSearchId)
                <a class="library-game-button" href="{{ route('library.index', ['category' => $selectedCategory->slug]) }}">Сбросить</a>
            @endif
            <button class="library-game-button" type="submit">Найти</button>
        </div>
    </form>

    @if($locationSearchId)
        @if($searchedLocation)
            <div class="library-map-location-result">
                <span class="library-map-location-result__data">
                    <strong>[{{ $searchedLocation->id }}] {{ $searchedLocation->name }}</strong>
                    <span>Карта: {{ $searchedLocation->map?->name ?? 'не указана' }}</span>
                </span>
                <span class="library-map-location-result__actions">
                    @if($searchedLocation->map?->slug)
                        <a class="library-game-button"
                           href="{{ route('map.public', ['slug' => $searchedLocation->map->slug, 'highlight_location' => $searchedLocation->id]) }}"
                           target="_blank"
                           rel="noopener">Показать на карте</a>
                    @endif
                    @if($bestiaryCategory)
                        <a class="library-game-button"
                           href="{{ route('library.index', ['category' => $bestiaryCategory->slug, 'location_id' => $searchedLocation->id]) }}">Монстры</a>
                    @endif
                </span>
            </div>
        @else
            <div class="library-map-location-result library-map-location-result--empty">
                Локация с номером {{ $locationSearchId }} не найдена.
            </div>
        @endif
    @endif

    <div class="library-map-tree-header">
        <b>Карты мира</b>
        <span class="library-map-tree-stats">
            <span>{{ $page->mapsCount }} карт</span>
            <span>{{ $page->locationsCount }} локаций</span>
        </span>
    </div>

    @if($page->roots !== [])
        <ul class="library-map-tree">
            @foreach($page->roots as $node)
                @include('library::partials.map-tree-node', ['node' => $node, 'bestiaryCategory' => $bestiaryCategory])
            @endforeach
        </ul>
    @else
        <div class="library-empty library-map-tree-empty">Карты пока не созданы.</div>
    @endif

    <div class="library-map-legend">
        Нажмите на название, чтобы открыть карту. Значок <b>⚔</b> показывает монстров выбранной карты.
    </div>
@endsection
