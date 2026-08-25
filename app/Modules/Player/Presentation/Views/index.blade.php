<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
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
                            .char-col-left  { width: 200px; flex-shrink: 0; }
                            .char-col-mid   { flex: 1; min-width: 170px; }
                            .char-col-right { flex: 1; min-width: 170px; }

                            /* ── карточка ── */
                            .char-card {
                                border: 1px solid #bca68e;
                                border-radius: 3px;
                                overflow: hidden;
                                font-size: 11px;
                                font-family: Tahoma, sans-serif;
                                background: #f4ecdf;
                                box-shadow: 0 1px 2px rgba(66, 45, 30, .10);
                            }
                            .char-card-title {
                                background-color: #ddcbb3;
                                background-image: linear-gradient(rgba(238, 224, 204, .88), rgba(216, 195, 168, .88)), url({{ asset('img/bg/table-header2.jpg') }});
                                background-repeat: repeat-x;
                                color: #563e2f;
                                font-weight: bold;
                                padding: 6px 9px;
                                font-size: 11px;
                                letter-spacing: .15px;
                                border-bottom: 1px solid #c5b096;
                                text-shadow: 0 1px rgba(255, 250, 239, .75);
                            }
                            .char-card-title a { color: inherit !important; }
                            .char-card-body {
                                background-color: #f5eddf;
                                background-image: url({{ asset('img/bg/common-bg.png') }});
                                background-repeat: repeat;
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
                                background: #d2c4b4;
                                border: 1px solid rgba(104, 80, 62, .25);
                                border-radius: 5px;
                                height: 7px;
                                overflow: hidden;
                                margin: 4px 0 6px;
                                box-shadow: inset 0 1px 2px rgba(68, 45, 30, .12);
                            }
                            .char-bar-fill {
                                height: 100%;
                                border-radius: 5px;
                                transition: width 0.3s;
                                box-shadow: none;
                            }
                            .char-bar-exp  { background: linear-gradient(90deg, #c07820, #f0b030, #e89020); }
                            .char-bar-hp   { background: linear-gradient(90deg, #aa1010, #ee3030, #cc2020); }
                            .char-bar-mp   { background: linear-gradient(90deg, #1030c0, #3060f0, #2050d8); }

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
                            .char-bar-skill,
                            .char-skill-row .char-bar-fill { background: #8e7055 !important; }

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
                                margin-top: 5px;
                                background: #eee2d1;
                                border: 1px solid #c5b096;
                                border-radius: 3px;
                                padding: 6px 8px;
                                font-size: 11px;
                                color: #594437;
                            }
                            .char-freepoints a { color: #765039; font-weight: bold; text-decoration: none; }
                            .char-freepoints a:hover { color: #4e3425; text-decoration: underline; }
                        </style>

                        @php
                            $skillsByType = collect($character->skills)->groupBy(fn ($skill) => $skill->type);
                        @endphp

                        <div class="char-wrap">

                            {{-- ════ ЛЕВАЯ КОЛОНКА: портрет + общая инфо ════ --}}
                            <div class="char-col char-col-left">

                                {{-- Портрет --}}
                                <div class="char-card">
                                    <div class="char-card-body" style="text-align:center;">
                                        <div class="char-name">{{ $character->playerName }}</div>
                                        <img src="{{ asset('img/avatar/dark_elf.jpg') }}"
                                             class="char-portrait" alt="Портрет">
                                        <div style="font-size:11px; color:#59483d;">
                                            Ур. <b>{{ $character->level }}</b>
                                            &nbsp;·&nbsp;
                                            <b>{{ $character->raceName }}</b>
                                        </div>
                                        <div style="margin-top:5px;">
                                            <span style="font-size:11px; color:#75665b;">Класс: </span>
                                            <span class="char-class-badge" style="--class-accent:{{ $character->stats->getDisplayCombatClassColor() }};">
                                                {{ $character->stats->getDisplayCombatClassLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Опыт --}}
                                <div class="char-card">
                                    <div class="char-card-title">Опыт</div>
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
                                            след. уровень: <b style="color:#59483d">{{ $character->expUp }}</b>
                                        </div>
                                    </div>
                                </div>

                                {{-- Деньги --}}
                                <div class="char-card">
                                    <div class="char-card-title">Финансы</div>
                                    <div class="char-card-body">
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">
                                                <img src="{{ asset('img/icon/m_game.gif') }}" width="14" height="14" style="vertical-align:middle; margin-right:3px;" alt="">
                                                Монеты
                                            </span>
                                            <span class="char-stat-val">{{ number_format($character->money, 0, '', ' ') }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">
                                                <img src="{{ asset('img/icon/m_dmd.gif') }}" width="14" height="14" style="vertical-align:middle; margin-right:3px;" alt="">
                                                Бриллианты
                                            </span>
                                            <span class="char-stat-val">{{ number_format($character->diamond, 0, '', ' ') }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label" style="color:#8a7a70;">В банке</span>
                                            <span class="char-stat-val">{{ number_format($character->bankBalance, 0, '', ' ') }}</span>
                                        </div>
                                        @if($character->bankAccount)
                                            <div style="font-size:10px; color:#8a7a70; padding-top:3px;">Счёт: <b style="color:#665247">{{ $character->bankAccount }}</b></div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Статистика --}}
                                <div class="char-card">
                                    <div class="char-card-title">Статистика</div>
                                    <div class="char-card-body">
                                        <div class="char-battle-row">
                                            <span>⚔ Побед: <b>{{ $character->victory }}</b></span>
                                            <span>💀 Поражений: <b>{{ $character->death }}</b></span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- ════ СРЕДНЯЯ КОЛОНКА: характеристики ════ --}}
                            <div class="char-col char-col-mid">

                                {{-- Основные характеристики --}}
                                <div class="char-card">
                                    <div class="char-card-title">
                                        <a href="/b/use.php?bid=14" style="color:#563e2f;text-decoration:none;">Основные характеристики</a>
                                    </div>
                                    <div class="char-card-body">
                                        @foreach([
                                            ['Сила',       $character->baseStrength,       $character->stats->getStrength()],
                                            ['Интуиция',   $character->baseIntuition,      $character->stats->getInt()],
                                            ['Ловкость',   $character->baseAgility,        $character->stats->getAgility()],
                                            ['Интеллект',  $character->baseIntelligence,   $character->stats->getIntelligence()],
                                            ['Мудрость',   $character->baseWisdom,         $character->stats->getMud()],
                                            ['Выносливость', $character->baseEndurance,    $character->stats->getEndurance()],
                                        ] as [$label, $base, $total])
                                            @php $bonus = $total - $base; @endphp
                                            <div class="char-stat-row">
                                                <span class="char-stat-label">{{ $label }}</span>
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
                                                Свободных очков: <b id="pts-free-count">{{ $character->freeStats }}</b>
                                                &nbsp;<a href="#" id="pts-open-modal" onclick="openPtsModal();return false;">Распределить »</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Физические характеристики --}}
                                <div class="char-card">
                                    <div class="char-card-title">Физические характеристики</div>
                                    <div class="char-card-body">
                                        <div style="font-size:11px; margin-bottom:2px;">
                                            Здоровье:
                                            <b style="color:#8d4f49">{{ $character->hpNow }}</b>
                                            / <b style="color:#59483d">{{ $character->stats->getHpMax() }}</b>
                                        </div>
                                        @php $hpPct = $character->stats->getHpMax() > 0 ? min(round($character->hpNow * 100 / $character->stats->getHpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-hp" style="width:{{ $hpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Класс</span>
                                            <span class="char-stat-val">
                                                {{ $character->stats->getDisplayCombatClassLabel() }}
                                            </span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Броня</span>
                                            <span class="char-stat-val">{{ $character->stats->getArmor() }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Крит</span>
                                            <span class="char-stat-val">{{ $character->stats->getCritical() }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Уворот</span>
                                            <span class="char-stat-val">{{ $character->stats->getDodge() }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Магические характеристики --}}
                                <div class="char-card">
                                    <div class="char-card-title">Магические характеристики</div>
                                    <div class="char-card-body">
                                        <div style="font-size:11px; margin-bottom:2px;">
                                            Мана:
                                            <b style="color:#526f8d">{{ $character->mpNow }}</b>
                                            / <b style="color:#59483d">{{ $character->stats->getMpMax() }}</b>
                                        </div>
                                        @php $mpPct = $character->stats->getMpMax() > 0 ? min(round($character->mpNow * 100 / $character->stats->getMpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-mp" style="width:{{ $mpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Атака</span>
                                            <span class="char-stat-val">{{ $character->stats->getMagicAttack() }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- ════ ПРАВАЯ КОЛОНКА: навыки ════ --}}
                            <div class="char-col char-col-right">

                                {{-- Боевые навыки --}}
                                <div class="char-card">
                                    <div class="char-card-title">Боевые навыки</div>
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
                                            <div class="char-skill-pct">Боевые навыки пока не освоены.</div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Волшебные навыки --}}
                                <div class="char-card">
                                    <div class="char-card-title">Волшебные навыки</div>
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
                                            <div class="char-skill-pct">Волшебные навыки пока не освоены.</div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Мирные умения --}}
                                <div class="char-card">
                                    <div class="char-card-title">Мирные умения</div>
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
                                            <div class="char-skill-pct">Мирные умения пока не освоены.</div>
                                        @endforelse
                                    </div>
                                </div>

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

</body>
</html>
