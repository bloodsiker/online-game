<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Влияние:') }} {{ $page['map']->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; }
        body { margin: 0; color: #45382f; font: 11px Tahoma, Arial, sans-serif; }
        a { color: #765039; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { height: 22px; background-position: 0 -25px; }
        .tbl-shp-sml.tt { height: 22px; background-position: center -50px; background-repeat: repeat-x; }
        .tbl-shp-sml.lt { height: 22px; background-position: 0 0; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { height: 18px; background-position: center -125px; background-repeat: repeat-x; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-usi_bg { background: #e8dac7 url({{ asset('img/bg/tbl-usi_bg.gif') }}) repeat; }
        .common-inset-tl { background: url({{ asset('main/images/common-inset-tl.png') }}) no-repeat; }
        .common-inset-tr { background: url({{ asset('main/images/common-inset-tr.png') }}) no-repeat; }
        .common-inset-bl { background: url({{ asset('main/images/common-inset-bl.png') }}) no-repeat; }
        .common-inset-br { background: url({{ asset('main/images/common-inset-br.png') }}) no-repeat; }
        .common-inset-t { background: url({{ asset('main/images/common-inset-t.png') }}) repeat-x; }
        .common-inset-b { background: url({{ asset('main/images/common-inset-b.png') }}) repeat-x; }
        .common-inset-l { background: url({{ asset('main/images/common-inset-l.png') }}) repeat-y; }
        .common-inset-r { background: url({{ asset('main/images/common-inset-r.png') }}) repeat-y; }
        .common-inset-bg { background: #ffffd6; }
        .influence-detail { max-width: 900px; margin: 0 auto; padding: 10px 14px 18px; }
        .influence-detail__back { margin-bottom: 8px; }
        .influence-summary {
            width: 100%;
            margin-bottom: 10px;
            border: 0;
            border-spacing: 0;
        }
        .influence-summary__content {
            padding: 12px 16px;
            text-align: center;
        }
        .influence-summary h1 { margin: 0 0 5px; color: #7d2415; font-size: 17px; }
        .influence-summary__points { margin-bottom: 8px; font-size: 12px; }
        .influence-summary__points b { color: #8b2f1e; font-size: 14px; }
        .rep-progress-bar { position: relative; width: min(520px, 100%); height: 31px; margin: 0 auto; overflow: hidden; }
        .rep-progress-bar__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; border-radius: 5px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -54px repeat-x; }
        .rep-progress-bar__fill { height: 27px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -27px repeat-x; }
        .rep-progress-bar__border { position: absolute; inset: 0; height: 31px; }
        .rep-progress-bar__border-left, .rep-progress-bar__border-right, .rep-progress-bar__border-center { height: 31px; background: url({{ asset('img/progressbar/progress-bar-1-border.png') }}) no-repeat; }
        .rep-progress-bar__border-left, .rep-progress-bar__border-right { position: absolute; top: 0; width: 20px; }
        .rep-progress-bar__border-left { left: 0; }
        .rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center { margin: 0 20px; background-position: 0 -62px; background-repeat: repeat-x; }
        .rep-progress-bar__text { position: absolute; inset: 0; color: #fff; font-weight: bold; line-height: 31px; text-align: center; text-shadow: 0 1px 2px #333; }
        .influence-summary__next { margin-top: 5px; color: #684936; }
        .influence-level-frame { width: 100%; margin-bottom: 9px; border-spacing: 0; }
        .influence-scale-title {
            margin: 15px 0 8px;
            color: #762719;
            font-size: 14px;
            text-align: center;
        }
        .influence-level-card {
            display: grid;
            grid-template-columns: 88px minmax(160px, .8fr) minmax(260px, 1.7fr);
            gap: 12px;
            margin-bottom: 0;
            padding: 10px;
        }
        .influence-level-card.is-current { background: rgba(255, 225, 154, .82); }
        .influence-level-card.is-locked { opacity: .72; }
        .influence-level-card__medal { text-align: center; }
        .influence-medal-frame { display: flex; width: 71px; height: 72px; margin: 0 auto 4px; align-items: center; justify-content: center; background: url({{ asset('main/images/user-reward-frame.png') }}) center/71px 72px no-repeat; }
        .influence-medal-frame img { width: 60px; height: 60px; object-fit: contain; }
        .influence-level-card__number { color: #75513b; font-weight: bold; }
        .influence-level-card__main h2 { margin: 2px 0 4px; color: #8a2818; font-size: 13px; }
        .influence-level-card__threshold { margin-bottom: 6px; font-weight: bold; }
        .influence-level-card__state { display: inline-block; padding: 2px 7px; border: 1px solid #9d856b; background: #dfccb0; color: #644834; font-weight: bold; }
        .influence-level-card.is-current .influence-level-card__state { border-color: #9b5b31; background: #bc6a32; color: #fff4dc; }
        .influence-level-card.is-reached:not(.is-current) .influence-level-card__state { border-color: #6f8752; background: #8caa67; color: #fff; }
        .influence-level-card__description { margin-top: 7px; color: #6a5240; }
        .influence-benefits { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
        .influence-benefit { min-height: 54px; padding: 6px 7px; border: 1px solid #ccb18e; background: rgba(255, 248, 227, .62); box-sizing: border-box; }
        .influence-benefit h3 { margin: 0 0 5px; color: #70432c; font-size: 11px; }
        .influence-benefit ul { margin: 0; padding-left: 15px; }
        .influence-benefit li { margin-bottom: 3px; }
        .influence-benefit__empty { color: #9a806b; font-style: italic; }
        .influence-reward-item { color: #8c1d12; font-weight: bold; }
        .influence-note { margin: 10px 2px 0; color: #765d49; font-size: 10px; text-align: center; }
        .influence-empty { margin: 18px 0; padding: 25px; border: 1px solid #c2aa8d; text-align: center; }
        @media (max-width: 760px) {
            .influence-level-card { grid-template-columns: 78px 1fr; }
            .influence-benefits { grid-column: 1 / -1; }
        }
        @media (max-width: 520px) {
            .influence-level-card { grid-template-columns: 1fr; text-align: center; }
            .influence-benefits { grid-template-columns: 1fr; text-align: left; }
        }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody><tr valign="top"><td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                <td class="tbl-shp-sml tt" valign="top" align="left">@include('player::partials.tabs', ['group' => 'influence'])</td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top">
                    <main class="influence-detail">
                        <div class="influence-detail__back">← <a href="{{ route('character.influence') }}">{{ __('Все территории') }}</a></div>
                        <x-game.inset-frame class="influence-summary">
                            <div class="influence-summary__content">
                            <h1>{{ __('Влияние в') }} {{ $page['map']->name }}</h1>
                            <div class="influence-summary__points">{{ __('Ваше влияние:') }} <b>{{ number_format($page['influence'], 0, '', ' ') }}</b></div>
                            <div class="rep-progress-bar" title="{{ $page['influence'] }} {{ __('из') }} {{ $page['nextLevel']?->required_influence ?? $page['influence'] }}">
                                <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" style="width: {{ $page['progressPercent'] }}%;"></div></div>
                                <div class="rep-progress-bar__border"><div class="rep-progress-bar__border-left"></div><div class="rep-progress-bar__border-right"></div><div class="rep-progress-bar__border-center"></div></div>
                                <div class="rep-progress-bar__text">{{ $page['progressPercent'] }}%</div>
                            </div>
                            <div class="influence-summary__next">
                                @if($page['levels']->isEmpty())
                                    {{ __('Шкала уровней для этой территории пока не настроена') }}
                                @elseif($page['nextLevel'])
                                    {{ __('До уровня') }} «{{ $page['nextLevel']->name }}» {{ __('осталось') }} <b>{{ number_format(max(0, $page['nextLevel']->required_influence - $page['influence']), 0, '', ' ') }}</b>
                                @else
                                    {{ __('Достигнут максимальный уровень влияния') }}
                                @endif
                            </div>
                            </div>
                        </x-game.inset-frame>

                        <h2 class="influence-scale-title">{{ __('Шкала уровней влияния') }}</h2>
                        @forelse($page['levels'] as $level)
                            @php
                                $reached = $page['influence'] >= $level->required_influence;
                                $current = $page['currentLevel']?->id === $level->id;
                                $medal = $level->medal;
                            @endphp
                            <x-game.inset-frame class="influence-level-frame">
                            <article class="influence-level-card {{ $current ? 'is-current' : ($reached ? 'is-reached' : 'is-locked') }}">
                                <div class="influence-level-card__medal">
                                    <div class="influence-medal-frame" title="{{ $medal?->description ?? __('Медаль не предусмотрена') }}">
                                        <img src="{{ $medal?->iconUrl() ?: asset('img/bg/empty_slot.gif') }}" alt="">
                                    </div>
                                    <div class="influence-level-card__number">{{ __('Уровень') }} {{ $level->level }}</div>
                                </div>
                                <div class="influence-level-card__main">
                                    <h2>{{ $level->name }}</h2>
                                    <div class="influence-level-card__threshold">{{ __('От') }} {{ number_format($level->required_influence, 0, '', ' ') }} {{ __('влияния') }}</div>
                                    <span class="influence-level-card__state">{{ $current ? __('Текущий уровень') : ($reached ? __('Достигнут') : __('Не достигнут')) }}</span>
                                    @if($medal)
                                        <div class="influence-level-card__description"><b>{{ $medal->name }}</b>@if($medal->description)<br>{{ $medal->description }}@endif</div>
                                    @endif
                                </div>
                                <div class="influence-benefits">
                                    <section class="influence-benefit">
                                        <h3>{{ __('Бонусы территории') }}</h3>
                                        @if($level->bonuses->isNotEmpty())
                                            <ul>@foreach($level->bonuses as $bonus)<li>{{ $bonus->bonus_type->label() }}: <b>+{{ rtrim(rtrim(number_format($bonus->value, 3, '.', ''), '0'), '.') }}</b></li>@endforeach</ul>
                                        @else
                                            <span class="influence-benefit__empty">{{ __('Нет бонусов') }}</span>
                                        @endif
                                    </section>
                                    <section class="influence-benefit">
                                        <h3>{{ __('Характеристики медали') }}</h3>
                                        @if($medal?->stats->isNotEmpty())
                                            <ul>@foreach($medal->stats as $stat)<li>{{ $stat->stat_type->label() }}: <b>+{{ rtrim(rtrim(number_format($stat->value, 3, '.', ''), '0'), '.') }}{{ $stat->is_percent ? '%' : '' }}</b></li>@endforeach</ul>
                                        @else
                                            <span class="influence-benefit__empty">{{ __('Нет характеристик') }}</span>
                                        @endif
                                    </section>
                                    <section class="influence-benefit">
                                        <h3>{{ __('Награды уровня') }}</h3>
                                        @if($level->rewards->isNotEmpty())
                                            <ul>
                                                @foreach($level->rewards as $reward)
                                                    <li>
                                                        @if($reward->reward_type->value === 'item' && $reward->item)
                                                            <a class="influence-reward-item" href="{{ route('items.info.share', ['id' => $reward->item->id]) }}" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $reward->item->name }}</a> × {{ $reward->amount }}
                                                        @else
                                                            {{ $reward->reward_type->label() }}: <b>{{ number_format($reward->amount, 0, '', ' ') }}</b>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="influence-benefit__empty">{{ __('Нет разовой награды') }}</span>
                                        @endif
                                    </section>
                                </div>
                            </article>
                            </x-game.inset-frame>
                        @empty
                            <div class="influence-empty">{{ __('Для этой территории пока не настроена шкала влияния.') }}</div>
                        @endforelse
                        <p class="influence-note">{{ __('Действуют бонусы текущего уровня и характеристики самой высокой полученной медали этой территории.') }}</p>
                    </main>
                </td>
                <td class="tbl-shp-sides rs">&nbsp;</td>
            </tr>
            <tr height="18"><td width="20" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td width="20" class="tbl-shp-sml rb"><b></b></td></tr>
            </tbody>
        </table>
    </td></tr></tbody>
</table>
</body>
</html>
