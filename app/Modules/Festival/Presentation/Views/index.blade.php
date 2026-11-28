<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('partials.image-tabs-fix')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('festival.title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    {{-- Шрифты для рун и глаголицы в ленте манускрипта (системных шрифтов с этими письменностями часто нет). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Glagolitic&family=Noto+Sans+Runic&display=swap">
    @include('festival::partials.styles')
</head>
<body>
@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif'; $btnCenter1 = 'img/bg/btn/btn-cent1.gif'; $btnRight1 = 'img/bg/btn/btn-right1.gif';
    $btnLeft2 = 'img/bg/btn/btn-left2.gif'; $btnCenter2 = 'img/bg/btn/btn-cent2.gif'; $btnRight2 = 'img/bg/btn/btn-right2.gif';
    $itemLink = static fn (int $id): string => route('items.info.share', ['id' => $id]);
    $phase = $festival?->status;
    $sideLabel = fn (?string $side): string => $side === null ? '' : __('festival.side_'.$side);
    $coin = $settings?->coin;
    $tip = static fn ($item): string => 'data-id="'.$item->id.'" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"';
@endphp

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('festival::partials.tabs', ['active' => 'index'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <div id="festival-content">
            <table class="coll brd2-all bg_l p10h p2v knowledge-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>{{ __('festival.npc_name') }}</b></td>
                    <td align="right" style="color:#955c4a;">
                        @if($participant)
                            <b>{{ __('festival.your_side') }}:</b> <b class="redd">{{ $sideLabel($participant->side) }}</b>
                            &nbsp;&nbsp;<b>{{ __('festival.your_points') }}:</b> <b class="redd">{{ number_format($participant->points, 0, '', ' ') }}</b>
                        @endif
                        @if($coin)
                            &nbsp;&nbsp;<b>{{ $coin->name }}:</b> <b class="redd">{{ number_format($coinCount, 0, '', ' ') }}</b>
                        @endif
                    </td>
                </tr></tbody>
            </table>

            @if(session('message'))<div class="message ok">{{ session('message') }}</div>@endif
            @if($errors->any())<div class="message">{{ $errors->first() }}</div>@endif

            <x-game.inset-frame class="quest-frame">
                <div class="active-quest festival-lore">
                    <div class="quest-title"><span class="lore-orn">✦</span> {{ __('festival.lore_title') }} <span class="lore-orn">✦</span></div>
                    <p>{{ __('festival.lore_1') }}</p>
                    <p>{{ __('festival.lore_2') }}</p>
                    <p>{{ __('festival.lore_3') }}</p>
                    <div class="lore-sign">— {{ __('festival.npc_name') }}</div>
                </div>
            </x-game.inset-frame>

            @if(! $settings || ! $coin)
                <div class="empty-list"><b>{{ __('festival.not_configured') }}</b></div>
            @elseif(! $festival)
                <div class="empty-list"><b>
                    @if($settings->launch_at && $settings->launch_at->isFuture())
                        {{ __('festival.starts_at', ['date' => $settings->launch_at->format('d.m.Y H:i')]) }}
                    @else
                        {{ __('festival.no_festival') }}
                    @endif
                </b></div>
            @else
                @php
                    $isCollection = $phase === 'collection';
                    $phaseStart = $isCollection ? $festival->starts_at : $festival->collection_ends_at;
                    $phaseEnd = $isCollection ? $festival->collection_ends_at : $festival->ends_at;
                    $total = max(1, $phaseStart->diffInSeconds($phaseEnd, true));
                    $left = max(0, (int) now()->diffInSeconds($phaseEnd, false));
                    $percent = (int) max(0, min(100, round(100 - $left / $total * 100)));
                    $d = intdiv($left, 86400); $h = intdiv($left % 86400, 3600); $m = intdiv($left % 3600, 60);
                    $leftText = $d > 0
                        ? $d.' '.__('festival.unit_d').' '.$h.' '.__('festival.unit_h')
                        : ($h > 0 ? $h.' '.__('festival.unit_h').' '.$m.' '.__('festival.unit_m') : max(1, $m).' '.__('festival.unit_m'));
                @endphp
                <x-game.inset-frame class="quest-frame quest-frame--narrow">
                    <div class="active-quest">
                        <div class="quest-title">
                            {{ $isCollection ? __('festival.phase_collection') : __('festival.phase_rewards') }}
                            <span class="badge-step">{{ $isCollection ? '1' : '2' }} / 2</span>
                        </div>
                        @unless($isCollection)
                            <div class="quest-winner">{{ __('festival.winner_banner', ['side' => $sideLabel($festival->winner_side)]) }}</div>
                        @endunless
                        <div class="quest-acceptor">
                            {{ $isCollection ? __('festival.collection_ends') : __('festival.rewards_ends') }}: <b>{{ $phaseEnd->format('d.m.Y H:i') }}</b>
                        </div>
                        <div class="progress-row">
                            <div class="progress-label">{{ __('festival.time_left') }}: <b>{{ $leftText }}</b></div>
                            <div class="rep-progress-bar">
                                <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" style="width: {{ $percent }}%;"></div></div>
                                <div class="rep-progress-bar__border">
                                    <div class="rep-progress-bar__border-left"></div>
                                    <div class="rep-progress-bar__border-right"></div>
                                    <div class="rep-progress-bar__border-center"></div>
                                </div>
                                <div class="rep-progress-bar__text">{{ $percent }}%</div>
                            </div>
                        </div>
                    </div>
                </x-game.inset-frame>

                {{-- Награда за вклад: после первого этапа, один раз --}}
                @if($claimable > 0)
                    <div class="knowledge-section">{{ __('festival.reward_title') }}</div>
                    <form method="post" data-ajax action="{{ route('festival.claim', ['npc' => $npcId]) }}" style="margin-bottom:12px;">
                        @csrf
                        <div style="margin-bottom:6px;">{{ __('festival.reward_text', ['coins' => number_format($claimable, 0, '', ' ')]) }}</div>
                        <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('festival.claim') }}"></span></span>
                    </form>
                @endif

                @if($phase === 'collection')
                    @if(! $participant)
                        <div class="knowledge-section">{{ __('festival.choose_side') }}</div>
                        <div class="festival-sides">
                            @foreach(['light', 'dark'] as $side)
                                <form method="post" data-ajax action="{{ route('festival.register', ['npc' => $npcId]) }}">
                                    @csrf
                                    <input type="hidden" name="side" value="{{ $side }}">
                                    <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('festival.join_'.$side) }}"></span></span>
                                </form>
                            @endforeach
                        </div>
                    @else
                        <div class="knowledge-section">{{ __('festival.stones_title') }}</div>
                        <div class="knowledge-hint">{{ __('festival.stones_hint') }} {{ __('festival.secret_score') }}</div>
                        @include('festival::partials.stone-rows', ['route' => 'festival.deposit', 'perLabel' => 'festival.points_per_stone', 'button' => 'festival.deposit'])
                    @endif
                @else
                    @if(! $participant)
                        <div class="knowledge-hint">{{ __('festival.not_registered') }}</div>
                    @elseif($isWinner)
                        <x-game.inset-frame class="quest-frame">
                            <div class="active-quest festival-result festival-result--won">
                                <div class="quest-title">{{ __('festival.result_won_title') }}</div>
                                <div>{{ __('festival.result_won_text') }}</div>
                            </div>
                        </x-game.inset-frame>

                        <div class="knowledge-section">{{ __('festival.exchange_title') }}</div>
                        <div class="knowledge-hint">{{ __('festival.exchange_hint') }}</div>
                        @include('festival::partials.stone-rows', ['route' => 'festival.exchange', 'perLabel' => 'festival.coins_per_stone', 'button' => 'festival.exchange'])

                        <div class="knowledge-section">{{ __('festival.shop_title') }}</div>
                        <div class="knowledge-hint">{{ __('festival.shop_hint') }}</div>
                        @if($shopUrl)
                            <div style="margin-bottom:12px;">
                                <span class="butt1 pointer"><span><input type="button" class="grnn" value="{{ __('festival.shop_open') }}" onclick="location.href='{{ $shopUrl }}';"></span></span>
                            </div>
                        @else
                            <div class="empty-list">{{ __('festival.no_shop') }}</div>
                        @endif
                    @else
                        <x-game.inset-frame class="quest-frame">
                            <div class="active-quest festival-result festival-result--lost">
                                <div class="quest-title">{{ __('festival.result_lost_title') }}</div>
                                <div>{{ __('festival.result_lost_text') }}</div>
                            </div>
                        </x-game.inset-frame>
                    @endif
                @endif
            @endif
            </div>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    </tbody>
</table>


<script>
    function festivalValue(input) {
        var min = parseInt(input.dataset.min) || 0, max = parseInt(input.dataset.max) || 0;
        var value = parseInt(input.value);
        if (isNaN(value)) { value = min; }
        return Math.min(Math.max(value, min), max);
    }
    function festivalSync(input) {
        var value = festivalValue(input), min = parseInt(input.dataset.min) || 0, max = parseInt(input.dataset.max) || 0;
        var box = input.closest('.b-input__inner');
        box.querySelector('.left').classList.toggle('left-disabled', value <= min);
        box.querySelector('.right').classList.toggle('right-disabled', value >= max);
        input.value = value;
    }
    function festivalCounter(el) {
        if (el.classList.contains('left-disabled') || el.classList.contains('right-disabled')) { return false; }
        var input = el.closest('.b-input__inner').querySelector('input');
        input.value = festivalValue(input) + (el.classList.contains('left') ? -1 : 1);
        festivalSync(input);
    }
    function festivalInit(scope) {
        (scope || document).querySelectorAll('.cart_amount_sell_input').forEach(function (input) {
            festivalSync(input);
            input.addEventListener('input', function () { festivalSync(this); });
            input.addEventListener('blur', function () { festivalSync(this); });
            input.addEventListener('keydown', function (e) {
                var box = this.closest('.b-input__inner');
                if (e.key === 'ArrowUp') { e.preventDefault(); festivalCounter(box.querySelector('.right')); }
                if (e.key === 'ArrowDown') { e.preventDefault(); festivalCounter(box.querySelector('.left')); }
            });
        });
    }
    festivalInit(document);


    // Действия без перезагрузки страницы: POST по ajax, затем подмена содержимого блока свежей версией страницы.
    function festivalFlash(text, ok) {
        var root = document.getElementById('festival-content');
        var box = root.querySelector('.festival-flash');
        if (!box) {
            box = document.createElement('div');
            root.insertBefore(box, root.firstChild);
        }
        box.className = 'message festival-flash' + (ok ? ' ok' : '');
        box.textContent = text;
    }
    function festivalRefresh() {
        return fetch(window.location.href, {credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('festival-content');
                var root = document.getElementById('festival-content');
                if (fresh && root) { root.innerHTML = fresh.innerHTML; festivalInit(root); }
            });
    }
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-ajax]')) { return; }
        e.preventDefault();
        if (form.dataset.busy) { return; }
        form.dataset.busy = '1';
        fetch(form.action, {
            method: 'POST', body: new FormData(form), credentials: 'same-origin',
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (data) { return {ok: r.ok, data: data}; });
        }).then(function (res) {
            if (!res.ok) {
                var errors = res.data.errors ? Object.values(res.data.errors)[0] : null;
                festivalFlash((errors && errors[0]) || res.data.message || '{{ __('festival.ajax_error') }}', false);
                form.dataset.busy = '';
                return;
            }
            return festivalRefresh().then(function () {
                // Итог сдачи — в стандартном игровом окне сообщений (как после лечения у колодца).
                if (res.data.modal && window.parent && typeof window.parent.showErrorIframe === 'function') {
                    window.parent.showErrorIframe(encodeURIComponent(res.data.modal));
                } else {
                    festivalFlash(res.data.message || '', true);
                }
            });
        }).catch(function () {
            festivalFlash('{{ __('festival.ajax_error') }}', false);
            form.dataset.busy = '';
        });
    });
</script>
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</body>
</html>
