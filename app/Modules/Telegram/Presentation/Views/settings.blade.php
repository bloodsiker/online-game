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
            <form class="telegram-settings-actions" action="{{ route('telegram.link') }}" method="post" data-telegram-link-form>
                @csrf
                <span class="butt1 pointer"><span><input type="submit" value="{{ __('telegram.connect') }}"></span></span>
            </form>
            @if(session('telegram_link'))
                <noscript>
                    <p>{{ __('telegram.link_instructions', ['minutes' => config('telegram.link_ttl_minutes')]) }}</p>
                    <p><a href="{{ session('telegram_link') }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer">{{ __('telegram.open_bot') }}</a></p>
                </noscript>
            @endif
        @else
            <p>{{ __('telegram.unavailable') }}</p>
        @endif
    </div>
</section>
@if(!($telegramAccount ?? null) && ($telegramAvailable ?? false))
    @include('telegram::link-modal')
@endif
