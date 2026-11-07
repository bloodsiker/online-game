{{--
    SEO-теги публичной страницы: title, description, keywords, canonical, Open Graph, Twitter, Dublin Core.

    @include('partials.seo', [
        'title' => '…',            // 10–70 символов
        'description' => '…',      // 70–160 символов
        'keywords' => '…',         // необязательно, по умолчанию — ключевые слова игры
        'image' => asset('…'),     // необязательно, по умолчанию img/og-image.jpg (1200×630)
        'url' => '…',              // необязательно, по умолчанию текущий адрес без query
        'type' => 'website',       // og:type: website | article | profile
        'alternates' => ['ru' => url, 'uk' => url],  // необязательно, hreflang
        'noindex' => false,
    ])
--}}
@php
    $seoSite = __('homepage.game_name').' (Eternum)';
    $seoTitle = trim((string) ($title ?? __('homepage.seo_title')));
    $seoDescription = trim((string) \Illuminate\Support\Str::limit(strip_tags((string) ($description ?? __('homepage.seo_description'))), 157, '…'));
    $seoKeywords = trim((string) ($keywords ?? __('homepage.seo_keywords')));
    $seoImage = $image ?? asset('img/og-image.jpg');
    $seoUrl = $url ?? url()->current();
    $seoType = $type ?? 'website';
    $seoLocale = app()->getLocale();
    $seoOgLocale = $seoLocale === 'uk' ? 'uk_UA' : 'ru_RU';
@endphp
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<link rel="canonical" href="{{ $seoUrl }}">
@if (! empty($noindex))
<meta name="robots" content="noindex, follow">
@else
<meta name="robots" content="index, follow, max-image-preview:large">
@endif
@foreach (($alternates ?? []) as $altLocale => $altUrl)
<link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}">
@endforeach

<meta property="og:site_name" content="{{ $seoSite }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $seoTitle }}">
<meta property="og:locale" content="{{ $seoOgLocale }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">

{{-- Дублинское ядро (Dublin Core) --}}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/">
<meta name="DC.title" content="{{ $seoTitle }}">
<meta name="DC.description" content="{{ $seoDescription }}">
<meta name="DC.subject" content="{{ $seoKeywords }}">
<meta name="DC.language" content="{{ $seoLocale }}">
<meta name="DC.creator" content="{{ $seoSite }}">
<meta name="DC.publisher" content="{{ $seoSite }}">
<meta name="DC.type" content="InteractiveResource">
<meta name="DC.format" content="text/html">
<meta name="DC.identifier" content="{{ $seoUrl }}">
