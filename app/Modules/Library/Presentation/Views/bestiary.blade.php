@extends('library::layout')

@section('title', $selectedCategory->name)
@section('panel-title', $selectedCategory->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/library_bestiary.css') }}?v={{ filemtime(public_path('css/library_bestiary.css')) }}">
@endpush

@section('content')
    <div class="library-breadcrumbs">
        <a href="{{ route('library.index') }}">Библиотека</a> → {{ $selectedCategory->name }}
    </div>

    @if($selectedCategory->description)
        <p class="library-bestiary-description">{{ $selectedCategory->description }}</p>
    @endif

    <form class="library-bestiary-filters" method="get" action="{{ route('library.index') }}">
        <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">

        <div class="library-bestiary-filters__row">
            <label class="library-bestiary-filters__search">
                <span>Название</span>
                <input class="library-search-field" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Название монстра">
            </label>
            <label>
                <span>Уровень от</span>
                <input class="library-search-field" type="number" name="level_from" value="{{ $filters['level_from'] ?? '' }}" min="1" max="10000">
            </label>
            <label>
                <span>до</span>
                <input class="library-search-field" type="number" name="level_to" value="{{ $filters['level_to'] ?? '' }}" min="1" max="10000">
            </label>
        </div>

        <div class="library-bestiary-filters__row">
            <label>
                <span>Карта</span>
                <select class="library-search-field" name="map_id">
                    <option value="">Все карты</option>
                    @foreach($maps as $map)
                        <option value="{{ $map->id }}" @selected((int) ($filters['map_id'] ?? 0) === $map->id)>{{ $map->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Номер локации</span>
                <input class="library-search-field"
                       type="number"
                       name="location_id"
                       value="{{ $filters['location_id'] ?? '' }}"
                       min="1"
                       placeholder="Например: 6">
            </label>
            <label>
                <span>Тип</span>
                <select class="library-search-field" name="boss">
                    <option value="all" @selected(($filters['boss'] ?? 'all') === 'all')>Все</option>
                    <option value="0" @selected(($filters['boss'] ?? 'all') === '0')>Обычные</option>
                    <option value="1" @selected(($filters['boss'] ?? 'all') === '1')>Боссы</option>
                </select>
            </label>
        </div>

        <div class="library-actions">
            <a class="library-game-button" href="{{ route('library.index', ['category' => $selectedCategory->slug]) }}">Сбросить</a>
            <button class="library-game-button" type="submit">Найти</button>
        </div>
    </form>

    <div class="library-bestiary-summary">
        Найдено монстров: <b>{{ $monsters->total() }}</b>
    </div>

    <div class="library-bestiary-grid">
        @forelse($monsters as $monster)
            @php
                $locationNames = $monster->locations->pluck('name')->unique()->values();
                $mapNames = $monster->locations->pluck('map.name')->filter()->unique()->values();
            @endphp
            <a class="library-bestiary-card {{ $monster->is_boss ? 'library-bestiary-card--boss' : '' }}"
               href="{{ route('info.monster.catalog', ['id' => $monster->id]) }}"
               data-library-info-popup>
                <span class="library-bestiary-card__portrait">
                    @if($monster->image)
                        <img src="{{ asset($monster->image) }}" alt="{{ $monster->name }}">
                    @else
                        <span class="library-bestiary-card__empty">?</span>
                    @endif
                    <span class="library-bestiary-card__level">{{ $monster->lvl }} ур.</span>
                </span>
                <span class="library-bestiary-card__body">
                    <span class="library-bestiary-card__heading">
                        <strong>{{ $monster->name }}</strong>
                        @if($monster->is_boss)<em>Босс</em>@endif
                    </span>
                    <span class="library-bestiary-card__stats">
                        <span><b>HP:</b> {{ format_money($monster->hp) }}</span>
                        <span><b>Урон:</b> {{ format_money($monster->min_dmg) }}–{{ format_money($monster->max_dmg) }}</span>
                        <span><b>Броня:</b> {{ format_money($monster->armor) }}</span>
                        <span><b>Атака:</b> {{ $monster->attack_type?->label() ?? 'Физическая' }}</span>
                    </span>
                    <span class="library-bestiary-card__habitat">
                        <b>Обитает:</b>
                        @if($locationNames->isNotEmpty())
                            {{ $locationNames->take(3)->implode(', ') }}@if($locationNames->count() > 3) и ещё {{ $locationNames->count() - 3 }}@endif
                        @else
                            местонахождение неизвестно
                        @endif
                    </span>
                    @if($mapNames->isNotEmpty())
                        <span class="library-bestiary-card__maps">{{ $mapNames->implode(', ') }}</span>
                    @endif
                </span>
            </a>
        @empty
            <div class="library-empty library-bestiary-empty">По заданным условиям монстры не найдены.</div>
        @endforelse
    </div>

    <div class="library-pagination">{{ $monsters->links('library::pagination') }}</div>
@endsection
