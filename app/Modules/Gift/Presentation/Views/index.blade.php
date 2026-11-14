<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ __('gift.page_title', ['name' => $user->name]) }} — {{ __('homepage.game_name') }}</title>
    @include('partials.favicons')
    <style>
        body { margin: 0; padding: 16px; background: #f1dcab; color: #6c382c; font: 12px Tahoma, Arial, sans-serif; }
        .gifts-page { max-width: 760px; margin: 0 auto; }
        .gifts-page h1 { margin: 0 0 6px; color: #8b2000; font: bold 18px Georgia, serif; text-align: center; }
        .gifts-page__back { display: block; margin-bottom: 12px; text-align: center; }
        .gifts-page__back a, .gifts-page nav a { color: #8b2000; }
        .gifts-page nav { margin-top: 12px; text-align: center; }
    </style>
    @include('gift::partials.styles')
    @include('gift::partials.tooltip-assets')
</head>
<body>
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>
<main class="gifts-page">
    <h1>{{ __('gift.page_title', ['name' => $user->name]) }}</h1>
    <div class="gifts-page__back"><a href="{{ route('info.user', ['id' => $user->id]) }}">{{ __('gift.back') }}</a></div>
    @if($gifts->isEmpty())
        <p style="text-align:center">{{ __('gift.empty') }}</p>
    @else
        <div class="player-gifts">@foreach($gifts as $gift)@include('gift::partials.gift-card', ['gift' => $gift])@endforeach</div>
        @if($gifts->hasPages())
            <nav>{{ $gifts->links('pagination::simple-default') }}</nav>
        @endif
    @endif
</main>
</body>
</html>
