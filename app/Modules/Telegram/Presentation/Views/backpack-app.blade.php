<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('telegram.backpack') }}</title>
    <link rel="stylesheet" href="{{ asset('css/telegram-backpack-mini-app.css') }}?v={{ filemtime(public_path('css/telegram-backpack-mini-app.css')) }}">
    <script src="https://telegram.org/js/telegram-web-app.js?64"></script>
    <script src="{{ asset('js/telegram-backpack-mini-app.js') }}?v={{ filemtime(public_path('js/telegram-backpack-mini-app.js')) }}" defer></script>
</head>
<body>
<main class="telegram-backpack" id="telegram-backpack-app" data-endpoint="{{ route('telegram.mini-app.backpack', absolute: false) }}"
      data-translations="{{ json_encode($labels) }}" data-default-image="{{ asset('img/bg/empty_slot.gif') }}">
    <div class="telegram-backpack__toolbar" id="telegram-backpack-toolbar">
        <header class="telegram-backpack__header">
            <div><h1 id="telegram-backpack-title">{{ __('telegram.backpack') }}</h1><p id="telegram-backpack-character" hidden></p></div>
            <div class="telegram-backpack__actions">
                <button type="button" id="telegram-backpack-refresh" aria-label="{{ __('telegram.mini_app_refresh') }}" title="{{ __('telegram.mini_app_refresh') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11a8 8 0 1 0-2 6M20 4v7h-7"/></svg>
                </button>
                <button type="button" id="telegram-backpack-close" aria-label="{{ __('ui.close') }}" title="{{ __('ui.close') }}" hidden>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </header>
        <nav class="telegram-backpack__categories" id="telegram-backpack-categories" aria-label="{{ __('telegram.backpack') }}"></nav>
    </div>
    <p class="telegram-backpack__message" id="telegram-backpack-message" role="status" aria-live="polite" hidden></p>
    <p class="telegram-backpack__bot" id="telegram-backpack-bot" hidden>
        <a href="https://t.me/{{ config('telegram.bot_username') }}" target="_blank" rel="noopener noreferrer">{{ __('telegram.open_bot') }}</a>
    </p>
    <section class="telegram-backpack__panel" aria-labelledby="telegram-backpack-title">
        <div id="telegram-backpack-items" aria-busy="true"></div>
        <p class="telegram-backpack__empty" id="telegram-backpack-empty" hidden>{{ __('telegram.mini_app_empty') }}</p>
    </section>
</main>
<dialog class="telegram-backpack-detail" id="telegram-backpack-detail" aria-labelledby="telegram-backpack-detail-name">
    <div class="telegram-backpack-detail__picture"><img id="telegram-backpack-detail-image" width="80" height="80" alt=""></div>
    <h2 id="telegram-backpack-detail-name"></h2>
    <p id="telegram-backpack-detail-quantity"></p>
    <p id="telegram-backpack-detail-type"></p>
    <p id="telegram-backpack-detail-upgrade" hidden></p>
    <button type="button" id="telegram-backpack-detail-close">{{ __('ui.close') }}</button>
</dialog>
</body>
</html>
