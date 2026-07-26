<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $page->name }} - информация о предмете</title>
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            font-family: Tahoma, Geneva, sans-serif;
            font-size: 11px;
            background-image: url({{ asset('main/images/bg2.gif') }});
        }
        .regcolor, .regcolor * { color: #955C4A; }
        a { text-decoration: none; }
        a:hover { text-decoration: underline; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .mrg-top { margin-top: 7px; }
        .b { font-weight: bold; }
        .red, .red * { color: #d00000; }
        .redd, .redd * { color: #BA0000 !important; }
        .grnn, .grnn * { color: #114d01 !important; }

        /* Красная орнаментная рамка, как на проде */
        .common-block { position: relative; }
        .common-block .common-content { position: relative; z-index: 2; }
        .common-block .corner-tl { position: absolute; top: -19px; left: -23px; width: 141px; height: 176px; background: url({{ asset('main/images/common-block-red-tl.png') }}) 0 0 no-repeat; }
        .common-block .corner-tr { position: absolute; top: -19px; right: -24px; width: 146px; height: 176px; background: url({{ asset('main/images/common-block-red-tr.png') }}) 0 0 no-repeat; }
        .common-block .corner-bl { position: absolute; bottom: -19px; left: -19px; width: 238px; height: 127px; background: url({{ asset('main/images/common-block-red-bl.png') }}) 0 0 no-repeat; }
        .common-block .corner-br { position: absolute; bottom: -19px; right: -21px; width: 241px; height: 128px; background: url({{ asset('main/images/common-block-red-br.png') }}) 0 0 no-repeat; }
        .common-block .bg-inner { background: url({{ asset('main/images/bgg2.gif') }}) repeat; }
        .common-block .bg-inner-l { background: url({{ asset('main/images/common-block-inner-red-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-inner-r { background: url({{ asset('main/images/common-block-inner-red-r.png') }}) 100% 0 repeat-y; }
        .common-block .bg-inner-t { margin: 0 12px; background: url({{ asset('main/images/common-block-inner-t.png') }}) 0 0 repeat-x; }
        .common-block .bg-inner-b { padding: 20px 18px; background: url({{ asset('main/images/common-block-inner-b.png') }}) 0 100% repeat-x; }
        .common-block .bg-t { height: 41px; margin: 0 39px; text-align: center; background: url({{ asset('main/images/common-block-t.png') }}) 0 100% repeat-x; }
        .common-block .bg-b { height: 41px; margin: 0 39px; background: url({{ asset('main/images/common-block-b.png') }}) 0 0 repeat-x; }
        .common-block .bg-l { background: url({{ asset('main/images/common-block-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-r { padding: 0 39px; background: url({{ asset('main/images/common-block-r.png') }}) 100% 0 repeat-y; }

        .common-header__small, .common-header__small .h-inner, .common-header__small .h-txt { display: inline-block; }
        .common-header__small { position: relative; top: 11px; z-index: 1; height: 39px; padding: 0 0 0 87px; background: url({{ asset('main/images/common-header-small.png') }}) 0 0 no-repeat; }
        .common-header__small .h-inner { height: 39px; padding: 0 97px 0 10px; background: url({{ asset('main/images/common-header-small.png') }}) 100% -39px no-repeat; }
        .common-header__small .h-txt { padding: 10px 0 0; font-weight: bold; font-size: 11px; text-align: center; color: #faf7b9; }

        /* Малое окно с угловыми картинками, как на проде */
        .tbl-shp_sml-top { background-image: url({{ asset('main/images/tbl-shp_sml-top.gif') }}); background-repeat: repeat-x; height: 22px; }
        .tbl-shp_sml-bottom { background-image: url({{ asset('main/images/tbl-shp_sml-bottom.gif') }}); background-repeat: repeat-x; height: 18px; }
        .tbl-usi_left { background-image: url({{ asset('main/images/tbl-usi_left.gif') }}); background-repeat: repeat-y; background-position: right; width: 20px; }
        .tbl-usi_right { background-image: url({{ asset('main/images/tbl-usi_right.gif') }}); background-repeat: repeat-y; width: 20px; }
        .tbl-usi_bg { background-image: url({{ asset('main/images/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .tbl-usi_label-center {
            background-image: url({{ asset('main/images/tbl-usi_label-center.gif') }});
            background-repeat: repeat-x;
            height: 22px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding: 0 10px 3px;
        }

        /* Таблицы характеристик */
        .tbl-ati_brd-all {
            border: 1px solid #DB9F73;
            font-family: Tahoma;
            font-size: 11px;
            color: #201610;
            font-weight: normal;
            padding: 2px 0 3px 4px;
        }
        .tbl-ati_regular { font-family: Tahoma; font-size: 11px; color: #000000; padding-left: 5px; font-weight: normal; }
        .tbl-sts_bg-light { background-image: url({{ asset('main/images/tbl-usi_bg-light.gif') }}); background-repeat: repeat; font-family: Tahoma; font-size: 11px; }
        .tbl-usi_brd-bottom { border-bottom: 1px solid #DB9F73; }
        .tbl_red { font-size: 11px; color: #BA0000; font-family: Tahoma; text-decoration: none; }
        .tbl-shp_item-ico { margin-left: 0; margin-right: 6px; border: 0; }
    </style>
</head>
<body class="regcolor">

<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td align="center" valign="middle">
            <table width="610" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr>
                    <td>
                        <div class="common-block common-block__red">
                            <div class="corner-tl"></div>
                            <div class="corner-tr"></div>
                            <div class="corner-bl"></div>
                            <div class="corner-br"></div>

                            <div class="bg-t">
                                <div class="common-header__small">
                                    <div class="h-inner">
                                        <div class="h-txt">Информация о предмете</div>
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

                                                            {{-- ==== Окно 1: картинка + название + тип + цена ==== --}}
                                                            <table width="490" border="0" cellspacing="0" cellpadding="0" align="center">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt=""></td>
                                                                    <td class="tbl-shp_sml-top" valign="top" align="center">&nbsp;</td>
                                                                    <td width="20" align="left" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt=""></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top" align="center">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td rowspan="4" valign="top" align="center" width="60">
                                                                                    <table width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                                                                                           style="float: left; margin: 1px; {{ $page->image ? 'background: url('.$page->image.') no-repeat center;' : '' }}">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td title="{{ $page->name }}" valign="bottom">&nbsp;</td>
                                                                                        </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                                <td rowspan="5" width="12">&nbsp;</td>
                                                                                <td colspan="3">
                                                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl-ati_brd-all">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td width="70" class="tbl-sts_bg-light"><b>Название:</b></td>
                                                                                            <td class="tbl-sts_bg-light">
                                                                                                <h1 style="display: inline; font-size: 11px; margin: 0;"><b style="color:{{ $page->color }}">{{ $page->name }}</b></h1>
                                                                                            </td>
                                                                                        </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="tbl-ati_regular" title="Тип предмета"><img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" class="tbl-shp_item-ico" alt="">{{ $page->typeName }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="tbl-ati_regular b red" title="Цена" nowrap>
                                                                                    @if ($page->price > 0)
                                                                                        <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ $page->price }}
                                                                                    @else
                                                                                        &nbsp;
                                                                                    @endif
                                                                                </td>
                                                                                <td>&nbsp;</td>
                                                                                <td>&nbsp;</td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <img src="{{ asset('main/images/d.gif') }}" width="1" height="7" alt="" border="0"><br>
                                                                    </td>
                                                                    <td class="tbl-usi_right">&nbsp;</td>
                                                                </tr>
                                                                <tr height="18">
                                                                    <td width="20" align="right" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt=""></td>
                                                                    <td class="tbl-shp_sml-bottom" valign="top" align="center">&nbsp;</td>
                                                                    <td width="20" align="left" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt=""></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>

                                                            {{-- ==== Окно 2: характеристики предмета ==== --}}
                                                            <table width="490" border="0" cellspacing="0" cellpadding="0" class="mrg-top" align="center">
                                                                <tbody>
                                                                <tr height="22">
                                                                    <td width="20" align="right" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt=""></td>
                                                                    <td class="tbl-shp_sml-top" valign="top" align="center">
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                            <tbody>
                                                                            <tr height="22">
                                                                                <td><img src="{{ asset('data/img/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                                                                                <td class="tbl-usi_label-center">Характеристики предмета</td>
                                                                                <td><img src="{{ asset('data/img/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td width="20" align="left" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt=""></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="tbl-usi_left">&nbsp;</td>
                                                                    <td class="tbl-usi_bg" valign="top" align="center">
                                                                        <img src="{{ asset('main/images/d.gif') }}" width="1" height="7" alt="" border="0"><br>

                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl-ati_brd-all coll">
                                                                            <tbody>
                                                                            @php $light = true; @endphp

                                                                            @foreach ($page->stats as $stat)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom"><b>{{ $stat['title'] }}</b></td>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" align="right" style="padding-right: 3px"><b class="tbl_red">{{ $stat['value'] }}</b></td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                            @endforeach

                                                                            @if (count($page->requirements) > 0)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2"><b style="color:#8b4a00;">Требования:</b></td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                                @foreach ($page->requirements as $req)
                                                                                    @php $reqColor = $req['met'] ? '#8b4a00' : '#ff0000'; @endphp
                                                                                    <tr>
                                                                                        <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" style="color: {{ $reqColor }};">{{ $req['label'] }}</td>
                                                                                        <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" align="right" style="color: {{ $reqColor }}; font-weight: bold; padding-right: 3px;">{{ $req['value'] }}</td>
                                                                                    </tr>
                                                                                    @php $light = ! $light; @endphp
                                                                                @endforeach
                                                                            @endif

                                                                            @if (count($page->gems) > 0)
                                                                <tr>
                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2"><b style="color:#3300ff;">Камни:</b></td>
                                                                </tr>
                                                                @php $light = ! $light; @endphp
                                                                @foreach ($page->gems as $gem)
                                                                    @if (!empty($gem['header']))
                                                                        <tr>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2" style="color:{{ $gem['color'] }};">
                                                                                <b><a href="{{ $gem['url'] }}" onclick="window.open('{{ $gem['url'] }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;" style="color:{{ $gem['color'] }};">{{ $gem['title'] }}</a></b>
                                                                            </td>
                                                                        </tr>
                                                                    @else
                                                                        <tr>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" style="padding-left: 15px;"><b>{{ $gem['title'] }}</b></td>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" align="right" style="padding-right: 3px;"><b class="tbl_red">{{ $gem['value'] }}</b></td>
                                                                        </tr>
                                                                    @endif
                                                                    @php $light = ! $light; @endphp
                                                                @endforeach
                                                            @endif

                                                            @if (count($page->runes) > 0)
                                                                <tr>
                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2"><b style="color:#990099;">Руны:</b></td>
                                                                </tr>
                                                                @php $light = ! $light; @endphp
                                                                @foreach ($page->runes as $rune)
                                                                    @if (!empty($rune['header']))
                                                                        <tr>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2" style="color:{{ $rune['color'] }};">
                                                                                <b><a href="{{ $rune['url'] }}" onclick="window.open('{{ $rune['url'] }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;" style="color:{{ $rune['color'] }};">{{ $rune['title'] }}</a></b>
                                                                            </td>
                                                                        </tr>
                                                                    @else
                                                                        <tr>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" style="padding-left: 15px;"><b>{{ $rune['title'] }}</b></td>
                                                                            <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" align="right" style="padding-right: 3px;"><b class="tbl_red">{{ $rune['value'] }}</b></td>
                                                                        </tr>
                                                                    @endif
                                                                    @php $light = ! $light; @endphp
                                                                @endforeach
                                                            @endif

                                                            @if ($page->noGive)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom redd b" colspan="2">Предмет нельзя передать!</td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                            @endif

                                                                            @if ($page->noWeight)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom grnn b" colspan="2">Предмет не занимает места в рюкзаке</td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                            @endif

                                                                            @if ($page->description)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2">{{ $page->description }}</td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                            @endif

                                                                            @if ($page->gateLocations)
                                                                                <tr>
                                                                                    <td class="{{ $light ? 'tbl-sts_bg-light' : '' }} tbl-usi_brd-bottom" colspan="2">Открывает переход: {{ $page->gateLocations }}</td>
                                                                                </tr>
                                                                                @php $light = ! $light; @endphp
                                                                            @endif

                                                                            @if ($page->stats === [] && $page->requirements === [] && $page->gems === [] && $page->runes === [] && ! $page->noGive && ! $page->noWeight && ! $page->description && ! $page->gateLocations)
                                                                                <tr>
                                                                                    <td class="tbl-sts_bg-light" colspan="2">Обычный предмет без особых свойств.</td>
                                                                                </tr>
                                                                            @endif
                                                                            </tbody>
                                                                        </table>

                                                                        <img src="{{ asset('main/images/d.gif') }}" width="1" height="7" alt="" border="0"><br>
                                                                    </td>
                                                                    <td class="tbl-usi_right">&nbsp;</td>
                                                                </tr>
                                                                <tr height="18">
                                                                    <td width="20" align="right" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt=""></td>
                                                                    <td class="tbl-shp_sml-bottom" valign="top" align="center">&nbsp;</td>
                                                                    <td width="20" align="left" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt=""></td>
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