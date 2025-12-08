<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quest->title }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }

        table[cellpadding="0"][cellpadding="0"][border="0"] {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .tbl-usi_label-center {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 3px;
        }
        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd2-all {
            border: 1px solid #DB9F73;
        }
        .bg_l {
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .bg_l2 {
            background-image: url(/img/bg/info/bg_l2.gif);
            cursor: pointer;
        }

        .tbl-shp_menu-center-inact {
            background-image: url(/img/bg/btn/tbl-shp_menu-center-inact.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            padding-left: 4px;
            padding-right: 4px;
            padding-bottom: 2px;
        }
        .tbl-shp_menu-center-act {
            background-image: url(/img/bg/btn/tbl-shp_menu-center-act.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            padding-left: 4px;
            padding-right: 4px;
            padding-bottom: 2px;
            color: #FFE9BA;
            font-weight: bold;
            width: auto;
        }
        .tbl-shp_menu-link_act {
            color: #FFE9BA !important;
            text-decoration: none;
        }
        .tbl-shp_menu-link_inact {
            color: #461C0B !important;
            text-decoration: none;
        }
        .tbl-sts_top {
            background-image: url(/img/bg/btn/tbl-sts_top.gif);
            background-repeat: repeat-x;
            background-position: bottom;
            height: 19px;
        }
        .tbl-sts b {
            background: url(/img/bg/tbl-sts.png) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }

    </style>
</head>
<body>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    @php
        $group = 'main';
        $btnLeft1 = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1 = 'img/bg/btn/btn-right1.gif';

        $btnLeft2 = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2 = 'img/bg/btn/btn-right2.gif';
    @endphp
    <tr height="21">
        <td width="19"><img id="left_1" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_1" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_1" href="{{ route('npc', ['id' => $npc->id]) }}" title="Квесты" class="btn_1">Квесты</a>
        </td>
        <td width="19"><img id="right_1" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="140" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_4" href="{{ route('location') }}" title="Вернуться в локацию" class="btn_1">Вернуться в локацию</a></td>
        <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr>
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <table class="coll w100" height="100%">
                <tbody>
                <tr>
                    <td valign="top" width="100%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="27">
                                                <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                            </td>
                                            <td align="center" class="tbl-usi_label-center">Этап квеста</td>
                                            <td width="27"
                                            ><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    <table class="coll w100 p6h p6v">
                                        <tbody>
                                        <tr class="bg_l brd2-all"
                                            onclick="location.href='{{ route('npc', ['id' => $npc->id]) }}'"
                                            onmouseover="this.className += ' bg_l2';"
                                            onmouseout="this.className = this.className.split(' bg_l2').join('');">
                                            <td width="1%">
                                                <img src="{{ asset('img/icon/qst_main_start.gif') }}" width="46" height="28">
                                            </td>
                                            <td class="redd2 b fs-13">Разговор о бое</td>
                                            <td align="right"></td>
                                        </tr>
                                        <tr class=" fs-12">
                                            <td class="ajustify" colspan="3">
                                                {!! $quest->description !!}
                                            </td>
                                        </tr>


                                        <tr class="bg_l brd2-all fs-12">
                                            <td width="1%">
                                                <img src="{{ asset('img/icon/qst_goal.png') }}" alt="">
                                            </td>
                                            <td class="b" colspan="2">
                                                <span class="brown">Ваша цель:</span>
                                                <span class="redd">{{ $quest->objective->description }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" align="center">
                                                <span class="butt1 pointer">
                                                    <span><input value="Далее" type="submit" onclick="location.href='{{ route('quest.take', ['id' => $quest->id, 'npc' => $npc->id]) }}'" class="grnn"></span>
                                                </span>
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

                    <td width="16"><img src="images/d.gif" width="16" height="1"></td>

                    <td valign="top" width="202" height="100%">
                        <table width="240" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="27">
                                                <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                            </td>
                                            <td align="center" class="tbl-usi_label-center">{{ $npc->name }}</td>
                                            <td width="27">
                                                <img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    <img src="{{ asset('img/npc/stareyshina.jpg') }}" alt="Эрдинг" width="190"
                                         height="171"><br>
                                    <div class="p2v">Глубокий шрам, навсегда изменивший лицо молодого воина, придает его
                                        облику еще больше суровости. Немногословный и категоричный в принятии решений
                                        Эрдинг не ведает, что такое страх, являясь неизменным защитником своего народа и
                                        Руменгильда.
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
        </td>
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'i':--}}
    {{--            window.parent.sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            window.parent.sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}
</script>

</body>
</html>
