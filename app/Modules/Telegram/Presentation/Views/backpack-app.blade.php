<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('telegram.backpack') }}</title>
    <link rel="stylesheet" href="{{ asset('css/telegram-backpack-mini-app.css') }}?v={{ filemtime(public_path('css/telegram-backpack-mini-app.css')) }}">
    <script src="https://telegram.org/js/telegram-web-app.js?64"></script>
    @vite('resources/js/mini-app/game.js')
</head>
<body>
<main class="telegram-backpack" id="telegram-backpack-app" data-endpoint="{{ route('telegram.mini-app.backpack', absolute: false) }}"
      data-character-endpoint="{{ route('telegram.mini-app.character', absolute: false) }}" data-auction-endpoint="{{ route('telegram.mini-app.auction', absolute: false) }}"
      data-chat-messages="{{ route('telegram.mini-app.chat.messages', absolute: false) }}" data-chat-send="{{ route('telegram.mini-app.chat.send', absolute: false) }}"
      data-chat-auth="{{ route('telegram.mini-app.chat.auth', absolute: false) }}" data-chat-realtime="{{ Vite::asset('resources/js/mini-app/chat-realtime.js') }}"
      data-reputations-endpoint="{{ route('telegram.mini-app.reputations', absolute: false) }}" data-influences-endpoint="{{ route('telegram.mini-app.influences', absolute: false) }}"
      data-online-endpoint="{{ route('telegram.mini-app.online', absolute: false) }}" data-online-count-endpoint="{{ route('telegram.mini-app.online-count', absolute: false) }}"
      data-default-avatar="{{ asset('img/avatar/dark_elf.jpg') }}"
      data-translations="{{ json_encode($labels) }}" data-default-image="{{ asset('img/bg/empty_slot.gif') }}">
    <div class="telegram-backpack__toolbar" id="telegram-backpack-toolbar">
        <button class="telegram-online-banner" type="button" id="telegram-backpack-online-open" data-view="online">
            <span><i aria-hidden="true"></i><span data-telegram-label="profile_online_count">{{ __('telegram.profile_online_count') }}</span></span>
            <b id="telegram-backpack-online-count">—</b><span aria-hidden="true">›</span>
        </button>
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
    <nav class="telegram-profile__tabs" id="telegram-backpack-profile-tabs" aria-label="{{ __('telegram.character') }}" hidden>
        <button type="button" data-profile-view="character" data-telegram-label="profile_back">{{ __('telegram.profile_back') }}</button>
        <button type="button" data-profile-view="reputations" data-telegram-label="profile_reputations">{{ __('telegram.profile_reputations') }}</button>
        <button type="button" data-profile-view="influences" data-telegram-label="profile_influences">{{ __('telegram.profile_influences') }}</button>
    </nav>
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
                <img class="telegram-profile__avatar" id="telegram-backpack-profile-avatar" width="128" height="180" alt="">
                <div><h2 id="telegram-backpack-profile-name"></h2>
                <p id="telegram-backpack-profile-race"></p>
                <p><span data-telegram-label="mini_app_level">{{ __('telegram.mini_app_level') }}</span> <b id="telegram-backpack-profile-level"></b></p></div>
            </div>
            <div class="telegram-profile__balances">
                <div><span data-telegram-label="mini_app_money">{{ __('telegram.mini_app_money') }}</span><b><img src="{{ asset('img/icon/m_game.gif') }}" width="14" height="14" alt=""> <span id="telegram-backpack-profile-money"></span></b></div>
                <div><span data-telegram-label="mini_app_diamonds">{{ __('telegram.mini_app_diamonds') }}</span><b><img src="{{ asset('img/icon/m_dmd.gif') }}" width="14" height="14" alt=""> <span id="telegram-backpack-profile-diamonds"></span></b></div>
            </div>
            <div class="telegram-profile__card" id="telegram-backpack-profile-resources"></div>
            <section class="telegram-profile__card"><h2 data-telegram-label="profile_stats">{{ __('telegram.profile_stats') }}</h2><dl class="telegram-profile__stats" id="telegram-backpack-profile-stats"></dl></section>
            <section class="telegram-profile__skills" id="telegram-backpack-profile-skills"></section>
        </div>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-reputations" aria-labelledby="telegram-backpack-title" hidden>
        <div class="telegram-progressions" id="telegram-backpack-reputations-list"></div>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-influences" aria-labelledby="telegram-backpack-title" hidden>
        <div class="telegram-progressions" id="telegram-backpack-influences-list"></div>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-online" aria-labelledby="telegram-backpack-title" hidden>
        <div class="telegram-online-list" id="telegram-backpack-online-list"></div>
        <div class="telegram-market__pagination" id="telegram-backpack-online-pagination" hidden>
            <button type="button" id="telegram-backpack-online-previous" data-telegram-label="previous" disabled>{{ __('telegram.previous') }}</button>
            <span id="telegram-backpack-online-page" role="status" aria-live="polite"></span>
            <button type="button" id="telegram-backpack-online-next" data-telegram-label="next" disabled>{{ __('telegram.next') }}</button>
        </div>
    </section>
    <section class="telegram-backpack__panel" id="telegram-backpack-auction" aria-labelledby="telegram-backpack-title" hidden>
        <nav class="telegram-market__tabs" aria-label="{{ __('telegram.mini_app_auction') }}">
            <button type="button" data-auction-scope="all" aria-pressed="true" data-telegram-label="auction_all">{{ __('telegram.auction_all') }}</button>
            <button type="button" data-auction-scope="mine" aria-pressed="false" data-telegram-label="auction_mine">{{ __('telegram.auction_mine') }}</button>
        </nav>
        <form class="telegram-market__filters" id="telegram-backpack-auction-filters">
            <label class="telegram-market__search"><span data-telegram-label="auction_search">{{ __('telegram.auction_search') }}</span><input type="search" name="name" maxlength="100" autocomplete="off"></label>
            <label><span data-telegram-label="auction_type">{{ __('telegram.auction_type') }}</span><select name="type"><option value="" data-telegram-label="auction_any_type">{{ __('telegram.auction_any_type') }}</option></select></label>
            <label><span data-telegram-label="auction_rarity">{{ __('telegram.auction_rarity') }}</span><select name="rarity"><option value="" data-telegram-label="auction_any_rarity">{{ __('telegram.auction_any_rarity') }}</option></select></label>
            <div class="telegram-market__filter-actions">
                <button type="submit" data-telegram-label="auction_apply">{{ __('telegram.auction_apply') }}</button>
                <button type="reset" data-telegram-label="auction_reset">{{ __('telegram.auction_reset') }}</button>
            </div>
        </form>
        <div class="telegram-market__lots" id="telegram-backpack-lots"></div>
        <p class="telegram-backpack__empty" id="telegram-backpack-lots-empty" hidden data-telegram-label="mini_app_no_lots">{{ __('telegram.mini_app_no_lots') }}</p>
        <div class="telegram-market__pagination" id="telegram-backpack-lots-pagination" hidden>
            <button type="button" id="telegram-backpack-lots-previous" data-telegram-label="previous" disabled>{{ __('telegram.previous') }}</button>
            <span id="telegram-backpack-lots-page" role="status" aria-live="polite"></span>
            <button type="button" id="telegram-backpack-lots-next" data-telegram-label="next" disabled>{{ __('telegram.next') }}</button>
        </div>
    </section>
    <section class="telegram-chat" id="telegram-backpack-chat" aria-labelledby="telegram-backpack-title" hidden>
        <div class="telegram-chat__toolbar">
            <select id="telegram-chat-channel" aria-label="{{ __('telegram.chat_title') }}"></select>
            <span id="telegram-chat-status" role="status"></span>
        </div>
        <div class="telegram-chat__messages" id="telegram-chat-messages" role="log" aria-live="polite" aria-relevant="additions"></div>
        <p class="telegram-chat__notice" id="telegram-chat-empty" hidden></p>
        <p class="telegram-chat__error" id="telegram-chat-error" role="alert" hidden></p>
        <p class="telegram-chat__notice" id="telegram-chat-mute" hidden></p>
        <form class="telegram-chat__composer" id="telegram-chat-form">
            <label class="telegram-chat__input-label" for="telegram-chat-input" data-telegram-label="chat_reply_hint">{{ __('telegram.chat_reply_hint') }}</label>
            <div><textarea id="telegram-chat-input" maxlength="500" rows="2" aria-label="{{ __('telegram.chat_placeholder') }}" placeholder="{{ __('telegram.chat_placeholder') }}"></textarea>
                <button type="submit" id="telegram-chat-send" data-telegram-label="chat_send">{{ __('telegram.chat_send') }}</button></div>
        </form>
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
        <button type="button" data-view="chat">
            <span class="telegram-app-navigation__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11a8 8 0 0 1-8 8H7l-5 3V11a9 9 0 0 1 19 0Z"/><path d="M7 10h9M7 14h6"/></svg></span>
            <span data-telegram-label="chat_title">{{ __('telegram.chat_title') }}</span>
        </button>
    </nav>
</main>
<dialog class="telegram-backpack-detail" id="telegram-backpack-detail" aria-labelledby="telegram-backpack-detail-name">
    <div class="telegram-backpack-detail__picture"><img id="telegram-backpack-detail-image" width="100" height="100" alt=""></div>
    <h2 id="telegram-backpack-detail-name"></h2>
    <p class="telegram-backpack-detail__description" id="telegram-backpack-detail-description" hidden></p>
    <p id="telegram-backpack-detail-quantity"></p>
    <p id="telegram-backpack-detail-type"></p>
    <p id="telegram-backpack-detail-upgrade" hidden></p>
    <button type="button" id="telegram-backpack-detail-close">{{ __('ui.close') }}</button>
</dialog>
</body>
</html>
