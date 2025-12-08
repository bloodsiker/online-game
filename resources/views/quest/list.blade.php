<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квесты</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            font-family: Tahoma;
            font-size: 11px;
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
<body class="regcolor">

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
        <td width="19"><img id="left_1" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_1" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_1" href="" title="Взятые" class="btn_2">Взятые</a>
        </td>
        <td width="19"><img id="right_1" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_2" href="" title="Повторяющиеся" class="btn_1">Повторяющиеся</a></td>
        <td width="19"><img id="right_2" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td width="19"><img id="left_3" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_3" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_3" href="" title="Завершенные" class="btn_1">Завершенные</a></td>
        <td width="19"><img id="right_3" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_4" href="{{ route('location') }}" title="Вернуться" class="btn_1">Вернуться</a></td>
        <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table class="coll w-100" cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr height="13">
        <td valign="top"></td>
    </tr>
    <tr>
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td>
                        <span class="butt1 pointer ">
                            <span><input value="Свернуть/Развернуть" type="button" onclick="quest_folding.toggle_all();"></span>
                        </span>
                    </td>

                    <td align="right" style="padding-right: 30px">
                        <b class="ajustify b">На странице по:
                            <select name="page_size" onchange="document.location.href=&quot;user_quest.php?mode=started&amp;page_size=&quot;+this.value">
                                <option value="10" selected="">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </b>

                    </td>
                </tr>
                </tbody>
            </table>

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
                                            <td align="center" class="tbl-usi_label-center">Список квестов</td>
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
                                <td class="tbl-usi_bg" valign="top" align="left" style="padding: 6px 4px 6px 4px">
                                    <table class="coll w100 p6h p2v brd2-all">
                                        <tbody>
                                        @foreach($quests as $quest)
                                            <tr class="bg_l" onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top" width="1">
                                                    <img id="image_open_quest_{{ $quest->quest_id }}" onclick="quest_folding.toggle({{ $quest->quest_id }});"
                                                         src="{{ asset('img/icon/qst_minus.gif') }}" width="9" height="9" border="0">
                                                </td>
                                                <td class="brd2-top b" onclick="quest_folding.toggle({{ $quest->quest_id }});">{{ $quest->quest->title }}</td>
                                                <td class="brd2-top" align="right">
                                                    <b class="butt2 pointer ">
                                                        <b>
                                                            <input value="Отказаться" type="button" onclick="return top.systemConfirm('Вы действительно хотите отказаться от квеста?','Действие',false,function(){location.href='user_quest.php?mode=started&amp;action=cancel&amp;ref=138'});">
                                                        </b>
                                                    </b>
                                                </td>
                                            </tr>
                                            <tr class="bg_l" id="quest_{{ $quest->quest_id }}">
                                                <td></td>
                                                <td colspan="2">
                                                    <div class="ajustify b">{!! $quest->quest->description !!}</div>
                                                    <img src="images/d.gif" width="1" height="6"><br>
                                                    <table class="coll">
                                                        <tbody>
                                                        <tr class="redd b">
                                                            <td nowrap="">Текущая цель:</td>
                                                            <td>Убивая <span class="underline">
                                                                    <a href="#" onclick="showMsg('navigator.php?name=Дряхлый скелет [2]','Навигатор',560,423);return false;">
                                                                        <img src="/images/compass.png" class="compass">Дряхлых скелетов</a>
                                                                </span>, добудьте <span class="underline">
                                                                    <a href="#" class="artifact_info b macros_artifact_quality0" onclick="showArtifactInfo(false,1010);return false;">черепов</a></span>
                                                                (10 шт) и принесите их <span class="underline">
                                                                    <a href="#" onclick="showMsg('navigator.php?name=Оккультист Коэшу','Навигатор',560,423);return false;">
                                                                        <img src="/images/compass.png" class="compass">оккультисту Коэшу</a>
                                                                </span>в <span class="underline">
                                                                    <a href="#" style="text-decoration: underline;" onclick="showMsg('navigator.php?name=Клановые захоронения','Навигатор',560,423);return false;">
                                                                        <img src="/images/compass.png" class="compass">Клановые захоронения</a></span>.
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endforeach

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
        </td>
    </tr>
    </tbody>
</table>

<script type="text/javascript" src="{{ asset('js/cookie.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/folding.js') }}" ></script>

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

<script>
    var quest_folding = new ListFolding({
        id_btn:             'image_open_quest_',
        id_expanded:        'quest_',
        cookie_save_state:  'quests_',
        init_state:			'expanded',
        id_list:            [{{ $questIds }}]
    });
    quest_folding.refresh();
</script>

</body>
</html>
