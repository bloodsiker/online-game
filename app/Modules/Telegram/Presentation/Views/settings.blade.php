<section class="game-settings game-settings-section" aria-labelledby="telegram-settings-title">
    <h2 id="telegram-settings-title">Telegram</h2>
    <div style="padding:0 10px;line-height:1.6">
        <p>{{ __('telegram.description') }}</p>
        @if($telegramAccount ?? null)
            <p><b>{{ __('telegram.linked') }}</b>
                {{ $telegramAccount->username ? '@'.$telegramAccount->username : $telegramAccount->telegramUserId }}
            </p>
            <form class="telegram-settings-actions" action="{{ route('telegram.unlink') }}" method="post">
                @csrf
                @method('DELETE')
                <span class="butt1 pointer"><span><input type="submit" value="{{ __('telegram.unlink') }}"></span></span>
            </form>
        @elseif($telegramAvailable ?? false)
            @if(session('telegram_link'))
                <p><b>{{ __('telegram.link_instructions', ['minutes' => config('telegram.link_ttl_minutes')]) }}</b></p>
                <p><a href="{{ session('telegram_link') }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer">{{ __('telegram.open_bot') }}</a></p>
                <p>{{ __('telegram.refresh_hint') }}</p>
            @endif
            <form class="telegram-settings-actions" action="{{ route('telegram.link') }}" method="post">
                @csrf
                <span class="butt1 pointer"><span><input type="submit" value="{{ session('telegram_link') ? __('telegram.new_link') : __('telegram.connect') }}"></span></span>
            </form>
        @else
            <p>{{ __('telegram.unavailable') }}</p>
        @endif
    </div>
</section>
