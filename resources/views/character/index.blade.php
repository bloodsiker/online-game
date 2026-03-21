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

                        <table border="0" cellspacing="0" cellpadding="0" style="position: relative; top: -2px;">
                            <tbody>
                            @php
                                $btnLeft1 = 'img/bg/btn/btn-left1.gif';
                                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                                $btnRight1 = 'img/bg/btn/btn-right1.gif';

                                $btnLeft2 = 'img/bg/btn/btn-left2.gif';
                                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                                $btnRight2 = 'img/bg/btn/btn-right2.gif';
                            @endphp
                            <tr height="21">
                                <td width="19"><img id="left_1" src="{{ asset($group === 'character' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_1" align="center" style="background: url({{ asset($group === 'character' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_1" href="{{ route('character', ['group' => 'character']) }}" title="Персонаж" class="{{ $group === 'character' ? 'btn_2' : 'btn_1' }}">Персонаж</a>
                                </td>
                                <td width="19"><img id="right_1" src="{{ asset($group === 'character' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'magic_skill' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'magic_skill' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('magic_skill', ['group' => 'magic_skill']) }}" title="Книга заклинаний" class="{{ $group === 'magic_skill' ? 'btn_2' : 'btn_1' }}">Книга заклинаний</a>
                                </td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'magic_skill' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'slots' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'slots' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('slots', ['group' => 'slots']) }}" title="Слоты" class="{{ $group === 'slots' ? 'btn_2' : 'btn_1' }}">Слоты</a>
                                </td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'slots' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

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
                                        <div class="char-name">{{ $player->user->name }}</div>
                                        <img src="{{ asset('img/avatar/dark_elf.jpg') }}"
                                             class="char-portrait" alt="Портрет">
                                        <div style="font-size:11px; color:#461c0b;">
                                            Ур. <b>{{ $player->lvl }}</b>
                                            &nbsp;·&nbsp;
                                            <b>{{ $player->race->name }}</b>
                                        </div>
                                    </div>
                                </div>

                                {{-- Опыт --}}
                                <div class="char-card">
                                    <div class="char-card-title">Опыт</div>
                                    <div class="char-card-body">
                                        <div style="display:flex;justify-content:space-between;font-size:11px;">
                                            <span class="char-stat-label">{{ $player->exp }}</span>
                                            <span class="char-stat-val">{{ $player->getPercentExp() }}%</span>
                                        </div>
                                        <div class="char-bar-wrap">
                                            <div class="char-bar-fill char-bar-exp"
                                                 style="width:{{ $player->getPercentExp() }}%"></div>
                                        </div>
                                        <div style="font-size:10px; color:#888;">
                                            след. уровень: <b style="color:#461c0b">{{ $player->exp_up }}</b>
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
                                            <span class="char-stat-val">{{ number_format($user->money, 0, '', ' ') }}</span>
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
                                            <span>⚔ Побед: <b>{{ $player->victory }}</b></span>
                                            <span>💀 Поражений: <b>{{ $player->death }}</b></span>
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
                                            ['Сила',       $player->getStrength()],
                                            ['Интуиция',   $player->getInt()],
                                            ['Ловкость',   $player->getAgility()],
                                            ['Интеллект',  $player->getIntelligence()],
                                            ['Мудрость',   $player->getMud()],
                                        ] as [$label, $val])
                                            <div class="char-stat-row">
                                                <span class="char-stat-label">{{ $label }}</span>
                                                <span class="char-stat-val">{{ $val }}</span>
                                            </div>
                                        @endforeach
                                        @if($player->free_stats)
                                            <div class="char-freepoints">
                                                Свободных очков: <b id="pts-free-count">{{ $player->free_stats }}</b>
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
                                            <b style="color:#8b2020">{{ $player->hp_now }}</b>
                                            / <b style="color:#461c0b">{{ $playerDecorator->getHpMax() }}</b>
                                        </div>
                                        @php $hpPct = $playerDecorator->getHpMax() > 0 ? min(round($player->hp_now * 100 / $playerDecorator->getHpMax()), 100) : 0; @endphp
                                        <div class="char-bar-wrap" style="height:10px; margin-bottom:8px;">
                                            <div class="char-bar-fill char-bar-hp" style="width:{{ $hpPct }}%"></div>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Броня</span>
                                            <span class="char-stat-val">{{ $playerDecorator->getArmor() }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Крит</span>
                                            <span class="char-stat-val">{{ $playerDecorator->getCritical() }}</span>
                                        </div>
                                        <div class="char-stat-row">
                                            <span class="char-stat-label">Уворот</span>
                                            <span class="char-stat-val">{{ $playerDecorator->getDodge() }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Магические характеристики --}}
                                <div class="char-card">
                                    <div class="char-card-title">Магические характеристики</div>
                                    <div class="char-card-body">
                                        <div style="font-size:11px; margin-bottom:2px;">
                                            Мана:
                                            <b style="color:#2040a0">{{ $player->mp_now }}</b>
                                            / <b style="color:#461c0b">{{ $playerDecorator->getMpMax() }}</b>
                                        </div>
                                        @php $mpPct = $playerDecorator->getMpMax() > 0 ? min(round($player->mp_now * 100 / $playerDecorator->getMpMax()), 100) : 0; @endphp
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
                                        @foreach($player->skills as $skill)
                                            @php $pct = $skill->exp_up > 0 ? round($skill->exp * 100 / $skill->exp_up, 1) : 0; @endphp
                                            <div class="char-skill-row">
                                                <div class="char-skill-head">
                                                    <span>{{ $skill->skill->name }}: <b>{{ $skill->lvl }}</b></span>
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
        current: parseInt('{{ $player->hp_now }}'),
        max: parseInt('{{ $playerDecorator->getHpMax() }}')
    };
    let mp = {
        current: parseInt('{{ $player->mp_now }}'),
        max: parseInt('{{ $playerDecorator->getMpMax() }}')
    };
    let experience = parseFloat('{{ $player->getPercentExp() }}');
    let lvl = parseInt('{{ $player->lvl }}');

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
            free:  {{ $player->getFreeStats() }},
            bases: {
                str:   {{ $player->getStrength() }},
                int:   {{ $player->getInt() }},
                agil:  {{ $player->getAgility() }},
                intel: {{ $player->getIntelligence() }},
                mud:   {{ $player->getMud() }},
            },
            full: {
                str:   {{ $playerDecorator->getStrength() }},
                int:   {{ $playerDecorator->getInt() }},
                agil:  {{ $playerDecorator->getAgility() }},
                intel: {{ $playerDecorator->getIntelligence() }},
                mud:   {{ $playerDecorator->getMud() }},
            },
            saveUrl: '{{ route('character.point_save') }}',
            csrf:    '{{ csrf_token() }}',
        });
    }
</script>

</body>
</html>
