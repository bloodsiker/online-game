<html>
<head>
    <title>{{ $monster->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="Description" content=""/>
    <script type="text/javascript">
        var art_alt = new Array();
        top.obj = null;
        top.noIframeAlt = 1;

        function show_alt() {
            var alive = 0;

            try {
                alive = top.obj.offsetParent
            } catch(e) {}

            var obj = top.gebi('artifact_alt');

            if (!obj) return false;

            if (alive && obj.style.display != 'none') {
                setTimeout("show_alt()", 500);
            } else {
                obj.style.display='none';
            }
        }
    </script>
    <style>
        *{
            font-family:Tahoma,Geneva,sans-serif;
            font-size:11px
        }
        .b {
            font-weight: 700;
        }
        .bg2 {
            background-color: #000;
            background-image: url({{ asset('img/bg/bg2.gif') }});
        }
        .regcolor, .regcolor * {
            color: #955c4a;
        }
        .common-block {
            position: relative;
        }
        .common-block .corner-tl {
            position: absolute;
            top: -19px;
            left: -23px;
            width: 141px;
            height: 176px;
            background: url({{ asset('img/bg/info/common-block-tl.png') }}) 0 0 no-repeat;
        }
        .common-block__red .corner-tl {
            background: url({{ asset('img/bg/info/common-block-red-tl.png') }}) 0 0 no-repeat;
        }
        .common-block .corner-tr {
            position: absolute;
            top: -19px;
            right: -24px;
            width: 146px;
            height: 176px;
            background: url({{ asset('img/bg/info/common-block-tr.png') }}) 0 0 no-repeat;
        }
        .common-block__red .corner-tr {
            background: url({{ asset('img/bg/info/common-block-red-tr.png') }}) 0 0 no-repeat;
        }
        .common-block .corner-bl {
            position: absolute;
            bottom: -19px;
            left: -19px;
            width: 238px;
            height: 127px;
            background: url({{ asset('img/bg/info/common-block-bl.png') }}) 0 0 no-repeat;
        }
        .common-block__red .corner-bl {
            background: url({{ asset('img/bg/info/common-block-red-bl.png') }}) 0 0 no-repeat;
        }
        .common-block .corner-br {
            position: absolute;
            bottom: -19px;
            right: -21px;
            width: 241px;
            height: 128px;
            background: url({{ asset('img/bg/info/common-block-br.png') }}) 0 0 no-repeat;
        }
        .common-block__red .corner-br {
            background: url({{ asset('img/bg/info/common-block-red-br.png') }}) 0 0 no-repeat;
        }
        .common-block .corner-br {
            position: absolute;
            bottom: -19px;
            right: -21px;
            width: 241px;
            height: 128px;
            background: url({{ asset('img/bg/info/common-block-br.png') }}) 0 0 no-repeat;
        }
        .common-block .bg-t {
            height: 41px;
            margin: 0 39px;
            text-align: center;
            background: url({{ asset('img/bg/info/common-block-t.png') }}) 0 100% repeat-x;
        }
        .common-block .bg-l {
            background: url({{ asset('img/bg/info/common-block-l.png') }}) 0 0 repeat-y;
        }
        .common-block .bg-b {
            height: 41px;
            margin: 0 39px;
            background: url({{ asset('img/bg/info/common-block-b.png') }}) 0 0 repeat-x;
        }
        .common-block .bg-r {
            padding: 0 39px;
            background: url({{ asset('img/bg/info/common-block-r.png') }}) 100% 0 repeat-y;
        }
        .common-header {
            position: relative;
            top: 7px;
            z-index: 1;
            height: 38px;
            padding: 0 0 0 192px;
            background: url({{ asset('img/bg/info/common-header.png') }}) 0 0 no-repeat;
        }
        .common-header__small {
            position: relative;
            top: 11px;
            z-index: 1;
            height: 39px;
            padding: 0 0 0 87px;
            background: url({{ asset('img/bg/info/common-header-small.png') }}) 0 0 no-repeat;
        }
        .common-header__small .h-inner {
            height: 39px;
            padding: 0 97px 0 10px;
            background: url({{ asset('img/bg/info/common-header-small.png') }}) 100% -39px no-repeat;
        }
        .common-header__small .h-txt, .common-header a, .common-header b {
            color: #faf7b9;
        }
        .common-header .h-txt {
            padding: 10px 0 0;
            font-weight: 700;
            font-size: 12px;
            text-align: center;
        }
        .common-header__small .h-txt {
            padding: 10px 0 0;
            font-weight: 700;
            font-size: 11px;
            text-align: center;
        }
        .common-header, .common-header .h-inner, .common-header .h-txt {
            display: inline-block;
        }
        .common-block .bg-inner {
            background: url({{ asset('img/bg/bgg2.gif') }}) repeat;
        }
        .common-block .bg-inner-l {
            background: url({{ asset('img/bg/info/common-block-inner-l.png') }}) 0 0 repeat-y;
        }
        .common-block__red .bg-inner-l {
            background: url({{ asset('img/bg/info/common-block-inner-red-l.png') }}) 0 0 repeat-y;
        }
        .common-block .bg-inner-r {
            background: url({{ asset('img/bg/info/common-block-inner-r.png') }}) 100% 0 repeat-y;
        }
        .common-block__red .bg-inner-r {
            background: url({{ asset('img/bg/info/common-block-inner-red-r.png') }}) 100% 0 repeat-y;
        }
        .common-block .bg-inner-t {
            margin: 0 12px;
            background: url({{ asset('img/bg/info/common-block-inner-t.png') }}) 0 0 repeat-x;
            zoom: 1;
        }
        .common-block .bg-inner-b {
            padding: 20px 18px;
            background: url({{ asset('img/bg/info/common-block-inner-b.png') }}) 0 100% repeat-x;
            zoom: 1;
        }
        .common-block .bg-b {
            height: 41px;
            margin: 0 39px;
            background: url({{ asset('img/bg/info/common-block-b.png') }}) 0 0 repeat-x;
        }
        .common-block .common-content {
            position: relative;
            z-index: 2;
        }
        .mrg-top {
            margin-top: 7px;
        }
        .tbl-shp_sml-top {
            background-image: url({{ asset('img/bg/info/tbl-shp_sml-top.gif') }});
            background-repeat: repeat-x;
            height: 22px;
            font-size: 1px;
        }
        .tbl-usi_label-center {
            background-image: url({{ asset('img/bg/info/tbl-usi_label-center.gif') }});
            background-repeat: repeat-x;
            height: 22px;
            font-family: Tahoma;
            font-weight: 700;
            font-size: 11px;
            color: #fcf5b7;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 3px;
        }
        .tbl-usi_left {
            background-image: url({{ asset('img/bg/info/tbl-usi_left.gif') }});
            background-repeat: repeat-y;
            background-position: right;
            width: 20px;
        }
        .tbl-usi_bg {
            background-image: url({{ asset('img/bg/info/tbl-usi_bg.gif') }});
            background-repeat: repeat;
        }
        .tbl-usi_right {
            background-image: url({{ asset('img/bg/info/tbl-usi_right.gif') }});
            background-repeat: repeat-y;
            width: 20px;
        }
        .tbl-shp_sml-bottom {
            background-image: url({{ asset('img/bg/info/tbl-shp_sml-bottom.gif') }});
            background-repeat: repeat-x;
            height: 18px;
            font-size: 1px;
        }
        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd2-all {
            border: 1px solid #db9f73;
        }
        .w100 {
            width: 100%;
        }
        .p10h, .p10h td {
            padding-left: 10px;
            padding-right: 10px;
        }
        .p2v, .p2v td {
            padding-top: 2px;
            padding-bottom: 2px;
        }
        .brd2-bt {
            border-bottom: 1px solid #db9f73;
        }
        .brd2-top {
            border-top: 1px solid #db9f73;
        }
        .bg_l {
            background-image: url({{ asset('img/bg/info/bg_l.gif') }});
        }
        .redd, .redd * {
            color: #ba0000 !important;
        }
    </style>
</head>
<body class="bg2 regcolor" topmargin="0" leftmargin="0">
<div id="artifact_alt" style="width:300px; position:absolute; z-index: 1001;"></div>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td align="center" valign="middle">
            <table height="10%" width="610" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr>
                    <td height="25">
                        &nbsp;
                        <h1 class="ext-logo" style="display: none">
                            <a href="/"><img src="" alt="" border="0" class="logo-main"></a>
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="common-block common-block__red">
                            <div class="corner-tl"></div>
                            <div class="corner-tr"></div>
                            <div class="corner-bl"></div>
                            <div class="corner-br"></div>
                            <div class="bg-t">
                                <div class="common-header common-header__small">
                                    <div class="h-inner">
                                        <div class="h-txt">
                                            {{ $monster->name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-l">
                                <div class="bg-r">
                                    <div class="bg-inner">
                                        <div class="bg-inner-l">
                                            <div class="bg-inner-r">
                                                <div class="bg-inner-t">
                                                    <div class="bg-inner-b">
                                                        <div class="common-content">
                                                            <img src="https://fun-dwar.com/images/data/bots/skelet.jpg" width="490"
                                                                 height="450" alt="Восставший дряхлый скелет"
                                                                 border="0"><br>
                                                            <table width="490" border="0" cellspacing="0"
                                                                   cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                    <td class="tbl-shp_sml-top" valign="top"
                                                                        align="center">
                                                                        <table border="0" cellspacing="0"
                                                                               cellpadding="0">
                                                                            <tbody>
                                                                            <tr height="22">
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                                <td class="tbl-usi_label-center">
                                                                                    Описание
                                                                                </td>
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td width="20" align="left" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top"
                                                                        style="padding: 6px 4px; text-align: justify;">
                                                                        Озлобленный дух погибшего солдата, представитель
                                                                        нежити, он враг всем живым существам.
                                                                    </td>
                                                                    <td class="tbl-usi_right">&nbsp;</td>
                                                                </tr>
                                                                <tr height="18">
                                                                    <td width="20" align="right" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                    <td class="tbl-shp_sml-bottom" valign="top"
                                                                        align="center">&nbsp;
                                                                    </td>
                                                                    <td width="20" align="left" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                            <table width="490" border="0" cellspacing="0"
                                                                   cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                    <td class="tbl-shp_sml-top" valign="top"
                                                                        align="center">
                                                                        <table border="0" cellspacing="0"
                                                                               cellpadding="0">
                                                                            <tbody>
                                                                            <tr height="22">
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                                <td class="tbl-usi_label-center">
                                                                                    Информация
                                                                                </td>
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td width="20" align="left" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top"
                                                                        style="padding: 6px 4px; text-align: justify;">
                                                                        <table class="coll w100 p10h p2v brd2-all">
                                                                            <tbody>
                                                                            <tr class="bg_l">
                                                                                <td class="brd2-top brd2-bt b">Жизнь
                                                                                </td>
                                                                                <td class="brd2-top brd2-bt b redd"
                                                                                    align="right">2996
                                                                                </td>
                                                                            </tr>
                                                                            <tr class>
                                                                                <td class="brd2-top brd2-bt b">Урон</td>
                                                                                <td class="brd2-top brd2-bt b redd"
                                                                                    align="right">196.1 .. 283.0
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="bg_l">
                                                                                <td class="brd2-top brd2-bt b">Денежная
                                                                                    награда
                                                                                </td>
                                                                                <td class="brd2-top brd2-bt b redd"
                                                                                    align="right"><span
                                                                                        title="Серебряный"><img
                                                                                            src="https://fun-dwar.com//images/m_game2.gif"
                                                                                            border="0" width="11"
                                                                                            height="11"
                                                                                            align="absmiddle"></span>&nbsp;16
                                                                                    .. <span title="Серебряный"><img
                                                                                            src="https://fun-dwar.com//images/m_game2.gif"
                                                                                            border="0" width="11"
                                                                                            height="11"
                                                                                            align="absmiddle"></span>&nbsp;21
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td class="tbl-usi_right">&nbsp;</td>
                                                                </tr>
                                                                <tr height="18">
                                                                    <td width="20" align="right" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                    <td class="tbl-shp_sml-bottom" valign="top"
                                                                        align="center">&nbsp;
                                                                    </td>
                                                                    <td width="20" align="left" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                            <table width="490" border="0" cellspacing="0"
                                                                   cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                    <td class="tbl-shp_sml-top" valign="top"
                                                                        align="center">
                                                                        <table border="0" cellspacing="0"
                                                                               cellpadding="0">
                                                                            <tbody>
                                                                            <tr height="22">
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                                <td class="tbl-usi_label-center">
                                                                                    Ценности
                                                                                </td>
                                                                                <td><img
                                                                                        src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}"
                                                                                        width="27" height="22" alt></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td width="20" align="left" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top"
                                                                        style="padding: 6px 4px; text-align: justify;">
                                                                        <ul style="float: left; margin: 0; padding: 0 0 0 0; list-style-type: none; width: 50%;">
                                                                            <li style="color: #000; margin: 0 0 5px;margin: 0 0 5px;">
                                                                                <b>- Боевые эффекты и припасы -</b></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,689);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Эликсир
                                                                                    крови</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,2659);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Вампирик</a>
                                                                            </li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,51);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Эликсир
                                                                                    мощи</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,54);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Эликсир
                                                                                    жизни</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality0"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,67821);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Малый
                                                                                    эликсир мудрости</a></li>
                                                                            <br>
                                                                            <li style="color: #000; margin: 0 0 5px;margin: 0 0 5px;">
                                                                                <b>- Снаряжение -</b></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality4"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,67669);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Книга
                                                                                    осквернения Хаосом</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,67514);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Кольчуга
                                                                                    Бесстрашия</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality0"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,269);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Перевязь
                                                                                    странника</a></li>
                                                                            <br>
                                                                            <li style="color: #000; margin: 0 0 5px;margin: 0 0 5px;">
                                                                                <b>- Квестовые ресурсы -</b></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality0"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,1109);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Череп
                                                                                    вершителя зла</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality0"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,1455);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Гномья
                                                                                    руна Яз</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality0"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,70100);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Черновая
                                                                                    заготовка</a></li>
                                                                            <br>
                                                                            <li style="color: #000; margin: 0 0 5px;margin: 0 0 5px;">
                                                                                <b>- Коллекции -</b></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,7476);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Бронзовая
                                                                                    монета «Беронский ти</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality3"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,7493);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Золотая
                                                                                    монета «Церрадор»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality3"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,7494);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Золотая
                                                                                    монета «Джахарал»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality3"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,7495);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Золотая
                                                                                    монета «Грумвол»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality3"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,7496);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Золотая
                                                                                    монета «Хагридорф»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,64351);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Бронзовая
                                                                                    монета «2023»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality3"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,64353);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Золотая
                                                                                    монета «2023»</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality2"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,64352);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Серебряная
                                                                                    монета «2023» </a></li>
                                                                            <br>
                                                                            <li style="color: #000; margin: 0 0 5px;margin: 0 0 5px;">
                                                                                <b>- Закрытые сундуки -</b></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,1281);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Закрытый
                                                                                    бронзовый ларец</a></li>
                                                                            <li><a href="#"
                                                                                   class="artifact_info b macros_artifact_quality1"
                                                                                   onClick="if (!window.__cfRLUnblockHandlers) return false; showArtifactInfo(false,1282);return false;"
                                                                                   data-cf-modified-53b7206bb455032014d450b4->Закрытый
                                                                                    металлический ларец</a></li>
                                                                            <br></ul>
                                                                    </td>
                                                                    <td class="tbl-usi_right">&nbsp;</td>
                                                                </tr>
                                                                <tr height="18">
                                                                    <td width="20" align="right" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                    <td class="tbl-shp_sml-bottom" valign="top"
                                                                        align="center">&nbsp;
                                                                    </td>
                                                                    <td width="20" align="left" valign="top"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}"
                                                                            width="20" height="18" alt></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-b"></div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
