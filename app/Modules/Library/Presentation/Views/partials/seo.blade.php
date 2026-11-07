<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
<meta name="keywords" content="{{ $seo->heading }}, {{ __('homepage.seo_keywords') }}">
<meta name="robots" content="{{ $seo->robots }}">
<link rel="canonical" href="{{ $seo->canonical }}">
@foreach($seo->alternates as $language => $url)
    <link rel="alternate" hreflang="{{ $language }}" href="{{ $url }}">
@endforeach
@if($seo->alternates)
    <link rel="alternate" hreflang="x-default" href="{{ $seo->alternates['ru'] ?? array_values($seo->alternates)[0] }}">
@endif
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:site_name" content="{{ __('library.brand') }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:image" content="{{ $seo->image }}">
<meta property="og:image:alt" content="{{ $seo->heading }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'uk' ? 'uk_UA' : 'ru_RU' }}">
@foreach($seo->alternates as $language => $url)
    @if($language !== app()->getLocale())
        <meta property="og:locale:alternate" content="{{ $language === 'uk' ? 'uk_UA' : 'ru_RU' }}">
    @endif
@endforeach
@if($seo->publishedAt)<meta property="article:published_time" content="{{ $seo->publishedAt }}">@endif
@if($seo->modifiedAt)<meta property="article:modified_time" content="{{ $seo->modifiedAt }}">@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
<meta name="twitter:image" content="{{ $seo->image }}">
{{-- Дублинское ядро (Dublin Core) --}}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/">
<meta name="DC.title" content="{{ $seo->title }}">
<meta name="DC.description" content="{{ $seo->description }}">
<meta name="DC.subject" content="{{ $seo->heading }}">
<meta name="DC.language" content="{{ app()->getLocale() }}">
<meta name="DC.creator" content="{{ __('homepage.game_name') }} (Eternum)">
<meta name="DC.publisher" content="{{ __('homepage.game_name') }} (Eternum)">
<meta name="DC.type" content="{{ $seo->type === 'article' ? 'Text' : 'Collection' }}">
<meta name="DC.format" content="text/html">
<meta name="DC.identifier" content="{{ $seo->canonical }}">
@if($seo->publishedAt)<meta name="DC.date" content="{{ $seo->publishedAt }}">@endif
