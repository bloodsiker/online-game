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
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
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
                                gap: 8px;
                                padding: 10px;
                                align-items: flex-start;
                            }
                            /* ── колонки ── */
                            .char-col { display: flex; flex-direction: column; gap: 8px; }
                            .char-col-left  { width: 200px; flex-shrink: 0; }
                            .char-col-mid   { flex: 1; min-width: 170px; }
                            .char-col-right { flex: 1; min-width: 170px; }

                            /* ── карточка ── */
                            .char-card {
                                border: 1px solid #CEBBAA;
                                border-radius: 4px;
                                overflow: hidden;
                                font-size: 11px;
                                font-family: Tahoma, sans-serif;
                            }
                            .char-card-title {
                                background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left #DFBBA3;
                                color: #461c0b;
                                font-weight: bold;
                                padding: 4px 8px;
                                font-size: 11px;
                                letter-spacing: 0.3px;
                            }
                            .char-card-body {
{{--                                background-image: url({{ asset('img/bg/bgg.gif') }});--}}
                                background-image: url({{ asset('img/bg/common-bg.png') }});
                                background-repeat: repeat;
                                padding: 7px 9px;
                            }

                            /* ── портрет ── */
                            .char-portrait {
                                display: block;
                                width: 130px;
                                height: 170px;
                                object-fit: cover;
                                border: 2px solid #CEBBAA;
                                border-radius: 3px;
                                margin: 0 auto 8px;
                            }
                            .char-name {
                                text-align: center;
                                font-weight: bold;
                                font-size: 12px;
                                color: #461c0b;
                                margin-bottom: 6px;
                            }

                            /* ── прогресс-бар ── */
                            .char-bar-wrap {
                                background: #b09880;
                                border-radius: 3px;
                                height: 8px;
                                overflow: hidden;
                                margin: 3px 0 5px;
                                box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
                            }
                            .char-bar-fill {
                                height: 100%;
                                border-radius: 3px;
                                transition: width 0.3s;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.25);
                            }
                            .char-bar-exp  { background: linear-gradient(90deg, #c07820, #f0b030, #e89020); }
                            .char-bar-hp   { background: linear-gradient(90deg, #aa1010, #ee3030, #cc2020); }
                            .char-bar-mp   { background: linear-gradient(90deg, #1030c0, #3060f0, #2050d8); }

                            /* ── строки параметров ── */
                            .char-stat-row {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 2px 0;
                                border-bottom: 1px solid #EDD5C3;
                                color: #333;
                                font-size: 11px;
                            }
                            .char-stat-row:last-child { border-bottom: none; }
                            .char-stat-val {
                                font-weight: bold;
                                color: #461c0b;
                                white-space: nowrap;
                            }
                            .char-stat-label { color: #5a3a2a; }

                            /* ── строка навыка со своим баром ── */
                            .char-skill-row { padding: 3px 0; border-bottom: 1px solid #EDD5C3; }
                            .char-skill-row:last-child { border-bottom: none; }
                            .char-skill-head {
                                display: flex;
                                justify-content: space-between;
                                font-size: 11px;
                                color: #333;
                            }
                            .char-skill-head b { color: #461c0b; }
                            .char-skill-pct { color: #888; font-size: 10px; }
                            .char-bar-skill { background: linear-gradient(90deg, #6a3818, #c07030, #a05828); }

                            /* ── деньги ── */

                            /* ── победы/поражения ── */
                            .char-battle-row {
                                display: flex;
                                gap: 12px;
                                font-size: 11px;
                                padding: 4px 0 0;
                                color: #5a3a2a;
                            }
                            .char-battle-row span b { color: #461c0b; }

                            /* ── очки ── */
                            .char-freepoints {
                                margin-top: 5px;
                                background: #FADCC2;
                                border: 1px solid #CEBBAA;
                                border-radius: 3px;
                                padding: 4px 7px;
                                font-size: 11px;
                                color: #461c0b;
                            }
                            .char-freepoints a { color: #8b3a1a; font-weight: bold; }
                        </style>

                        <div class="char-wrap">

                            {{-- ════ ЛЕВАЯ КОЛОНКА: портрет + общая инфо ════ --}}
                            <div class="char-col char-col-left">

                                {{-- Портрет --}}
                                <div class="char-card">
                                    <div class="char-card-body" style="text-align:center;">
                                        <div class="char-name">{{ $character->playerName }}</div>
                                        <img src="{{ asset('img/avatar/dark_elf.jpg') }}"
                                             class="char-portrait" alt="Портрет">
                                        <div style="font-size:11px; color:#461c0b;">
                                            Ур. <b>{{ $character->level }}</b>
                                            &nbsp;·&nbsp;
                                            <b>{{ $character->raceName }}</b>
                                        </div>
                                        <div style="margin-top:5px;">
                                            <span style="font-size:11px; color:#5a3a2a;">Класс: </span>
                                            <span style="display:inline-block; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:bold;
                                                background:{{ $character->stats->getDisplayCombatClassColor() }};
                                                color:#fff;">
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
                                        <div style="font-size:10px; color:#888;">
                                            след. уровень: <b style="color:#461c0b">{{ $character->expUp }}</b>
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
                                            <span class="char-stat-val">0</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label" style="color:#888;">В банке</span>
                                            <span class="char-stat-val">0</span>
                                        </div>
                                        <div style="font-size:10px; color:#888; padding-top:3px;">Счёт: <b style="color:#5a3a2a">7131</b></div>
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
                                        <a href="/b/use.php?bid=14" style="color:#461c0b;text-decoration:none;">Основные характеристики</a>
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
                                                        <span style="color:#2a7a2a;font-size:11px">(+{{ $bonus }})</span>
                                                    @elseif($bonus < 0)
                                                        <span style="color:#8b2020;font-size:11px">({{ $bonus }})</span>
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
                                            <b style="color:#8b2020">{{ $character->hpNow }}</b>
                                            / <b style="color:#461c0b">{{ $character->stats->getHpMax() }}</b>
                                        </div>
                                        @php $hpPct = $character->stats->getHpMax() > 0 ? min(round($character->hpNow * 100 / $character->stats->getHpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-hp" style="width:{{ $hpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Класс</span>
                                            <span class="char-stat-val" style="color:{{ $character->stats->getDisplayCombatClassColor() }}">
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
                                            <b style="color:#2040a0">{{ $character->mpNow }}</b>
                                            / <b style="color:#461c0b">{{ $character->stats->getMpMax() }}</b>
                                        </div>
                                        @php $mpPct = $character->stats->getMpMax() > 0 ? min(round($character->mpNow * 100 / $character->stats->getMpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-mp" style="width:{{ $mpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Атака</span>
                                            <span class="char-stat-val">237</span>
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
                                        @foreach($character->skills as $skill)
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
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Волшебные навыки --}}
                                <div class="char-card">
                                    <div class="char-card-title">Волшебные навыки</div>
                                    <div class="char-card-body">
                                        @foreach([
                                            ['Волшебное оружие', 1,  52.46],
                                            ['Колдовство',       14, 27.13],
                                        ] as [$name, $lvl, $pct])
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $name }}: <b>{{ $lvl }}</b></span>
                                                    <span class="char-skill-pct">{{ $pct }}%</span>
                                                </div>
                                                <div class="char-bar-wrap" style="margin:2px 0 0;">
                                                    <div class="char-bar-fill char-bar-skill" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Мирные умения --}}
                                <div class="char-card">
                                    <div class="char-card-title">Мирные умения</div>
                                    <div class="char-card-body">
                                        @foreach([
                                            ['Охота',                         15, 56.15, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Зачаровывание предметов',       24, 67.12, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Торговля',                      32, 69.39, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Торговля дефицитными товарами', 43, 61.20, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Взлом замков',                   6, 40.33, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Подводное плавание',            17, 21.67, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Рыболовство',                    2, 12.78, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Лесозаготовка',                 17, 56.01, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                            ['Горное дело',                   16, 35.08, 'linear-gradient(90deg,#b06000,#f0a000)'],
                                        ] as [$name, $lvl, $pct, $color])
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $name }}: <b>{{ $lvl }}</b></span>
                                                    <span class="char-skill-pct">{{ $pct }}%</span>
                                                </div>
                                                <div class="char-bar-wrap" style="margin:2px 0 0;">
                                                    <div class="char-bar-fill" style="width:{{ $pct }}%; background:{{ $color }};"></div>
                                                </div>
                                            </div>
                                        @endforeach
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
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i':
                window.parent.sendDataToGame('{{ route('backpack') }}');
                break;
            case ' ':
                window.parent.sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    });

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
