<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан</title>
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
        .p10h, .p10h td {
            padding-left: 10px;
            padding-right: 10px;
        }
        .p4v, .p4v td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
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
        .brd2, .brd2 td {
            border: 1px solid #DB9F73;
        }
        .dbgl2 {
            background-color: #FFFBD6;
        }

    </style>
</head>
<body class="regblk">

<table class="coll" width="100%" height="100%" border="0">
    
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
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
                                <td align="center" class="tbl-usi_label-center">Заявка на регистрацию клана</td>
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

                        <table class="coll w100 p10h p2v brd2-all">
                            <tbody>
                            <tr class="bg_l">
                                <td align="left" nowrap=""><b>Игроков в клане:</b>&nbsp;&nbsp;&nbsp;<b class="redd">
                                        / </b>
                                    <b>всего:&nbsp;
                                        <b class="redd">1</b> из <b class="redd">40</b> / </b>
                                    <b>online:&nbsp;
                                        <b class="redd">1 / </b>
                                    </b></td>
                            </tr>
                            </tbody>
                        </table>

                        <form method="post" action="clan_management.php?f=1&amp;mode=management&amp;action=save_users"
                              id="form_check_leader_change">
                            <table class="coll w100 p10h p4v brd2" style="margin-top:12px;margin-bottom:12px">
                                <tbody>
                                <tr>
                                    <td class="b" align="center" title="Ник члена клана">Ник игрока</td>
                                    <td class="b" align="center" width="185" title="Звание игрока в вашем клане">Звание в клане</td>
                                    <td class="b" align="center" title="Местоположение игрока">Местоположение</td>
                                    <td class="b" align="center" title="Дата и время последнего входа в игру">Последний вход</td>
                                    <td class="b" align="center" title="Статус игрока (Online/Offline)">Статус</td>
                                    <td class="b" align="center" title="Доступные действия над членом клана">Действия
                                    </td>
                                </tr>

                                <tr class="bg_l">
                                    <td width="10%" nowrap="">
                                        <a href="#" onclick="userPrvTag('Tap0K');return false;" title="Приватное сообщение">
                                            <img src="/images/users-arrow.gif" border="0" width="12" height="10" align="absmiddle">
                                        </a>
                                        &nbsp;
                                        <a href="#" onclick="showClanInfo('20_1');return false;" title="Elders">
                                            <img src="images/data/clans/6082736.gif" border="0" width="13" height="13" align="absmiddle">
                                        </a>&nbsp;
                                        <a>
                                            <b onclick="userToTag('Tap0K');return false;" title="Персональное сообщение" style="cursor:hand">
                                                <b class="kser0" title="">Tap0K&nbsp;[3]</b>
                                            </b>
                                        </a>
                                        &nbsp;
                                        <a href="#" onclick="showUserInfo('Tap0K', 'https://fun-dwar.com/');return false;" title="Информация о персонаже">
                                            <img src="/images/player_info.gif" border="0" width="10" height="10" align="absmiddle"></a>
                                    </td>
                                    <td nowrap="" height="27" width="165">
                                        <select name="form[mem][316][grade]" class="dbgl2 b small" style="width:165px" onchange="leader_change(this);">
                                            <option value="118" selected="">Глава клана</option>
                                            <option value="119">Офицер</option>
                                            <option value="120">Рядовой</option>
                                            <option value="121">Новичок</option>
                                        </select>
                                        <script type="text/javascript">
                                            function leader_change(obj) {
                                                if (obj.value != 118) {
                                                    show_alert = 1;
                                                } else {
                                                    show_alert = 0;
                                                }
                                            }
                                        </script>
                                    </td>
                                    <td width="26%" align="center"><b>Регистрационная палата</b></td>
                                    <td width="16%" align="center"><b>7 июня 2025 01:07</b>
                                    </td>
                                    <td width="18%" align="center">
                                        <b><span class="grnn">online</span></b>
                                    </td>
                                    <td align="right"></td>
                                </tr>
                                </tbody>
                            </table>

                            <script type="text/javascript">
                                function check_leader_change() {
                                    if (!show_alert) return true;
                                    return top.systemConfirm('Сменить главу', '<span>При смене главы все взятые клановые квесты обнулятся.</span><br><br>Смена главы клана в период владения башней ведет к потере контроля над ней!', gebi('form_check_leader_change'));
                                    //return true;
                                }
                            </script>
                            <div align="center">
                                <span class="butt1 pointer">
                                    <span>
                                        <input value="Сохранить изменения" type="submit"
                                              onclick="if (!window.__cfRLUnblockHandlers) return false; return check_leader_change();"
                                              class="grnn"
                                              style="width:160px;">
                                    </span>
                                </span>
                            </div>
                        </form>


                        <form method="post" action="clan_management.php?f=1&amp;mode=management&amp;action=invite_user">
                            <table cellspacing="0" cellpadding="5" border="0">
                                <tbody><tr>
                                    <td valign="middle">Ник игрока</td>
                                    <td valign="middle" width="100"><input type="text" name="form[invite_nick]" value="" class="w100 dbgl2 brd b small"></td>
                                    <td valign="middle"><b class="butt2 pointer"><b><input value="Пригласить в клан" type="submit" onclick="if (!window.__cfRLUnblockHandlers) return false; if(document._submit)return false;document._submit=true;" style="width:130px"></b></b></td>
                                </tr><tr>
                                </tr></tbody></table>
                        </form>

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
