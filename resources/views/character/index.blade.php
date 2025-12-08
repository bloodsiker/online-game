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
                                    <a id="center_2" href="{{ route('magic_skill', ['group' => 'magic_skill']) }}" title="Книга заклинаний" class="{{ $group === 'magic_skill' ? 'btn_2' : 'btn_1' }}">Книга заклинаний</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'magic_skill' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">


                        <table cellspacing="1" cellpadding="1" border="0" class="b" width="">
                            <tbody>
                            <tr>
                                <td class="tbgr">
                                    <table cellspacing="0" cellpadding="10" width="100%" height="100%">
                                        <tbody>
                                        <tr valign="top">
                                            <td>
                                                <table cellspacing="1" cellpadding="1" border="0" class="b">
                                                    <tbody>
                                                    <tr>
                                                        <td class="tbgr">
                                                            <table cellspacing="1" cellpadding="5" border="0" width="100%" class="b">
                                                                <tbody>
                                                                <tr class="t1">
                                                                    <td colspan="3" align="center" style="padding:0; height: 25px"><b>{{ $player->user->name }}</b></td>
                                                                </tr>
                                                                <tr valign="top" class="l1">
                                                                    <td align="right">

                                                                        <img src="https://skazanie.com/portrait/mm14.jpg" width="130" height="170" border="0" hspace="0" vspace="0" alt="Изменить" title="Изменить">
                                                                        <p>
                                                                            <nobr>
                                                                                Уровень: <b>{{ $player->lvl }}</b> ({{ $player->getPercentExp() }}%)<br>
                                                                                <sup><a href="/help/exp.php" target="_blank">(?)</a></sup>
                                                                                Опыт: <b>{{ $player->exp }}</b><br>след. уровень: <b>{{ $player->exp_up }}</b>
                                                                            </nobr>
                                                                        </p>
                                                                        <p>
                                                                            <nobr>
                                                                                Раса: <b><a href="/b/use.php?bid=21" title="Изменить расу на Майа">{{ $player->race->name }}</a></b><br>
                                                                                Пол: <b>Лорд</b><br>
                                                                                Класс: <b>Воин</b>
                                                                            </nobr>
                                                                        </p>
                                                                        <br>
                                                                        <div align="left">
                                                                            <nobr>
                                                                                <img src="{{ asset('img/icon/money.png') }}" width="40" height="50" border="0" align="left" style="margin: 0 7px 0 2px" alt="Талеры" title="Талеры">
                                                                                С собой: <b>{{ number_format($user->money, 0, '', ' ') }}</b> монеты<br>
                                                                                В банке: <b>0</b>
                                                                            </nobr>
                                                                            <nobr>Номер счета в банке: <b>7131</b></nobr><br>
                                                                            <br style="line-height:5px;">

                                                                            <span>Побед: <b>{{ $player->victory }}</b></span><br>
                                                                            <span>Поражений: <b>{{ $player->death }}</b></span>
                                                                        </div>


                                                                    </td>
                                                                    <td>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0">
                                                                                    <b><nobr><a href="/b/use.php?bid=14" title="Перераспределить">Основные характеристики:</a></nobr></b>
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="l0">
                                                                                <td>
                                                                                    <nobr>
                                                                                        <span>Cила: <b>{{ $player->getStrength() }}</b></span> <small>(2500+788)</small><br>
                                                                                        <span>Интуиция: <b>{{ $player->getInt() }}</b></span> <small>(3526+609)</small><br>
                                                                                        <span>Ловкость:<b>{{ $player->getAgility() }}</b></span> <small>(3526+609)</small><br>
                                                                                        <span>Интеллект: <b>{{ $player->getIntelligence() }}</b></span> <small>(600+669)</small><br>
                                                                                        <span>Мудрость: <b>{{ $player->getMud() }}</b></span> <small>(825+508)</small><br><br>
                                                                                        @if($player->free_stats)
                                                                                            <span>Свободные очки: <b>{{ $player->free_stats }}</b></span><br>
                                                                                            <a href="{{ route('character.point') }}">Распределить</a> »
                                                                                        @endif
                                                                                    </nobr>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <br>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0">
                                                                                    <nobr><b>Физические характеристики:</b></nobr>
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="l0">
                                                                                <td>
                                                                                    <nobr>
                                                                                        Здоровье: <b>{{ $player->hp_now }}</b>/<b>{{ $player->hp_max }}</b> <small>(3419+229)</small><br>
                                                                                        Крит: <b>1317</b> <small>(1170+147)</small><br>
                                                                                        Уворот: <b>920</b> <small>(780+140)</small><br>
                                                                                        Броня: <b>{{ $playerDecorator->getArmor() }}</b><br>
                                                                                    </nobr>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <br>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0">
                                                                                    <nobr><b>Магические характеристики:</b></nobr>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="l0">
                                                                                    <nobr>
                                                                                        Мана: <b>{{ $player->mp_now }}</b>/<b>{{ $player->mp_max }}</b> <small>(718+39)</small>
                                                                                        <br>Атака: <b>237</b> <small>(125+112)</small>
                                                                                    </nobr>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0"><b>Боевые навыки:</b></td>
                                                                            </tr>
                                                                            <tr class="l0">
                                                                                <td>
                                                                                    @foreach($player->skills as $skill)
                                                                                        <span>{{ $skill->skill->name }}: <b>{{ $skill->lvl }}</b></span><small>&nbsp;({{ round($skill->exp * 100 / $skill->exp_up, 2) }}%)</small><br>
                                                                                    @endforeach
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <br>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0"><b>Волшебные навыки:</b></td>
                                                                            </tr>
                                                                            <tr class="l0">
                                                                                <td>Волшебное оружие: <b>1</b><small>&nbsp;(52.46%)</small><br>Колдовство:
                                                                                    <b>14</b><small>&nbsp;(27.13%)</small><br></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <br>
                                                                        <table cellspacing="1" cellpadding="5" border="0" class="b" width="100%">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td class="t0"><b>Мирные умения:</b></td>
                                                                            </tr>
                                                                            <tr class="l0">
                                                                                <td>Охота: <b>15</b><small>&nbsp;(56.15%)</small><br>Зачаровывание
                                                                                    предметов: <b>24</b><small>&nbsp;(67.12%)</small><br>Торговля: <b>32</b><small>&nbsp;(69.39%)</small><br>Торговля
                                                                                    дефицитными товарами: <b>43</b><small>&nbsp;(61.20%)</small><br>Взлом
                                                                                    замков: <b>6</b><small>&nbsp;(40.33%)</small><br>Подводное плавание: <b>17</b><small>&nbsp;(21.67%)</small><br>Рыболовство:
                                                                                    <b>2</b><small>&nbsp;(12.78%)</small><br>Лесозаготовка: <b>17</b><small>&nbsp;(56.01%)</small><br>Горное
                                                                                    дело: <b>16</b><small>&nbsp;(35.08%)</small><br></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <p>
                                                    <a href="/help/character/" target="_blank">Помощь</a> »
                                                </p>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>


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
            case 'c':
                window.parent.sendDataToGame('{{ route('character') }}');
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
        max: parseInt('{{ $player->hp_max }}')
    };
    let mp = {
        current: parseInt('{{ $player->mp_now }}'),
        max: parseInt('{{ $player->mp_max }}')
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
</script>

</body>
</html>
