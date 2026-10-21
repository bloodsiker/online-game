<dialog id="trade-quantity-modal" class="player-trade__quantity-modal" aria-labelledby="trade-quantity-title">
    <div class="popup_global_container">
        <div class="popup-top-left">
            <div class="popup-top-right"><div class="popup-top-center">
                <div class="popup_global_title" id="trade-quantity-title">{{ __('Передать предмет') }}</div>
            </div></div>
            <button type="button" class="popup_global_close_btn" data-quantity-cancel aria-label="{{ __('Закрыть') }}"></button>
        </div>
        <div class="popup-left-center"><div class="popup-right-center">
            <form id="trade-quantity-form" class="popup_global_content player-trade__quantity-content">
                <div class="player-trade__quantity-item"><img id="trade-quantity-image" src="{{ asset('img/bg/empty_slot.gif') }}" alt=""><b id="trade-quantity-name"></b></div>
                <label for="trade-quantity-input">{{ __('Количество для передачи') }}</label>
                <div class="player-trade__quantity-controls">
                    <b class="butt2 pointer"><b><button type="button" id="trade-quantity-minus" aria-label="{{ __('Уменьшить количество') }}">−</button></b></b>
                    <input id="trade-quantity-input" type="number" min="1" step="1" value="1" required autocomplete="off">
                    <b class="butt2 pointer"><b><button type="button" id="trade-quantity-plus" aria-label="{{ __('Увеличить количество') }}">+</button></b></b>
                    <b class="butt2 pointer"><b><button type="button" id="trade-quantity-all">{{ __('Все') }}</button></b></b>
                </div>
                <div>{{ __('Доступно:') }} <b id="trade-quantity-max"></b></div>
                <div class="player-trade__actions">
                    <span class="butt1 pointer"><span><button type="submit" class="butt1">{{ __('Добавить') }}</button></span></span>
                    <span class="butt1 pointer"><span><button type="button" class="butt1" data-quantity-cancel>{{ __('Отмена') }}</button></span></span>
                </div>
            </form>
        </div></div>
        <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
    </div>
</dialog>
