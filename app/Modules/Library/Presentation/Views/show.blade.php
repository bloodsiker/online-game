@extends('library::layout')

@section('title', $article->title)
@section('panel-title', $article->title)

@section('panel-header')
    <span
        class="library-header-select default-select"
        data-library-header-select
        role="combobox"
        aria-label="{{ __('library.navigation') }}"
        aria-controls="library-article-options"
        aria-expanded="false"
        tabindex="0"
    >
        <span class="wrap_inner">
            <select class="library-header-native-select" aria-label="{{ __('library.navigation') }}" tabindex="-1">
                @foreach($articleNavigation as $navigationArticle)
                    <option value="{{ $libraryUrls->article(slug: $navigationArticle->slug, categorySlug: $navigationArticle->category->slug) }}" @selected($navigationArticle->id === $article->id)>
                        @if($navigationArticle->category_id !== $selectedCategory->id){{ $navigationArticle->category->name }} — @endif{{ $navigationArticle->title }}
                    </option>
                @endforeach
            </select>
        </span>
        <span class="value">{{ $article->title }}</span>
        <span class="button" aria-hidden="true"><span class="button_inner"></span></span>
        <span class="dropdown" id="library-article-options" role="listbox">
            <span class="dropdown_list">
                <span class="dropdown_list_inner">
                    @foreach($articleNavigation as $navigationArticle)
                        <a
                            class="option @if($navigationArticle->id === $article->id) option_selected @endif"
                            href="{{ $libraryUrls->article(slug: $navigationArticle->slug, categorySlug: $navigationArticle->category->slug) }}"
                            role="option"
                            aria-selected="{{ $navigationArticle->id === $article->id ? 'true' : 'false' }}"
                        >@if($navigationArticle->category_id !== $selectedCategory->id){{ $navigationArticle->category->name }} — @endif{{ $navigationArticle->title }}</a>
                    @endforeach
                </span>
            </span>
        </span>
    </span>
@endsection

@section('tooltip-script')
    {!! $itemTooltipScript !!}
@endsection

@section('content')
    <div class="library-breadcrumbs">
        <a href="{{ $libraryUrls->index() }}">{{ __('library.name') }}</a> →
        <a href="{{ $libraryUrls->index(parameters: ['category' => $selectedCategory->slug]) }}">{{ $selectedCategory->name }}</a> →
        {{ $article->title }}
    </div>

    @if($article->cover_image)<img class="library-cover" src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">@endif
    <article class="library-article-content">{!! $article->rendered_content !!}</article>

    @if($linkedEntities->isNotEmpty())
        <div class="library-related">
            <b>{{ __('library.related') }}</b><br>
            @foreach($linkedEntities as $entity)<span class="library-badge">{{ $entity['type'] }}: {{ $entity['label'] }}</span>@endforeach
        </div>
    @endif

    <div class="library-muted" style="margin-top:15px">
        {{ __('library.published') }} {{ $article->published_at?->format('d.m.Y H:i') }} · {{ __('library.views') }} {{ $article->views_count }}
    </div>
@endsection
