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
            font-size: 12px;
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
<body>

<table class="coll" height="100%" border="0">
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
                        <br>
                        @if(1 == 1)
                            <div align="center"><b class="redd">Форма регистрации</b></div>
                            <br>
                            <form method="post" action="area.php?&amp;mode=register&amp;action=create_request"
                                  enctype="multipart/form-data">
                                <input type="hidden" name="MAX_FILE_SIZE" value="2048">
                                <input type="hidden" name="form[request_id]" value="">

                                <table class="coll w100 p10h p4v brd2">
                                    <colgroup>
                                        <col width="380">
                                        <col>
                                        <col width="260">
                                        <col>

                                    </colgroup>
                                    <tbody>
                                    <tr class="bg_l">
                                        <td><b>Название клана</b> (должно быть уникальным):<br> <b class="redd">Примечание:</b>
                                            максимальная длина названия - <b>30 знаков</b>. Название не может
                                            содержать одновременно русские и английские буквы.<br>Запрещены
                                            названия, которые явно не подходят по тематике к игре, а также
                                            относящиеся к религии.
                                        </td>
                                        <td><input name="form[title]" value="" maxlength="32"
                                                   class="w100 dbgl2 brd b small"></td>
                                    </tr>
                                    <tr>
                                        <td><b>Значок клана:</b><br>
                                            <b class="redd">Примечание:</b> формат значка <b>.gif</b>,
                                            прозрачный фон, разрешение 13x13 пикселей:<br>На значках запрещены
                                            кресты в любом виде, флаги государств, свастика.
                                        </td>
                                        <td nowrap=""><input name="form[picture]" type="file"
                                                             class="dbgl2 brd b small" style="width:300px"></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div align="center">
                                <span class="butt1 pointer ">
                                    <span><input value="Подать заявку" type="submit" class="grnn" style="width:160px;"></span>
                                </span>
                                </div>
                            </form>
                        @else
                            <div align="center"><b>Вы уже состоите в клане!</b></div>
                        @endif

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

        <td valign="top" width="235" height="100%">
            <table width="235" border="0" cellspacing="0" cellpadding="0" height="100%">
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
                                <td align="center" class="tbl-usi_label-center">Информация</td>
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
                        <img src="{{ asset('img/npc/arhivarius.jpg') }}" alt="Эрдинг" width="190"
                             height="171"><br>
                        <div class="p2v">Архивариус Вудугри - архивариус Регистрационной палаты, лицо, уполномоченное регистрировать
                            кланы расы древних и хранить их архивы.
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
