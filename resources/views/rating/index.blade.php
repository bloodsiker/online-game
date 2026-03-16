<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рейтинг</title>
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
            font-size: 11px;
        }
        a, a:link, a:visited, a:active {
            color: #BA0000;
            text-decoration: none;
        }
        .b {
            font-weight: bold;
        }
        img {
            vertical-align: middle;
        }
        .regblk, .regblk * {
            color: #49382D;
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

        .w100 {
            width: 100%;
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

        .tbl-usi-hdr.lc {
            background-position: left -25px;
            width: 27px;
        }
        .tbl-usi-hdr {
            background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat;
            font-family: tahoma, sans-serif;
            height: 22px;
        }
        .c-s-n-fon {
            background: url({{ asset('img/bg/table/c-fon-n-s.gif') }}) left top repeat;
        }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b {
            display: block;
            height: 22px;
            font-size: 0;
            overflow: hidden;
            width: 27px;
        }
        .tbl-usi-hdr.mbg {
            background-position: center -50px;
            background-repeat: repeat-x;
            color: #FCF5B7;
            font-size: 11px;
            font-weight: bold;
            height: 16px;
            padding: 1px 10px 5px 10px;
            line-height: 16px;
            vertical-align: middle;
        }
        .tbl-usi-hdr.rc {
            background-position: right 0;
            width: 27px;
        }
        .ach_menu td, .ach_menu td a {
            color: #775d42 !important;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
        }
        .ach_menu td {
            height: 22px;
            padding-left: 5px;
        }
        .ach_menu_act {
            border: 1px solid #d4a182;
            border-left-width: 0px;
            border-right-width: 0px;
            color: #775d42;
            font-size: 11px;
            font-weight: bold;
            height: 22px;
            padding-left: 5px;
        }
        .ach_menu td img {
            margin-right: 6px;
            border: 0px;
        }
        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .pg, .pg td {
            color: #8D2616;
            height: 17px;
            text-align: center;
            vertical-align: center !important;
            padding-left: 1px;
            padding-right: 1px;
        }
        .pg-inact {
            margin: 1px;
            padding: 1px 0 0 2px;
            text-align: center;
            background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center;
            background-repeat: no-repeat;
            height: 17px;
            width: 17px;
        }
        .pg-act {
            margin: 1px;
            padding: 1px 0 0 2px;
            text-align: center;
            background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center;
            height: 17px;
            width: 17px;
        }
        .pg-inact_lnk {
            color: #C50000 !important;
            font-size: 9px;
            font-weight: bold;
        }
        .pg-act_lnk {
            color: #FFF3D2 !important;
            font-size: 9px;
            font-weight: bold;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
        }
        .dbgl {
            background-color: #FFE7C5;
        }
        table.user-rating {
            border-top: #db9f73 1px solid;
            border-left: #db9f73 1px solid;
            border-collapse: separate !important;
        }
        .user-rating td, .user-rating th {
            height: 15px;
            padding: 4px;
            color: #631c0b;
            border-right: #db9f73 1px solid;
            border-bottom: #db9f73 1px solid;
        }
        .user-rating th, .user-rating th span {
            color: #955c4a;
        }
        .user-rating-red, .user-rating-red b {
            font-weight: bold;
            color: #ba0000 !important;
        }
        .user-rating th span {
            padding: 0 0 1px 20px;
            background: url({{ asset('img/icon/rating_headers_sprite.png') }}) 0 0 no-repeat;
        }
        .user-rating th .user-rating-reputation {
            background-position: 0 -44px;
        }
        .user-rating th .user-rating-progress {
            background-position: 0 -67px;
        }
    </style>
</head>
<body class="regblk">

<table cellspacing="0" cellpadding="0" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td width="1%"  valign="top">
            <table width="250px" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Рейтинги</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                        <table class="w100 ach_menu" cellpadding="0" cellspacing="0">
                            <tbody>
                                @foreach($menu as $key => $menuItem)
                                    <tr>
                                        <td height="10" class="@if($key === $type) c-s-n-fon ach_menu_act @else ach_menu_pas @endif">
                                            <a href="{{ route('rating', ['type' => $key]) }}">
                                                <img src="{{ asset('img/icon/users-arrow.gif') }}" alt="{{ $menuItem['title'] }}" align="absMiddle">{{ $menuItem['title'] }}
                                            </a>
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
        <td  valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">{{ $menu[$type]['title'] }}</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 4px 0 4px 0">

                        <table class="coll" cellspacing="0">
                            <tbody>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" class="pg w100">
                                        <tbody>
                                        <tr>
                                            <td class="b" width="10">
                                                <nobr>Страницы:&nbsp;</nobr>
                                            </td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=0"
                                                    class="pg-inact_lnk">1</a></td>
                                            <td class="pg-act"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=1"
                                                    class="pg-act_lnk">2</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=2"
                                                    class="pg-inact_lnk">3</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=3"
                                                    class="pg-inact_lnk">4</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=4"
                                                    class="pg-inact_lnk">5</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=5"
                                                    class="pg-inact_lnk">6</a></td>
                                            <td style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Найти
                                                    игрока:</strong>&nbsp;<input id="user_nick" value=""
                                                                                 onkeydown="if(event.keyCode==13)search_user(this.value);"
                                                                                 class="brd dbgl"
                                                                                 style="width: 128px;">&nbsp;&nbsp;<b
                                                    class="butt2 pointer "><b><input value="Поиск" type="button"
                                                                                     onclick="return search_user(gebi('user_nick').value);"
                                                                                     style="width:50px"></b></b>&nbsp;&nbsp;&nbsp;&nbsp;<b
                                                    class="butt2 pointer "><b><input value="Найти себя"
                                                                                     type="button"
                                                                                     onclick="return search_me();"
                                                                                     style="width:100px"></b></b>
                                            </td>
                                            <td width="1%" style="text-align:right" nowrap="">
                                                <a href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=0">
                                                    <img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                                </a>
                                                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                                                <a href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=2">
                                                    <img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая">
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td>
                            </tr>
                            </tbody>
                        </table>



                        <table width="60%" class="coll user-rating" cellspacing="0">
                            <thead>
                            <tr class="">
                                <th align="left" width="50">№</th>
                                <th align="left">Ник игрока</th>
                                <th width="140"><span class="user-rating-progress">{{ $menu[$type]['name'] }}</span></th>
                            </tr><tr>
                            </tr></thead>
                            <tbody>
                            @foreach($players as $player)
                                <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                                    <td class="rating-nowrap" align="center">
                                        <span class="user-rating-red">{{ $players->firstItem() + $loop->index }}</span>
                                    </td>
                                    <td>
                                        <span class="user-rating-red">
                                            <a href="#" onclick="userPrvTag('хаирман');return false;" title="Приватное сообщение">
                                                <img src="{{ asset('img/icon/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                            </a>
                                            @if($player->user->clanMembership)
                                                <a href="#" onclick="showClanInfo('2_1');return false;" title="Alliance">
                                                    <img src="{{ asset('img/resource/tmp_clan.gif') }}" border="0" width="13" height="13" align="absmiddle">
                                                </a>
                                            @endif
                                            <a>
                                                <b onclick="userToTag('хаирман');return false;" title="Персональное сообщение" style="cursor:hand">
                                                    <b class="kser0" title="">{{ $player->user->name }}&nbsp;[{{ $player->lvl }}]</b>
                                                </b>
                                            </a>
                                            <a href="#" onclick="showUserInfo('%D1%85%D0%B0%D0%B8%D1%80%D0%BC%D0%B0%D0%BD', 'https://feo-dwar.com/');return false;" title="Информация о персонаже">
                                                <img src="{{ asset('img/icon/player_info.gif') }}" border="0" align="absmiddle">
                                            </a>
                                        </span>
                                    </td>
                                    <td align="center"><span class="">{{ data_get($player, $menu[$type]['column']) }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>



                        <table class="coll" cellspacing="0">
                            <tbody>
                            <tr>
                                <td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" class="pg w100">
                                        <tbody>
                                        <tr>
                                            <td class="b" width="10">
                                                <nobr>Страницы:&nbsp;</nobr>
                                            </td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=0"
                                                    class="pg-inact_lnk">1</a></td>
                                            <td class="pg-act"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=1"
                                                    class="pg-act_lnk">2</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=2"
                                                    class="pg-inact_lnk">3</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=3"
                                                    class="pg-inact_lnk">4</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=4"
                                                    class="pg-inact_lnk">5</a></td>
                                            <td class="pg-inact"><a
                                                    href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=5"
                                                    class="pg-inact_lnk">6</a></td>
                                            <td style="text-align: left;"></td>
                                            <td width="1%" style="text-align:right" nowrap="">
                                                <a href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=0">
                                                    <img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                                </a>
                                                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                                                <a href="user_ratings.php?&amp;mode=global&amp;submode=rating&amp;page=2">
                                                    <img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая">
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td>
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

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
