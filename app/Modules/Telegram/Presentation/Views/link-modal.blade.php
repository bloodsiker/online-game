<div class="telegram-overlay" id="telegram-link-overlay" hidden></div>
<div class="telegram-modal" id="telegram-link-modal" role="dialog" aria-modal="true" aria-labelledby="telegram-link-title" hidden
     data-translations="{{ json_encode([
         'preparing' => __('telegram.preparing'), 'failed' => __('telegram.link_failed'),
         'connection_failed' => __('ui.connection_failed'), 'session_expired' => __('ui.session_expired'),
         'login_again' => __('ui.login_again'), 'too_many_attempts' => __('ui.too_many_attempts'),
     ]) }}">
    <div class="popup_global_container">
        <div class="popup-top-left">
            <div class="popup-top-right"><div class="popup-top-center">
                <div class="popup_global_title" id="telegram-link-title">{{ __('telegram.connect') }}</div>
            </div></div>
            <button class="popup_global_close_btn" type="button" data-telegram-close aria-label="{{ __('ui.close') }}" style="border:0;padding:0;"></button>
        </div>
        <div class="popup-left-center"><div class="popup-right-center">
            <div class="popup_global_content">
                <p id="telegram-link-message" role="status" aria-live="polite" hidden></p>
                <div id="telegram-link-ready" hidden>
                    <p>{{ __('telegram.link_instructions', ['minutes' => config('telegram.link_ttl_minutes')]) }}</p>
                    <p class="telegram-modal__link"><a id="telegram-bot-link" @if(session('telegram_link')) href="{{ session('telegram_link') }}" @endif target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer">{{ __('telegram.open_bot') }}</a></p>
                    <p>{{ __('telegram.refresh_hint') }}</p>
                </div>
                <div class="telegram-modal__actions">
                    <span class="butt1 pointer"><span><input type="button" value="{{ __('ui.close') }}" data-telegram-close></span></span>
                </div>
            </div>
        </div></div>
        <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
    </div>
</div>
