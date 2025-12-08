<html>
<head>
    <title>Map</title>
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
        * {
            font-family: Tahoma,Geneva,sans-serif;
            font-size: 11px
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
        .line-align {
            display: flex;
            align-items: center;
            gap: 3px;
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
                                            Город Нейрин
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

                                                            <table width="490" border="0" cellspacing="0"
                                                                   cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                    <td class="tbl-shp_sml-top" valign="top" align="center"></td>
                                                                    <td width="20" align="left" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px; text-align: justify;">
                                                                        @include('maps.city.frame')
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
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="mrg-top">
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
                                                                                            <td><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt></td>
                                                                                            <td class="tbl-usi_label-center">
                                                                                                Проходы в другие области
                                                                                            </td>
                                                                                            <td><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt></td>
                                                                                        </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                                <td width="20" align="left" valign="bottom">
                                                                                    <img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="tbl-usi_left">&nbsp;</td>
                                                                                <td class="tbl-usi_bg" valign="top"
                                                                                    style="padding: 6px 4px; text-align: justify;">
                                                                                    <table class="coll w100 p10h p2v brd2-all">
                                                                                        <tbody>
                                                                                        <tr class="bg_l">
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                    <span class="line-align" onclick="mark_l(3493,2)"
                                                                                          onmouseover="mark_l(3493,1)"
                                                                                          onmouseout="mark_l(3493,0)"><span
                                                                                            class="listloc">3493</span> <span
                                                                                            style="font-size:18px;">→</span> <a
                                                                                            href="/i/map.php?m=MzcwMQ#3494">Домик охотника</a> <span
                                                                                            class="listloc">3494</span></span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class>
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                    <span class="line-align" onclick="mark_l(3571,2)"
                                                                                          onmouseover="mark_l(3571,1)"
                                                                                          onmouseout="mark_l(3571,0)"><span
                                                                                            class="listloc">3571</span> <span
                                                                                            style="font-size:18px;">→</span> <a
                                                                                            href="/i/map.php?m=MjAwMA#2000">Эльфийский лес</a> <span
                                                                                            class="listloc">2000</span></span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class="bg_l">
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                    <span class="line-align" onclick="mark_l(3503,2)"
                                                                                          onmouseover="mark_l(3503,1)"
                                                                                          onmouseout="mark_l(3503,0)"><span
                                                                                            class="listloc">3503</span> <span
                                                                                            style="font-size:18px;">→</span> <a
                                                                                            href="/i/map.php?m=MjAxMQ#1430">Холмы хоббитов</a> <span
                                                                                            class="listloc">1430</span></span>
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
                                                                    </td>
                                                                    <td>
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="mrg-top">
                                                                            <tbody>
                                                                            <tr height="22">
                                                                                <td width="20" align="right" valign="bottom">
                                                                                    <img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt>
                                                                                </td>
                                                                                <td class="tbl-shp_sml-top" valign="top" align="center">
                                                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                                                        <tbody>
                                                                                        <tr height="22">
                                                                                            <td><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt></td>
                                                                                            <td class="tbl-usi_label-center">
                                                                                                Условные обозначения
                                                                                            </td>
                                                                                            <td><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt></td>
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
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                                <span class="line-align"
                                                                                                    style="cursor:pointer;cursor:hand;margin-top:6px;display:inline-block;"
                                                                                                    onclick="area_click(1)"
                                                                                                    onmouseover="area_show(1,1)"
                                                                                                    onmouseout="area_show(1,0)"><span
                                                                                                        class="a1 listloc"
                                                                                                        style="">&nbsp;</span> - Город Нейрин</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class>
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                                <span class="line-align"
                                                                                                    style="cursor:pointer;cursor:hand;margin-top:6px;display:inline-block;"
                                                                                                    onclick="area_click(3028)"
                                                                                                    onmouseover="area_show(3028,1)"
                                                                                                    onmouseout="area_show(3028,0)"><span
                                                                                                        class="a3028 listloc"
                                                                                                        style="">&nbsp;</span> - Пастбища</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr class="bg_l">
                                                                                            <td class="brd2-top brd2-bt b">
                                                                                                <a href="/i/map.php" class="line-align"><span
                                                                                                        style="margin-top:6px;display:inline-block;"><span
                                                                                                            class="ulocation"><span
                                                                                                                class="listloc"
                                                                                                                id="lid">135</span></span> - Вы здесь</span></a>
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
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>




                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="mrg-top">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                    <td class="tbl-shp_sml-top" valign="top" align="center">
                                                                    </td>
                                                                    <td width="20" align="left" valign="bottom"><img
                                                                            src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}"
                                                                            width="20" height="22" alt></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top"
                                                                        style="padding: 6px 4px; text-align: justify;">
                                                                        <p>
                                                                            <sup>1</sup>  Кликните на цифру в прямоугольнике, чтобы подсветить локацию.
                                                                        </p>
                                                                        <p>
                                                                            <sup>2</sup>  Кликните на закрашенный прямоугольник, чтобы выделить местность.
                                                                        </p>
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
