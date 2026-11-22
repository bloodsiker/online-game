<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Персонаж') }}</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #45382f;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 12px;
        }
        a {
            color: #765039;
        }
        a:hover{
            color: #4e3425;
        }
        .b {
            background-color: #CEBBAA;
        }
        .t0 {
            background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left;
            background-color: #EDD5C3;
        }
        .t1 {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left;
            background-color: #DFBBA3;
        }
        .l0 {
            background-color: #FFF8EA;
        }
        .l1 {
            background-color: #FFFBF5;
        }

        .tbgr {
            background-color: #FADCC2;
        }

        .tbl-shp-sides.ls, .tbl-shp-sides_0.ls {
            background-position: left top;
            background-repeat: repeat-y;
        }
        .tbl-shp-sides.rs, .tbl-shp-sides_0.rs {
            background-position: right top;
            background-repeat: repeat-y;
        }
        .tbl-shp-sml.rt, .tbl-shp-sml_0.rt {
            background-position: 0 -25px;
            height: 22px;
        }
        .tbl-shp-sml.tt, .tbl-shp-sml_0.tt {
            background-position: center -50px;
            background-repeat: repeat-x;
            height: 22px;
        }
        .tbl-shp-sml.lt, .tbl-shp-sml_0.lt {
            background-position: 0 0;
            height: 22px;
        }
        .tbl-shp-sml.lb, .tbl-shp-sml_0.lb {
            background-position: 0 -75px;
        }
        .tbl-shp-sml.bb, .tbl-shp-sml_0.bb {
            background-position: center -125px;
            background-repeat: repeat-x;
            height: 18px;
        }
        .tbl-shp-sml.rb, .tbl-shp-sml_0.rb {
            background-position: 0 -100px;
        }
        .tbl-shp-sml {
            background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-shp-sides {
            background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-usi_bg {
            background-color: #e8dac7;
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
        }
        .common-inset-tl { background: url({{ asset('main/images/common-inset-tl.png') }}) no-repeat; }
        .common-inset-tr { background: url({{ asset('main/images/common-inset-tr.png') }}) no-repeat; }
        .common-inset-bl { background: url({{ asset('main/images/common-inset-bl.png') }}) no-repeat; }
        .common-inset-br { background: url({{ asset('main/images/common-inset-br.png') }}) no-repeat; }
        .common-inset-t { background: url({{ asset('main/images/common-inset-t.png') }}) repeat-x; }
        .common-inset-b { background: url({{ asset('main/images/common-inset-b.png') }}) repeat-x; }
        .common-inset-l { background: url({{ asset('main/images/common-inset-l.png') }}) repeat-y; }
        .common-inset-r { background: url({{ asset('main/images/common-inset-r.png') }}) repeat-y; }
        .common-inset-bg { background: #ffffd6; }
        .regcolor, .regcolor * {
            color: #955c4a;
        }
        .btn_1 {
            color: #461c0b !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 11px;
        }
        .btn_2 {
            color: #ffe9ba !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 11px;
        }
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="left">

                        @include('player::partials.tabs')

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">


                        <style>
                            .char-wrap {
                                display: flex;
                                gap: 10px;
                                padding: 12px;
                                align-items: flex-start;
                            }
                            /* ── колонки ── */
                            .char-col { display: flex; flex-direction: column; gap: 10px; }
                            .char-col-left  { width: 220px; flex-shrink: 0; }
                            .char-col-mid   { flex: 1; min-width: 170px; }
                            .char-col-right { flex: 1; min-width: 170px; }

                            /* ── карточка ── */
                            .char-card {
                                width: 100%;
                                border: 0;
                                border-spacing: 0;
                                font-size: 11px;
                                font-family: Tahoma, sans-serif;
                            }
                            .char-card-title {
                                background: transparent;
                                color: #563e2f;
                                font-weight: bold;
                                padding: 6px 9px;
                                font-size: 11px;
                                letter-spacing: .15px;
                                border-bottom: 1px solid rgba(104, 80, 62, .2);
                            }
                            .char-card-title a { color: inherit !important; }
                            .char-card-body {
                                background: transparent;
                                padding: 9px 10px;
                            }

                            /* ── портрет ── */
                            .char-portrait {
                                display: block;
                                width: 130px;
                                height: 170px;
                                object-fit: cover;
                                border: 1px solid #a98f74;
                                border-radius: 3px;
                                margin: 0 auto 8px;
                                padding: 3px;
                                background: #e4d4bd;
                                box-shadow: 0 2px 4px rgba(62, 40, 26, .16);
                            }
                            .char-name {
                                text-align: center;
                                font-weight: bold;
                                font-size: 13px;
                                color: #563a29;
                                margin-bottom: 8px;
                                letter-spacing: .15px;
                            }
                            .char-class-badge {
                                display: inline-block;
                                padding: 2px 7px;
                                border: 1px solid #b9a38b;
                                border-left: 3px solid var(--class-accent, #80634f);
                                border-radius: 3px;
                                background: #ece0cf;
                                color: #57443a;
                                font-size: 10px;
                                font-weight: bold;
                            }

                            /* ── прогресс-бар ── */
                            .char-bar-wrap {
                                background: linear-gradient(#f4ead9, #d8c5a9);
                                border: 1px solid #b9a184;
                                border-radius: 5px;
                                height: 7px;
                                overflow: hidden;
                                margin: 4px 0 6px;
                                box-shadow: inset 0 1px 2px rgba(87, 61, 35, .16), 0 1px rgba(255, 255, 255, .75);
                            }
                            .char-bar-fill {
                                height: 100%;
                                border-radius: 5px;
                                transition: width 0.3s;
                                box-shadow: none;
                            }
                            .char-bar-exp,
                            .char-bar-skill,
                            .char-skill-row .char-bar-fill {
                                background: linear-gradient(90deg, #d38a28, #ffd45e, #efaa36) !important;
                                box-shadow: inset 0 1px rgba(255, 246, 195, .75);
                            }
                            .char-bar-hp { background: linear-gradient(90deg, #aa1010, #ee3030, #cc2020) !important; }
                            .char-bar-mp { background: linear-gradient(90deg, #1030c0, #3060f0, #2050d8) !important; }

                            /* ── строки параметров ── */
                            .char-stat-row {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                min-height: 16px;
                                padding: 4px 2px;
                                border-bottom: 1px solid #dfd2c1;
                                color: #4b413a;
                                font-size: 11px;
                            }
                            .char-stat-row:hover { background: rgba(220, 203, 181, .20); }
                            .char-stat-row:last-child { border-bottom: none; }
                            .char-stat-val {
                                font-weight: bold;
                                color: #533d30;
                                white-space: nowrap;
                            }
                            .char-stat-label { color: #6c5c51; }
                            /* Знак вопроса с подсказкой — тот же тултип, что в настройках (js/settings-tooltips.js) */
                            .char-stat-help-wrap { display: inline-block; vertical-align: 1px; margin-left: 3px; }
                            .char-stat-help {
                                display: inline-flex; align-items: center; justify-content: center;
                                width: 13px; height: 13px; padding: 0; border: 1px solid #a47d55; border-radius: 50%;
                                background: #f8e5a8; color: #8a5a3c; font: bold 9px/1 Tahoma, Arial, sans-serif; cursor: help;
                            }
                            .char-stat-help:hover, .char-stat-help:focus-visible { background: #a47d55; color: #fff2ce; outline: none; }
                            .game-settings-tooltip { position: fixed; z-index: 1010; width: 350px; max-width: calc(100vw - 20px); box-sizing: border-box; padding: 6px 8px 7px; border: 1px solid #e3b360; background: #f8e5a8; color: #955c4a; font: 11px/1.4 Tahoma, Arial, sans-serif; box-shadow: 0 3px 3px 1px rgba(41,13,5,.4); pointer-events: none; overflow-wrap: anywhere; }
                            .game-settings-tooltip[hidden] { display: none; }

                            /* ── строка навыка со своим баром ── */
                            .char-skill-row { padding: 5px 1px 6px; border-bottom: 1px solid #dfd2c1; }
                            .char-skill-row:last-child { border-bottom: none; }
                            .char-skill-head {
                                display: flex;
                                justify-content: space-between;
                                font-size: 11px;
                                color: #554a42;
                            }
                            .char-skill-head b { color: #594032; }
                            .char-skill-pct { color: #8a7a70; font-size: 10px; }
                            /* ── деньги ── */

                            /* ── победы/поражения ── */
                            .char-battle-row {
                                display: flex;
                                gap: 12px;
                                font-size: 11px;
                                padding: 2px 0;
                                color: #6c5c51;
                            }
                            .char-battle-row span b { color: #533d30; }

                            /* ── очки ── */
                            .char-freepoints {
                                display: flex;
                                min-height: 30px;
                                margin-top: 7px;
                                padding: 5px 9px;
                                align-items: center;
                                justify-content: center;
                                gap: 5px;
                                border: 1px solid #9b7043;
                                border-radius: 4px;
                                background: linear-gradient(#f6e6c7, #dfc397);
                                box-shadow: inset 0 1px rgba(255, 250, 229, .9), 0 1px 2px rgba(75, 46, 23, .2);
                                color: #583a24;
                                font-size: 11px;
                                text-shadow: 0 1px rgba(255, 255, 255, .55);
                            }
                            .char-freepoints b { color: #8b2c19; font-size: 12px; }
                            .char-freepoints a {
                                display: inline-block;
                                margin-left: 3px;
                                padding: 3px 7px;
                                border: 1px solid #a77a48;
                                border-radius: 3px;
                                background: linear-gradient(#fff1d3, #e8ca97);
                                color: #70401f;
                                font-weight: bold;
                                text-decoration: none;
                            }
                            .char-freepoints a:hover {
                                border-color: #875b31;
                                background: linear-gradient(#fff7e7, #f0d9ae);
                                color: #4e2b17;
                            }
                        </style>

                        @php
                            $skillsByType = collect($character->skills)->groupBy(fn ($skill) => $skill->type);
                        @endphp

                        <div class="char-wrap">

                            {{-- ════ ЛЕВАЯ КОЛОНКА: портрет + общая инфо ════ --}}
                            <div class="char-col char-col-left">

                                {{-- Портрет --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-body" style="text-align:center;">
                                        <div class="char-name">{{ $character->playerName }}</div>
                                        <img src="{{ public_media_url(auth()->user()?->profile?->avatar, fallback: 'img/avatar/dark_elf.jpg') }}"
                                             class="char-portrait" alt="{{ __('Портрет') }}">
                                        <div style="font-size:11px; color:#59483d;">
                                            {{ __('Ур.') }} <b>{{ $character->level }}</b>
                                            &nbsp;·&nbsp;
                                            <b>{{ $character->raceName }}</b>
                                        </div>
                                        <div style="margin-top:5px;">
                                            <span style="font-size:11px; color:#75665b;">{{ __('Класс:') }} </span>
                                            <span class="char-class-badge" style="--class-accent:{{ $character->stats->getDisplayCombatClassColor() }};">
                                                {{ $character->stats->getDisplayCombatClassLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                </x-game.inset-frame>

                                {{-- Опыт --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Опыт') }}</div>
                                    <div class="char-card-body">
                                        <div style="display:flex;justify-content:space-between;font-size:11px;">
                                            <span class="char-stat-label">{{ $character->exp }}</span>
                                            <span class="char-stat-val">{{ $character->expPercent }}%</span>
                                        </div>
                                        <div class="char-bar-wrap">
                                            <div class="char-bar-fill char-bar-exp"
                                                 style="width:{{ $character->expPercent }}%"></div>
                                        </div>
                                        <div style="font-size:10px; color:#8a7a70;">
                                            {{ __('след. уровень:') }} <b style="color:#59483d">{{ $character->expUp }}</b>
                                        </div>
                                    </div>
                                </x-game.inset-frame>

                                {{-- Деньги --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Финансы') }}</div>
                                    <div class="char-card-body">
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">
                                                <img src="{{ asset('img/icon/m_game.gif') }}" width="14" height="14" style="vertical-align:middle; margin-right:3px;" alt="">
                                                {{ __('Монеты') }}
                                            </span>
                                            <span class="char-stat-val">{{ number_format($character->money, 0, '', ' ') }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">
                                                <img src="{{ asset('img/icon/m_dmd.gif') }}" width="14" height="14" style="vertical-align:middle; margin-right:3px;" alt="">
                                                {{ __('Бриллианты') }}
                                            </span>
                                            <span class="char-stat-val">{{ number_format($character->diamond, 0, '', ' ') }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label" style="color:#8a7a70;">{{ __('В банке') }}</span>
                                            <span class="char-stat-val">{{ number_format($character->bankBalance, 0, '', ' ') }}</span>
                                        </div>
                                        @if($character->bankAccount)
                                            <div style="font-size:10px; color:#8a7a70; padding-top:3px;">{{ __('Счёт:') }} <b style="color:#665247">{{ $character->bankAccount }}</b></div>
                                        @endif
                                    </div>
                                </x-game.inset-frame>

                                {{-- Статистика --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Статистика') }}</div>
                                    <div class="char-card-body">
                                        <div class="char-battle-row">
                                            <span>⚔ {{ __('Побед:') }} <b>{{ $character->victory }}</b></span>
                                            <span>💀 {{ __('Поражений:') }} <b>{{ $character->death }}</b></span>
                                        </div>
                                    </div>
                                </x-game.inset-frame>

                            </div>

                            {{-- ════ СРЕДНЯЯ КОЛОНКА: характеристики ════ --}}
                            <div class="char-col char-col-mid">

                                {{-- Основные характеристики --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">
                                        <a href="/b/use.php?bid=14" style="color:#563e2f;text-decoration:none;">{{ __('Основные характеристики') }}</a>
                                    </div>
                                    <div class="char-card-body">
                                        {{-- Подсказки соответствуют PlayerStatFormulas / PlayerStatService --}}
                                        @foreach([
                                            [__('Сила'),       $character->baseStrength,       $character->stats->getStrength(),     __('Каждое очко даёт +1 к броне и увеличивает урон оружия в процентах. Основная характеристика класса «Танк».')],
                                            [__('Интуиция'),   $character->baseIntuition,      $character->stats->getInt(),          __('Каждое очко даёт +1 к шансу критического удара и увеличивает силу крит. удара. Основная характеристика класса «Крит».')],
                                            [__('Ловкость'),   $character->baseAgility,        $character->stats->getAgility(),      __('Каждое очко даёт +1 к увороту — шансу избежать удара. Основная характеристика класса «Уворот».')],
                                            [__('Интеллект'),  $character->baseIntelligence,   $character->stats->getIntelligence(), __('Увеличивает силу заклинаний: урон и лечение магией.')],
                                            [__('Мудрость'),   $character->baseWisdom,         $character->stats->getMud(),          __('Каждое очко даёт +3 к запасу маны и +1 к сопротивлению магии.')],
                                            [__('Выносливость'), $character->baseEndurance,    $character->stats->getEndurance(),    __('Каждое очко даёт +3 к максимальному здоровью.')],
                                        ] as [$label, $base, $total, $hint])
                                            @php $bonus = $total - $base; @endphp
                                            <div class="char-stat-row">
                                                <span class="char-stat-label">
                                                    {{ $label }}
                                                    <span class="char-stat-help-wrap" data-setting-hint="{{ $hint }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => $label]) }}">?</button></span>
                                                </span>
                                                <span class="char-stat-val">
                                                    {{ $base }}
                                                    @if($bonus > 0)
                                                        <span style="color:#5e7654;font-size:11px">(+{{ $bonus }})</span>
                                                    @elseif($bonus < 0)
                                                        <span style="color:#8a5550;font-size:11px">({{ $bonus }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($character->freeStats)
                                            <div class="char-freepoints">
                                                {{ __('Свободных очков:') }} <b id="pts-free-count">{{ $character->freeStats }}</b>
                                                &nbsp;<a href="#" id="pts-open-modal" onclick="openPtsModal();return false;">{{ __('Распределить »') }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </x-game.inset-frame>

                                {{-- Физические характеристики --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Физические характеристики') }}</div>
                                    <div class="char-card-body">
                                        <div style="font-size:11px; margin-bottom:2px;">
                                            {{ __('Здоровье:') }}
                                            <b style="color:#8d4f49">{{ $character->hpNow }}</b>
                                            / <b style="color:#59483d">{{ $character->stats->getHpMax() }}</b>
                                        </div>
                                        @php $hpPct = $character->stats->getHpMax() > 0 ? min(round($character->hpNow * 100 / $character->stats->getHpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-hp" style="width:{{ $hpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Класс') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Определяется соотношением базовых Силы, Ловкости и Интуиции. Классы сильны друг против друга по кругу: Танк побеждает Уворот, Уворот — Крит, Крит — Танка.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Класс')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getDisplayCombatClassLabel() }}
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Урон') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Урон оружия за раунд: одно оружие (включая двуручное) бьёт одной рукой, два оружия — суммарно. Растёт от Силы.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Урон')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ format_money($character->weaponDamage['min']) }} – {{ format_money($character->weaponDamage['max']) }}
                                                @php $dmgDebuffAvg = (int) round(($character->weaponDamageDebuff['min'] + $character->weaponDamageDebuff['max']) / 2); @endphp
                                                @if($dmgDebuffAvg > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $dmgDebuffAvg }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Броня') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Снижает получаемый физический урон. Каждое следующее очко даёт чуть меньше, чем предыдущее. Растёт от Силы и экипировки.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Броня')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getArmor() }}
                                                @if($character->stats->getDebuffReduction('armor') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('armor') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Крит') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Шанс нанести критический удар. Сравнивается с критом противника: чем больше перевес, тем выше шанс. Растёт от Интуиции и экипировки.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Крит')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getCritical() }}
                                                @if($character->stats->getDebuffReduction('critical') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('critical') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Сила крит. удара') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Во сколько процентов от обычного урона наносится физический критический удар. Растёт от Интуиции и экипировки.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Сила крит. удара')]) }}">?</button></span></span>
                                            <span class="char-stat-val">{{ $character->stats->getCritDamage() }}%</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Уворот') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Шанс полностью избежать удара. Сравнивается с уворотом противника: чем больше перевес, тем выше шанс. Растёт от Ловкости и экипировки.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Уворот')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getDodge() }}
                                                @if($character->stats->getDebuffReduction('dodge') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('dodge') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Шанс блока щитом') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Вероятность срабатывания блока при экипированном щите.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Шанс блока щитом')]) }}">?</button></span></span>
                                            <span class="char-stat-val">{{ $character->stats->getBlockChance() }}%</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Блок щитом (фикс.)') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Фиксированная часть урона, которую блок поглощает и отражает атакующему.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Блок щитом (фикс.)')]) }}">?</button></span></span>
                                            <span class="char-stat-val">{{ $character->stats->getBlockFlat() }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Блок щитом') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Процент урона, который блок поглощает и отражает атакующему.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Блок щитом')]) }}">?</button></span></span>
                                            <span class="char-stat-val">{{ $character->stats->getBlockPercent() }}%</span>
                                        </div>
                                    </div>
                                </x-game.inset-frame>

                                {{-- Магические характеристики --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Магические характеристики') }}</div>
                                    <div class="char-card-body">
                                        <div style="font-size:11px; margin-bottom:2px;">
                                            {{ __('Мана:') }}
                                            <b style="color:#526f8d">{{ $character->mpNow }}</b>
                                            / <b style="color:#59483d">{{ $character->stats->getMpMax() }}</b>
                                        </div>
                                        @php $mpPct = $character->stats->getMpMax() > 0 ? min(round($character->mpNow * 100 / $character->stats->getMpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-mp" style="width:{{ $mpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Магическая атака') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Прибавляется к силе заклинаний вместе с Интеллектом: увеличивает урон и лечение магией. Даёт экипировка.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Магическая атака')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getMagicAttack() }}
                                                @if($character->stats->getDebuffReduction('magic_attack') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('magic_attack') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Магическое сопротивление') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Снижает получаемый магический урон. Каждое следующее очко даёт чуть меньше, чем предыдущее. Растёт от Мудрости и экипировки.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Магическое сопротивление')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getMagicResistance() }}
                                                @if($character->stats->getDebuffReduction('magic_resistance') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('magic_resistance') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">{{ __('Магический крит') }} <span class="char-stat-help-wrap" data-setting-hint="{{ __('Вероятность критического срабатывания атакующего заклинания.') }}"><button type="button" class="char-stat-help" aria-label="{{ __('Что даёт: :stat', ['stat' => __('Магический крит')]) }}">?</button></span></span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getMagicCriticalChance() }}%
                                                @if($character->stats->getDebuffReduction('magic_critical') > 0)
                                                    <span style="color:#c0392b;font-size:11px" title="{{ __('Снижено активным дебаффом') }}">(-{{ $character->stats->getDebuffReduction('magic_critical') }})</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </x-game.inset-frame>

                            </div>

                            {{-- ════ ПРАВАЯ КОЛОНКА: навыки ════ --}}
                            <div class="char-col char-col-right">

                                {{-- Боевые навыки --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Боевые навыки') }}</div>
                                    <div class="char-card-body">
                                        @forelse($skillsByType->get('combat', collect()) as $skill)
                                            @php $pct = $skill->expPercent(); @endphp
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $skill->name }}: <b>{{ $skill->level }}</b></span>
                                                    <span class="char-skill-pct">{{ $pct }}%</span>
                                                </div>
                                                <div class="char-bar-wrap" style="margin:2px 0 0;">
                                                    <div class="char-bar-fill char-bar-skill" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="char-skill-pct">{{ __('Боевые навыки пока не освоены.') }}</div>
                                        @endforelse
                                    </div>
                                </x-game.inset-frame>

                                {{-- Волшебные навыки --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Волшебные навыки') }}</div>
                                    <div class="char-card-body">
                                        @forelse($skillsByType->get('magic', collect()) as $skill)
                                            @php $pct = $skill->expPercent(); @endphp
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $skill->name }}: <b>{{ $skill->level }}</b></span>
                                                    <span class="char-skill-pct">{{ $pct }}%</span>
                                                </div>
                                                <div class="char-bar-wrap" style="margin:2px 0 0;">
                                                    <div class="char-bar-fill char-bar-skill" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="char-skill-pct">{{ __('Волшебные навыки пока не освоены.') }}</div>
                                        @endforelse
                                    </div>
                                </x-game.inset-frame>

                                {{-- Мирные умения --}}
                                <x-game.inset-frame class="char-card">
                                    <div class="char-card-title">{{ __('Мирные умения') }}</div>
                                    <div class="char-card-body">
                                        @forelse($skillsByType->get('peaceful', collect()) as $skill)
                                            @php $pct = $skill->expPercent(); @endphp
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $skill->name }}: <b>{{ $skill->level }}</b></span>
                                                    <span class="char-skill-pct">{{ $pct }}%</span>
                                                </div>
                                                <div class="char-bar-wrap" style="margin:2px 0 0;">
                                                    <div class="char-bar-fill char-bar-skill" style="width:{{ $pct }}%;"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="char-skill-pct">{{ __('Мирные умения пока не освоены.') }}</div>
                                        @endforelse
                                    </div>
                                </x-game.inset-frame>

                            </div>
                        </div>


                    </td>
                    <td class="tbl-shp-sides rs">&nbsp;</td>
                </tr>
                <tr height="18">
                    <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
                    <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                    <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>




<script>

    let hp = {
        current: parseInt('{{ $character->hpNow }}'),
        max: parseInt('{{ $character->stats->getHpMax() }}')
    };
    let mp = {
        current: parseInt('{{ $character->mpNow }}'),
        max: parseInt('{{ $character->stats->getMpMax() }}')
    };
    let experience = parseFloat('{{ $character->expPercent }}');
    let lvl = parseInt('{{ $character->level }}');

    function playerAction() {
        parent.sendToFrame('character-frame', { hp, mp, experience, lvl });
    }
    playerAction();

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif

    const _ptsMsg = sessionStorage.getItem('pts-message');
    if (_ptsMsg) {
        sessionStorage.removeItem('pts-message');
        window.parent.showErrorIframe(_ptsMsg);
    }
</script>

<script>
    function openPtsModal() {
        parent.openPtsModal({
            free:  {{ $character->freeStats }},
            bases: {
                strength:     {{ $character->baseStrength }},
                intuition:    {{ $character->baseIntuition }},
                agility:      {{ $character->baseAgility }},
                intelligence: {{ $character->baseIntelligence }},
                wisdom:       {{ $character->baseWisdom }},
                endurance:    {{ $character->baseEndurance }},
            },
            full: {
                strength:     {{ $character->stats->getStrength() }},
                intuition:    {{ $character->stats->getInt() }},
                agility:      {{ $character->stats->getAgility() }},
                intelligence: {{ $character->stats->getIntelligence() }},
                wisdom:       {{ $character->stats->getMud() }},
                endurance:    {{ $character->stats->getEndurance() }},
            },
            saveUrl: '{{ route('character.point_save') }}',
            csrf:    '{{ csrf_token() }}',
        });
    }
</script>
<script src="{{ asset('js/settings-tooltips.js') }}?v={{ filemtime(public_path('js/settings-tooltips.js')) }}" defer></script>

</body>
</html>
