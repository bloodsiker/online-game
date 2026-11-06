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
      data-character-endpoint="{{ route('telegram.mini-app.character', absolute: false) }}" data-auction-endpoint="{{ route('telegram.mini-app.auction', absolute: false) }}"
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
    <section class="telegram-backpack__panel" id="telegram-backpack-inventory" aria-labelledby="telegram-backpack-title">
        <div id="telegram-backpack-items" aria-busy="true"></div>
        <p class="telegram-backpack__empty" id="telegram-backpack-empty" hidden>{{ __('telegram.mini_app_empty') }}</p>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-profile" aria-labelledby="telegram-backpack-title" hidden>
        <div id="telegram-backpack-profile-content" hidden>
            <div class="telegram-profile__identity">
                <span class="telegram-profile__icon" aria-hidden="true">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 22v-2a8 8 0 0 1 16 0v2"/></svg>
                </span>
                <h2 id="telegram-backpack-profile-name"></h2>
                <p><span data-telegram-label="mini_app_level">{{ __('telegram.mini_app_level') }}</span> <b id="telegram-backpack-profile-level"></b></p>
            </div>
            <div class="telegram-profile__balances">
                <div><span data-telegram-label="mini_app_money">{{ __('telegram.mini_app_money') }}</span><b><img src="{{ asset('img/icon/m_game.gif') }}" width="14" height="14" alt=""> <span id="telegram-backpack-profile-money"></span></b></div>
                <div><span data-telegram-label="mini_app_diamonds">{{ __('telegram.mini_app_diamonds') }}</span><b><img src="{{ asset('img/icon/m_dmd.gif') }}" width="14" height="14" alt=""> <span id="telegram-backpack-profile-diamonds"></span></b></div>
            </div>
        </div>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-auction" aria-labelledby="telegram-backpack-title" hidden>
        <h2 class="telegram-market__heading" data-telegram-label="mini_app_own_lots">{{ __('telegram.mini_app_own_lots') }}</h2>
        <div class="telegram-market__lots" id="telegram-backpack-lots"></div>
        <p class="telegram-backpack__empty" id="telegram-backpack-lots-empty" hidden data-telegram-label="mini_app_no_lots">{{ __('telegram.mini_app_no_lots') }}</p>
        <div class="telegram-market__pagination" id="telegram-backpack-lots-pagination" hidden>
            <button type="button" id="telegram-backpack-lots-previous" data-telegram-label="previous" disabled>{{ __('telegram.previous') }}</button>
            <span id="telegram-backpack-lots-page" role="status" aria-live="polite"></span>
            <button type="button" id="telegram-backpack-lots-next" data-telegram-label="next" disabled>{{ __('telegram.next') }}</button>
        </div>
    </section>
    <nav class="telegram-app-navigation" id="telegram-backpack-navigation" aria-label="{{ __('telegram.mini_app_navigation') }}">
        <button type="button" data-view="character">
            <span class="telegram-app-navigation__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></span>
            <span data-telegram-label="character">{{ __('telegram.character') }}</span>
        </button>
        <button type="button" data-view="backpack" aria-current="page">
            <span class="telegram-app-navigation__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="5" width="16" height="17" rx="4"/><path d="M8 5V4a4 4 0 0 1 8 0v1M8 14h8v5H8zM7 10h10"/></svg></span>
            <span data-telegram-label="mini_app_bag">{{ __('telegram.mini_app_bag') }}</span>
        </button>
        <button type="button" data-view="auction">
            <span class="telegram-app-navigation__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 4 6 6-4 4-6-6zM3 21l10-10M11 3l2-2 9 9-2 2M13 22h9M15 19h5v3h-5z"/></svg></span>
            <span data-telegram-label="mini_app_auction">{{ __('telegram.mini_app_auction') }}</span>
        </button>
    </nav>
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
