<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предметы на локации</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-size: 11px; }
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; }
        a { color: #000000; }
        a:hover { color: #353434; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .t1 { background: linear-gradient(to bottom, #f2c398, #fff0c1); }
        .l0 { background-color: #FFF8EA; }
        .l1 { background-color: #FFFBF5; }
        .border { background-color: #CEBBAA; }
        img.itm { width: 50px; height: 50px; border: 0; vertical-align: middle; border-right: 1px solid #CEBBAA; margin-right: 8px; }
        .item-name { font-weight: bold; color: #3a1c0a; }
        .item-count { color: #7a5030; font-weight: bold; white-space: nowrap; }
        .empty-msg { padding: 12px 10px; color: #888; font-style: italic; }
        .footer-row td { padding: 4px 8px; }
        .msg { padding: 5px 10px; background: #fff8ea; border-bottom: 1px solid #CEBBAA; color: #461c0b; }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody><tr valign="top"><td>
        <table border="0" cellspacing="0" cellpadding="0"><tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="left"></td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 4px 0;">
                <table border="0" cellspacing="1" cellpadding="0" class="border" style="white-space: nowrap;"><tbody>
                <tr class="t1">
                    <td colspan="3" style="padding: 4px 10px;">
                        <b style="color:#461c0b;">{{ $page->count ? "Предметы на локации ({$page->count})" : 'Предметы на локации' }}</b>
                    </td>
                </tr>
                @if($page->message !== '')
                    <tr><td colspan="3" class="msg">{!! $page->message !!}</td></tr>
                @endif
                @forelse($page->items as $i => $item)
                    <tr class="{{ $i % 2 === 0 ? 'l0' : 'l1' }}">
                        <td style="white-space: nowrap;"><img src="{{ $item->image }}" class="itm"><span class="item-name">{{ $item->name }}</span></td>
                        <td style="padding: 3px 8px;" align="center"><span class="item-count">x{{ $item->count }}</span></td>
                        <td style="padding: 3px 8px;" align="right"><b class="butt2 pointer"><b><input value="{{ $item->actionLabel }}" type="button" onclick="location.href='{{ $item->actionUrl }}'"></b></b></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-msg">Больше ничего нет...</td></tr>
                @endforelse
                <tr class="footer-row t1">
                    <td colspan="3">
                        <b class="butt2 pointer"><b><input value="« Описание местности" type="button" onclick="window.parent.sendDataToGame('{{ $page->locationUrl }}')"></b></b>
                        &nbsp;
                        <b class="butt2 pointer"><b><input value="Рюкзак" type="button" onclick="window.parent.sendDataToGame('{{ $page->backpackUrl }}')"></b></b>
                    </td>
                </tr>
                </tbody></table>
            </td>
            <td class="tbl-shp-sides rs">&nbsp;</td>
        </tr>
        <tr height="18">
            <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
            <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
            <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
        </tr>
        </tbody></table>
    </td></tr></tbody>
</table>
<script>
</script>
</body>
</html>
