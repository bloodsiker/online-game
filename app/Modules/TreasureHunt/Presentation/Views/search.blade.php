@php
    $treasureConfig = [
        'state' => $hunt,
        'startUrl' => route('treasure.start', $hunt['id']),
        'completeUrl' => route('treasure.complete', $hunt['id']),
        'cancelUrl' => route('treasure.cancel', $hunt['id']),
        'csrf' => csrf_token(), 'refreshTokenUrl' => route('player.csrf-token'),
        'labels' => ['retry' => __('treasure.retry'), 'search' => __('treasure.search'), 'error' => __('treasure.network_error')],
    ];
@endphp
<style>
    @foreach(['tl' => 'no-repeat', 'tr' => 'no-repeat', 'bl' => 'no-repeat', 'br' => 'no-repeat', 't' => 'repeat-x', 'b' => 'repeat-x', 'l' => 'repeat-y', 'r' => 'repeat-y'] as $part => $repeat)
    [data-treasure-search] .common-inset-{{ $part }} { background: url('{{ asset('main/images/common-inset-'.$part.'.png') }}') {{ $repeat }}; }
    @endforeach
    [data-treasure-search] .common-inset-bg { background: #ffffd6; }
    [data-treasure-search] [hidden] { display: none !important; }
    .treasure-search-modal { position: fixed; inset: 0; z-index: 9000; display: flex; align-items: center; justify-content: center; background: rgba(24,18,12,.58); }
    .treasure-search-modal__body { width: 460px; max-width: calc(100% - 24px); max-height: calc(100vh - 24px); overflow-y: auto; }
    .treasure-search-modal__content { padding: 12px 18px 8px; color: #5c351d; text-align: center; }
    .treasure-search-modal__actions { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
    .treasure-search-modal__actions button { min-width: 150px; }
    [data-treasure-message]:empty { display: none; }
    .treasure-search-reward { position: relative; width: 72px; height: 71px; margin: 10px auto; background: url('{{ asset('main/images/user-reward-frame.png') }}') no-repeat; }
    .treasure-search-reward img { position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; }
    .treasure-search-progress { position: relative; height: 31px; margin: 4px 0 8px; overflow: hidden; }
    .treasure-search-progress__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -54px repeat-x; }
    .treasure-search-progress__fill { width: 0; height: 27px; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -27px repeat-x; }
    .treasure-search-progress__border { position: absolute; inset: 0; height: 31px; pointer-events: none; }
    .treasure-search-progress__border::before, .treasure-search-progress__border::after { content: ''; position: absolute; top: 0; width: 20px; height: 31px; background: url('{{ asset('img/progressbar/progress-bar-1-border.png') }}') no-repeat; }
    .treasure-search-progress__border::before { left: 0; }
    .treasure-search-progress__border::after { right: 0; background-position: 0 -31px; }
    .treasure-search-progress__center { position: absolute; inset: 0 20px; background: url('{{ asset('img/progressbar/progress-bar-1-border.png') }}') 0 -62px repeat-x; }
    .treasure-search-progress__text { position: absolute; inset: 0; line-height: 31px; font-weight: bold; color: #fff; text-shadow: 0 1px 2px #321a11; }
</style>
<div data-treasure-search data-config="{{ json_encode($treasureConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
    <div data-treasure-panel style="margin:12px 0;">
        <x-game.inset-frame>
            <div style="padding:4px 8px;">
                <b>{{ __('treasure.title') }}</b><p>{{ $hunt['name'] }}</p>
                <span class="butt1 pointer"><span><button type="button" class="butt1 shop" data-treasure-open>{{ __('treasure.search') }}</button></span></span>
            </div>
        </x-game.inset-frame>
    </div>
    <div class="treasure-search-modal" data-treasure-modal hidden role="dialog" aria-modal="true" aria-label="{{ __('treasure.title') }}">
        <div class="treasure-search-modal__body popup_global_container">
            <div class="popup-top-left">
                <div class="popup-top-right"><div class="popup-top-center"><div class="popup_global_title">{{ __('treasure.title') }}</div></div></div>
                <div class="popup_global_close_btn" data-treasure-dismiss role="button" tabindex="0" aria-label="{{ __('treasure.close') }}"></div>
            </div>
            <div class="popup-left-center"><div class="popup-right-center"><div class="popup_global_content" style="padding:8px 12px 3px;">
            <div class="treasure-search-modal__content">
            <div class="treasure-search-progress" data-treasure-progress hidden>
                <div class="treasure-search-progress__bg"><div class="treasure-search-progress__fill" data-treasure-fill></div></div>
                <div class="treasure-search-progress__border"><div class="treasure-search-progress__center"></div></div>
                <div class="treasure-search-progress__text" data-treasure-percent>0%</div>
            </div>
            <p data-treasure-message role="status"></p>
            <div data-treasure-reward class="treasure-search-reward" hidden><img data-treasure-reward-image width="60" height="60" alt=""></div>
            <div class="treasure-search-modal__actions">
                <span class="butt1 pointer" data-treasure-start-wrap hidden><span><button class="butt1 shop" type="button" data-treasure-start>{{ __('treasure.retry') }}</button></span></span>
                <span class="butt1 pointer" data-treasure-cancel-wrap hidden><span><button class="butt1 shop" type="button" data-treasure-cancel>{{ __('treasure.cancel') }}</button></span></span>
                <span class="butt1 pointer" data-treasure-chest-wrap hidden><span><button class="butt1 shop" type="button" data-treasure-chest>{{ __('treasure.open_chest') }}</button></span></span>
                <span class="butt1 pointer" data-treasure-close-wrap><span><button class="butt1 shop" type="button" data-treasure-close>{{ __('treasure.close') }}</button></span></span>
            </div>
            </div>
            </div></div></div>
            <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
        </div>
    </div>
</div>
<script src="{{ asset('js/treasure-hunt.js') }}?v={{ filemtime(public_path('js/treasure-hunt.js')) }}" defer></script>
