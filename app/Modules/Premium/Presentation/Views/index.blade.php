<!DOCTYPE html><html lang="{{ app()->getLocale() }}"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('premium.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/premium.css') }}?v={{ filemtime(public_path('css/premium.css')) }}">
</head><body class="premium-page">
@include('premium_shop::_nav', ['activeMenu' => 'premium'])
@component('premium::_frame', ['title' => __('premium.title')])
    <table class="coll w100 p10h p2v brd2-all premium-balance" border="0" cellspacing="0" cellpadding="0">
        <tbody><tr class="bg_l">
            <td align="left" nowrap><b>{{ __('premium.balance') }}</b>&nbsp;&nbsp;&nbsp;
                <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->money) }}</b>
                &nbsp;&nbsp;&nbsp;<b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->diamond) }}</b>
            </td>
        </tr></tbody>
    </table>
    <div class="premium-status">
        <div class="premium-status__identity">
            <span class="premium-crown level-{{ $currentLevel?->level ?? 0 }}" aria-hidden="true"></span>
            <div><b class="premium-status__title">{{ __('premium.current', ['level' => $currentLevel?->level ?? 0]) }}</b>
                <div class="premium-status__date">@if($period){{ __('premium.until') }} <time datetime="{{ $period->expires_at->toIso8601String() }}">{{ $period->expires_at->format('d.m.Y H:i') }}</time>@else{{ __('premium.inactive') }}@endif</div>
            </div>
        </div>
        @if($period)
            <div class="premium-status__countdown"><span>{{ __('premium.remaining') }}</span><b data-premium-expires="{{ $period->expires_at->toIso8601String() }}">{{ format_game_duration(max(0, (int) now()->diffInSeconds($period->expires_at, false))) }}</b></div>
        @endif
    </div>
    @php
        $points = $period?->diamond_cents ?? 0;
        $position = 0;
        foreach ($levels as $step) {
            if ($step->required_diamond_cents === null) break;
            if ($points >= $step->required_diamond_cents) { $position = $step->level * 20; continue; }
            $previous = $levels->firstWhere('level', $step->level - 1)?->required_diamond_cents ?? 0;
            $position = ($step->level - 1) * 20 + 20 * max(0, $points - $previous) / max(1, $step->required_diamond_cents - $previous);
            break;
        }
    @endphp
    <div class="premium-track-wrap"><div class="premium-track"><div class="premium-track__fill" style="width:{{ min(100, $position) }}%"></div><b class="premium-track__text">{{ __('premium.exchanged_total', ['amount' => format_money($points / 100)]) }}</b>
        @foreach(range(0, 5) as $number)<div class="premium-milestone" style="left:{{ $number * 20 }}%"><span class="premium-crown level-{{ $number }}"></span>@if($number)<span class="premium-milestone__cost"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt=""> {{ ($threshold = $levels->firstWhere('level', $number)?->required_diamond_cents) === null ? '—' : format_money($threshold / 100) }}</span>@endif</div>@endforeach
    </div></div>
    <div class="premium-intro"><div><ul><li>{{ __('premium.rule_start') }}</li><li>{{ __('premium.rule_accumulate') }}</li><li>{{ __('premium.rule_expire') }}</li><li>{{ __('premium.rule_bonuses') }}</li></ul></div>
        <div class="premium-exchange">
            @component('premium::_frame', ['title' => __('premium.exchange')])
            <div class="premium-wallet"><b>{{ __('premium.balance') }}</b> <img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt=""> {{ format_money($user->diamond) }}</div>
            @if(session('message'))<p class="premium-notice">{{ session('message') }}</p>@endif
            @if($errors->any())<p class="premium-error">{{ $errors->first() }}</p>@endif
            @if($configured)
                <p>{{ __('premium.rate', ['rate' => format_money($rate)]) }}</p>
                <form method="post" action="{{ route('premium.exchange') }}">@csrf<input type="hidden" name="request_id" value="{{ $requestId }}">
                    <label><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt=""> × <input class="brd dbgl" name="amount" type="number" step="1" min="1" max="9999999" required value="{{ old('amount', '1') }}" data-exchange-amount aria-label="{{ __('premium.diamonds') }}"></label>
                    <span><img src="{{ asset('img/icon/premium-exchange-arrow.gif') }}" width="18" height="10" alt=""> <b data-exchange-preview data-rate="{{ $rate }}">{{ format_money($rate) }}</b> <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt=""></span>
                    <span class="butt1 pointer"><span><button class="butt1" type="submit">{{ __('premium.exchange_action') }}</button></span></span>
                </form>
            @else <p>{{ __('premium.not_configured') }}</p>@endif
            @endcomponent
        </div>
    </div>
@endcomponent
@component('premium::_frame', ['title' => __('premium.benefits')])
    <div class="premium-benefits-layout"><nav class="premium-level-nav" aria-label="{{ __('premium.benefits') }}">
        @foreach($levels as $level)<a href="{{ route('premium.status', ['level' => $level->level]) }}" data-premium-level="{{ $level->level }}" @if($selected === $level->level) aria-current="page" @endif><span class="premium-crown level-{{ $level->level }}"></span><b>{{ __('premium.level', ['level' => $level->level]) }}</b></a>@endforeach
    </nav><div class="premium-benefits-panel">@component('premium::_inner-frame')
        @foreach($levels as $level)
            <div data-premium-level-panel="{{ $level->level }}" @if($selected !== $level->level) hidden @endif>
                <div class="premium-benefits-grid">
                    @foreach($bonusTypes as $bonus)
                        @continue(!$level->bonusEnabled($bonus))
                        <div class="premium-benefit"><span class="premium-crown level-{{ $level->level }}"></span><div><b>{{ __('premium.bonus.'.$bonus->value) }}</b><p class="premium-benefit__description">{{ __('premium.description.'.$bonus->value) }}</p><p>{{ $bonus->display((float) ($level->bonuses[$bonus->value] ?? 0)) }}</p></div></div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <p class="premium-bonus-note">{{ __('premium.extra_note') }}</p>@endcomponent</div></div>
@endcomponent
<script>
    (() => {
        const levelLinks = document.querySelectorAll('[data-premium-level]');
        const levelPanels = document.querySelectorAll('[data-premium-level-panel]');
        levelLinks.forEach(link => link.addEventListener('click', event => {
            if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            const level = link.dataset.premiumLevel;
            levelLinks.forEach(tab => {
                if (tab === link) tab.setAttribute('aria-current', 'page');
                else tab.removeAttribute('aria-current');
            });
            levelPanels.forEach(panel => { panel.hidden = panel.dataset.premiumLevelPanel !== level; });
            const url = new URL(window.location.href);
            url.searchParams.set('level', level);
            window.history.replaceState(null, '', url);
        }));
        const input = document.querySelector('[data-exchange-amount]');
        const preview = document.querySelector('[data-exchange-preview]');
        if (input && preview) {
            const updateExchangePreview = () => {
                const amount = Math.max(0, Math.floor((Number(input.value) || 0) * Number(preview.dataset.rate)));
                preview.textContent = String(amount).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            };
            input.addEventListener('input', updateExchangePreview);
            updateExchangePreview();
        }
        const expires = document.querySelector('[data-premium-expires]');
        const serverNow = @json(now()->timestamp) * 1000;
        const clientStart = Date.now();
        @php
            $premiumTimerWords = [
                [__('день'), __('дня'), __('дней')],
                [__('час'), __('часа'), __('часов')],
                [__('минута'), __('минуты'), __('минут')],
            ];
        @endphp
        const timerWords = @json($premiumTimerWords);
        function plural(count, forms) {
            const words = Object.values(forms);
            const lastTwo = count % 100;
            const last = count % 10;
            return words[lastTwo >= 11 && lastTwo <= 14 ? 2 : last === 1 ? 0 : last >= 2 && last <= 4 ? 1 : 2];
        }
        let expired = false;
        function tick() {
            if (!expires || expired) return;
            const left = Math.max(0, Math.ceil((Date.parse(expires.dataset.premiumExpires) - (serverNow + Date.now() - clientStart)) / 1000));
            if (!left) { expired = true; window.location.reload(); return; }
            const values = [Math.floor(left / 86400), Math.floor(left % 86400 / 3600), Math.floor(left % 3600 / 60)];
            const parts = [];
            values.forEach((count, index) => { if (count || parts.length) parts.push(count + ' ' + plural(count, timerWords[index])); });
            parts.push((left % 60) + ' ' + @json(__('сек.')));
            expires.textContent = parts.join(' ');
        }
        tick(); if (expires) setInterval(tick, 1000);
        @if(session('message'))
            window.parent.sendToFrame?.('character-frame', { money: @json($user->money), diamond: @json($user->diamond) });
        @endif
    })();
</script>
</body></html>
