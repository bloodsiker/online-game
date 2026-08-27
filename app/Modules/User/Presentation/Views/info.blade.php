<html>
<head>
    <title>{{ $user->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        .b { font-weight: 700; }
        .bg2 { background-color: #000; background-image: url({{ asset('img/bg/bg2.gif') }}); }
        .regcolor, .regcolor * { color: #955c4a; }
        .common-block { position: relative; }
        .common-block .corner-tl { position: absolute; top: -19px; left: -23px; width: 141px; height: 176px; background: url({{ asset('img/bg/info/common-block-tl.png') }}) 0 0 no-repeat; }
        .common-block .corner-tr { position: absolute; top: -19px; right: -24px; width: 146px; height: 176px; background: url({{ asset('img/bg/info/common-block-tr.png') }}) 0 0 no-repeat; }
        .common-block .corner-bl { position: absolute; bottom: -19px; left: -19px; width: 238px; height: 127px; background: url({{ asset('img/bg/info/common-block-bl.png') }}) 0 0 no-repeat; }
        .common-block .corner-br { position: absolute; bottom: -19px; right: -21px; width: 241px; height: 128px; background: url({{ asset('img/bg/info/common-block-br.png') }}) 0 0 no-repeat; }
        .common-block .bg-t { height: 41px; margin: 0 39px; text-align: center; background: url({{ asset('img/bg/info/common-block-t.png') }}) 0 100% repeat-x; }
        .common-block .bg-l { background: url({{ asset('img/bg/info/common-block-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-b { height: 41px; margin: 0 39px; background: url({{ asset('img/bg/info/common-block-b.png') }}) 0 0 repeat-x; }
        .common-block .bg-r { padding: 0 39px; background: url({{ asset('img/bg/info/common-block-r.png') }}) 100% 0 repeat-y; }
        .common-header { position: relative; top: 7px; z-index: 1; height: 38px; padding: 0 0 0 192px; background: url({{ asset('img/bg/info/common-header.png') }}) 0 0 no-repeat; }
        .common-header .h-inner { height: 38px; padding: 0 192px 0 0; background: url({{ asset('img/bg/info/common-header.png') }}) 100% -38px no-repeat; }
        .common-header .h-txt, .common-header a, .common-header b { color: #faf7b9; }
        .common-header .h-txt { padding: 10px 0 0; font-weight: 700; font-size: 12px; text-align: center; }
        .common-header, .common-header .h-inner, .common-header .h-txt { display: inline-block; }
        .common-block .bg-inner { background: url({{ asset('img/bg/bgg2.gif') }}) repeat; }
        .common-block .bg-inner-l { background: url({{ asset('img/bg/info/common-block-inner-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-inner-r { background: url({{ asset('img/bg/info/common-block-inner-r.png') }}) 100% 0 repeat-y; }
        .common-block .bg-inner-t { margin: 0 12px; background: url({{ asset('img/bg/info/common-block-inner-t.png') }}) 0 0 repeat-x; zoom: 1; }
        .common-block .bg-inner-b { padding: 20px 18px; background: url({{ asset('img/bg/info/common-block-inner-b.png') }}) 0 100% repeat-x; zoom: 1; }
        .common-block .common-content { position: relative; z-index: 2; }
        .mrg-top { margin-top: 7px; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .tbl-usi-hdr { background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        a.tbl-shp_menu-center-inact, a.tbl-shp_menu-center-inact span,
        a.tbl-shp_menu-center-act, a.tbl-shp_menu-center-act span { display: inline-block; height: 19px; margin: 0 0 0 -3px; padding: 0 0 0 16px; text-decoration: none; cursor: pointer; font-size: 11px; }
        a.tbl-shp_menu-center-inact:hover, a.tbl-shp_menu-center-act:hover { text-decoration: underline; }
        a.tbl-shp_menu-center-act, a.tbl-shp_menu-center-act span { color: #ffe9ba; background: url({{ asset('img/bg/btn/tbl-shp_menu-act_2.gif') }}) 0 0 no-repeat; }
        a.tbl-shp_menu-center-inact, a.tbl-shp_menu-center-inact span { color: #461c0b; background: url({{ asset('img/bg/btn/tbl-shp_menu-inact_2.gif') }}) 0 0 no-repeat; }
        a.tbl-shp_menu-center-inact span, a.tbl-shp_menu-center-act span { padding: 0 20px 0 8px; font-weight: bold; line-height: 16px; background-position: 100% -19px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .brd2-bt { border-bottom: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .bg_l { background-image: url({{ asset('img/bg/info/bg_l.gif') }}); }
        .redd, .redd * { color: #ba0000 !important; }
        .online-status { color: #4C9A50 !important; font-weight: 700; }
        a { color: #955c4a; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .hero-itm { display: block; padding: 0; width: 50px; height: 50px; border: 0; }
        .hero-itm.equipped { background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); cursor: pointer; }
        td.item-hero { position: relative; line-height: 0; }
        .equip-grid { border-collapse: separate; border-spacing: 2px; }
        .medal_bg td { text-align: center; vertical-align: middle; background-repeat: no-repeat; height: 53px; }
        .medal_bg_c { background: url({{ asset('img/medal/medal_c.gif') }}) top center; width: 45px; }
        .medal_bg_c img { width: 35px; height: 35px; border: 0; vertical-align: middle; }
        .medal_bg img { cursor: pointer; }
        #artifact_alt .aa-table {
            border-radius: 30px 30px 0 0;
            box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, 0.2);
            font-size: 11px;
        }
        .aa-tl {
            background: url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat;
            width: 14px;
            height: 24px;
        }
        .aa-t {
            background: url(/img/bg/item_info/tbl-pop_top.gif);
            height: 24px;
        }
        .aa-tr {
            background: url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat;
            width: 14px;
            height: 24px;
        }
        .aa-l {
            background: url(/img/bg/item_info/tbl-pop_left.gif) repeat-y;
            width: 14px;
        }
        .aa-r {
            background: url(/img/bg/item_info/tbl-pop_right.gif) repeat-y;
            width: 14px;
        }
        .aa-bl {
            background: url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat;
            width: 14px;
            height: 5px;
        }
        .aa-b {
            background: url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x;
            height: 5px;
        }
        .aa-br {
            background: url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat;
            width: 14px;
            height: 5px;
        }
        .list_dark { background-color: #F4BB8A; }
        .skill_list td { padding: 0 7px; }
        .red, .red * { color: #d00000; }
    </style>
    {!! $itemTooltipScript !!}
    <script>
        window.gebi = window.gebi || function (id) { return document.getElementById(id); };
    </script>
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body class="bg2 regcolor" topmargin="0" leftmargin="0">
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0;top: 0"></div>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td align="center" valign="middle">
            <table height="10%" width="860" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr><td height="25">&nbsp;</td></tr>
                <tr>
                    <td>
                        <div class="common-block">
                            <div class="corner-tl"></div>
                            <div class="corner-tr"></div>
                            <div class="corner-bl"></div>
                            <div class="corner-br"></div>
                            <div class="bg-t">
                                <div class="common-header">
                                    <div class="h-inner">
                                        <div class="h-txt">{{ $user->name }} [{{ $user->player->lvl }}]</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-l"><div class="bg-r"><div class="bg-inner"><div class="bg-inner-l"><div class="bg-inner-r"><div class="bg-inner-t"><div class="bg-inner-b">
                            <div class="common-content">

                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        {{-- Левая колонка: медали + кукла с экипировкой --}}
                                        <td valign="top" width="320">

                                            {{-- Слайдер медалей за репутацию (заглушка) --}}
                                            <table border="0" cellpadding="0" cellspacing="0" align="center" class="medal_bg">
                                                <tbody>
                                                <tr>
                                                    <td><img id="medal_l" src="{{ asset('img/medal/medal_l.gif') }}" border="0" width="25" height="52" onclick="moveMedals(-1);"></td>
                                                    <td class="medal_bg_c" id="medal_0"></td>
                                                    <td class="medal_bg_c" id="medal_1"></td>
                                                    <td class="medal_bg_c" id="medal_2"></td>
                                                    <td class="medal_bg_c" id="medal_3"></td>
                                                    <td class="medal_bg_c" id="medal_4"></td>
                                                    <td class="medal_bg_c" id="medal_5"></td>
                                                    <td><img id="medal_r" src="{{ asset('img/medal/medal_r.gif') }}" border="0" width="25" height="52" onclick="moveMedals(1);"></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <div style="height:6px;font-size:1px;">&nbsp;</div>

                                            @php
                                                $equip = $user->player->playerEquip;
                                                $emptySlotImage = asset('img/bg/empty_slot.gif');
                                                $slotAttrs = static fn ($item): string => $item === null
                                                    ? 'class="item-hero"'
                                                    : 'class="item-hero" data-id="'.(int) $item->id.'" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" onclick="window.open(\''.route('items.info', ['id' => $item->id]).'\', \'\', \'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;"';
                                                $slotImage = static fn ($item): string => $item === null
                                                    ? $emptySlotImage
                                                    : $item->itemInfo->image;
                                            @endphp
                                            <table class="equip-grid" cellspacing="0" cellpadding="0" border="0" align="center">
                                                <tbody>
                                                <tr>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td {!! $slotAttrs($equip?->helmetSlot) !!} align="center">
                                                        <img src="{{ $slotImage($equip?->helmetSlot) }}" class="hero-itm @if($equip?->helmetSlot) equipped @endif" id="i2n1">
                                                    </td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                </tr>
                                                <tr>
                                                    <td {!! $slotAttrs($equip?->shoulderSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->shoulderSlot) }}" class="hero-itm @if($equip?->shoulderSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                    <td align="center" rowspan="4" colspan="3" bgcolor="#FAF0E4">
                                                        <img src="{{ asset('img/avatar/dark_elf.jpg') }}" width="130" height="170" border="0" hspace="0" vspace="0">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->forearmSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->forearmSlot) }}" class="hero-itm @if($equip?->forearmSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td {!! $slotAttrs($equip?->handLeft) !!}>
                                                        <img src="{{ $slotImage($equip?->handLeft) }}" class="hero-itm @if($equip?->handLeft) equipped @endif" id="i4n1">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->handRight) !!}>
                                                        <img src="{{ $slotImage($equip?->handRight) }}" class="hero-itm @if($equip?->handRight) equipped @endif" id="i4n1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td {!! $slotAttrs($equip?->armorSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->armorSlot) }}" class="hero-itm @if($equip?->armorSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->leggingSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->leggingSlot) }}" class="hero-itm @if($equip?->leggingSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td {!! $slotAttrs($equip?->chainArmorSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->chainArmorSlot) }}" class="hero-itm @if($equip?->chainArmorSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->shoesSlot) !!}>
                                                        <img src="{{ $slotImage($equip?->shoesSlot) }}" class="hero-itm @if($equip?->shoesSlot) equipped @endif" id="i4n1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    {{-- Резерв под будущие кольца --}}
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                    <td class="item-hero" align="center"><img src="{{ $emptySlotImage }}" class="hero-itm"></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <table class="equip-grid" cellspacing="0" cellpadding="0" border="0" align="center">
                                                <tbody>
                                                <tr>
                                                    <td align="center" width="30"></td>
                                                    <td {!! $slotAttrs($equip?->beltFirstSlot) !!} align="center">
                                                        <img src="{{ $slotImage($equip?->beltFirstSlot) }}" class="hero-itm @if($equip?->beltFirstSlot) equipped @endif">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->beltSecondSlot) !!} align="center">
                                                        <img src="{{ $slotImage($equip?->beltSecondSlot) }}" class="hero-itm @if($equip?->beltSecondSlot) equipped @endif">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->bagFirstSlot) !!} align="center">
                                                        <img src="{{ $slotImage($equip?->bagFirstSlot) }}" class="hero-itm @if($equip?->bagFirstSlot) equipped @endif">
                                                    </td>
                                                    <td {!! $slotAttrs($equip?->bagSecondSlot) !!} align="center">
                                                        <img src="{{ $slotImage($equip?->bagSecondSlot) }}" class="hero-itm @if($equip?->bagSecondSlot) equipped @endif">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            @if($locationPath !== '')
                                            <div style="height:10px;font-size:1px;">&nbsp;</div>

                                            {{-- Текущая локация персонажа --}}
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr height="22">
                                                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                                    <td class="tbl-shp-sml tt" valign="top" align="center">
                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                            <tr height="22">
                                                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                <td align="center" class="tbl-usi-hdr mbg">Находится</td>
                                                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                                </tr>
                                                <tr>
                                                    <td class="tbl-shp-sides ls">&nbsp;</td>
                                                    <td class="tbl-usi_bg" valign="top" align="center" style="padding:6px 4px;">
                                                        <table class="coll w100 p10h p2v brd2-all">
                                                            <tbody>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b redd" align="center">{{ $locationPath }}</td>
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
                                            @endif
                                        </td>

                                        <td width="16">&nbsp;</td>

                                        {{-- Правая колонка: игровая информация --}}
                                        <td valign="top">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr height="22">
                                                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                                    <td class="tbl-shp-sml tt" valign="top" align="center">
                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                            <tr height="22">
                                                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                <td align="center" class="tbl-usi-hdr mbg">Игровая информация</td>
                                                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                                </tr>

                                                <tr>
                                                    <td class="tbl-shp-sides ls">&nbsp;</td>
                                                    <td class="tbl-usi_bg" valign="top" align="center" style="padding:6px 4px;">
                                                        <table class="coll w100 p10h p2v brd2-all" border="0">
                                                            <tbody>
                                                            @if($user->player->race)
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Раса</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $user->player->race->name }}</td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Класс персонажа</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $stats->getDisplayCombatClassLabel() }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Побед</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $user->player->victory }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Поражений</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $user->player->death }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Онлайн:</td>
                                                                <td class="brd2-top brd2-bt b" align="right">
                                                                    @if($isOnline)
                                                                        <span class="online-status">В игре</span>
                                                                    @else
                                                                        {{ $user->last_online_at ? \Carbon\Carbon::parse($user->last_online_at)->format('d.m.Y H:i') : '—' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        <div style="height:10px;font-size:1px;">&nbsp;</div>

                                                        <table class="coll w100 p10h p2v brd2-all">
                                                            <tbody>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Сила</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ (int) $user->player->strength }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Ловкость</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ (int) $user->player->agility }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Интуиция</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ (int) $user->player->intuition }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Выносливость</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $stats->getEndurance() }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Мудрость</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ (int) $user->player->wisdom }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Интеллект</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ (int) $user->player->intelligence }}</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        <div style="height:10px;font-size:1px;">&nbsp;</div>

                                                        <table class="coll w100 p10h p2v brd2-all">
                                                            <tbody>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Здоровье</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $user->player->hp_now }} / {{ $stats->getHpMax() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Мана</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $user->player->mp_now }} / {{ $stats->getMpMax() }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Броня</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $stats->getArmor() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="brd2-top brd2-bt b">Критический удар</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $stats->getCritical() }}</td>
                                                            </tr>
                                                            <tr class="bg_l">
                                                                <td class="brd2-top brd2-bt b">Уворот</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $stats->getDodge() }}</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        @if($user->clanMembership)
                                                            <div style="height:10px;font-size:1px;">&nbsp;</div>
                                                            <table class="coll w100 p10h p2v brd2-all">
                                                                <tbody>
                                                                <tr class="bg_l">
                                                                    <td class="brd2-top brd2-bt b">Клан</td>
                                                                    <td class="brd2-top brd2-bt b redd" align="right">
                                                                        <a href="{{ route('clan.public', ['clan' => $user->clanMembership->clan->id]) }}" title="Открыть информацию о клане" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $user->clanMembership->clan->name }}@if($user->clanMembership->clan->icon)&nbsp;<img src="{{ Storage::disk('public')->url($user->clanMembership->clan->icon) }}" alt="" border="0" width="13" height="13" align="absmiddle">@endif</a>
                                                                    </td>
                                                                </tr>
                                                                @if($user->clanMembership->role)
                                                                <tr>
                                                                    <td class="brd2-top brd2-bt b">Звание</td>
                                                                    <td class="brd2-top brd2-bt b redd" align="right">{{ $user->clanMembership->role->name }}</td>
                                                                </tr>
                                                                @endif
                                                                </tbody>
                                                            </table>
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

                                            @if($user->player->skills->isNotEmpty())
                                            <div style="height:10px;font-size:1px;">&nbsp;</div>

                                            {{-- Навыки/Умения --}}
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr height="22">
                                                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                                    <td class="tbl-shp-sml tt" valign="top" align="center">
                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                            <tr height="22">
                                                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                <td align="center" class="tbl-usi-hdr mbg">Навыки/Умения</td>
                                                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                                </tr>
                                                <tr>
                                                    <td class="tbl-shp-sides ls">&nbsp;</td>
                                                    <td class="tbl-usi_bg" valign="top" align="center" style="padding:6px 4px;">
                                                        <table class="coll w100 p10h p2v brd2-all">
                                                            <tbody>
                                                            @foreach($user->player->skills as $i => $playerSkill)
                                                            <tr class="{{ $i % 2 === 0 ? 'bg_l' : '' }}">
                                                                <td class="brd2-top brd2-bt b">{{ $playerSkill->skill->name }}</td>
                                                                <td class="brd2-top brd2-bt b redd" align="right">{{ $playerSkill->lvl }}</td>
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
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div style="height:10px;font-size:1px;">&nbsp;</div>

                                {{-- Артефакты: одетые артефакты (заглушка) --}}
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr height="22">
                                        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                        <td class="tbl-shp-sml tt" valign="top" align="center">
                                            <table border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr height="22">
                                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                    <td align="center" class="tbl-usi-hdr mbg">Артефакты</td>
                                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-shp-sides ls">&nbsp;</td>
                                        <td class="tbl-usi_bg" valign="top" align="left" style="padding:3px 0 8px 0;">
                                            @foreach([
                                                'mob_dreven1_100.gif', 'mob_dugr_250.gif', 'mob_eldiv1_10.gif', 'mob_eldiv1_500.gif',
                                                'mob_eldiv2_500.gif', 'mob_eldiv3_100.gif', 'mob_eldiv3_250.gif', 'mob_eldiv3_50.gif',
                                                'mob_eldiv4_100.gif', 'mob_eldiv4_500.gif',
                                            ] as $achievement)
                                                <a href="#" onclick="return false;"><img src="{{ asset('img/achievements/'.$achievement) }}" width="55" height="55" border="0" style="margin:1px;"></a>
                                            @endforeach
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

                                <div style="height:10px;font-size:1px;">&nbsp;</div>

                                {{-- Личная информация --}}
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr height="22">
                                        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                        <td class="tbl-shp-sml tt" valign="top" align="center">
                                            <table border="0" cellspacing="0" cellpadding="0">
                                                <tbody>
                                                <tr height="22">
                                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                    <td align="center" class="tbl-usi-hdr mbg">Личная информация</td>
                                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                    </tr>
                                    <tr id="personal_info">
                                        <td class="tbl-shp-sides ls">&nbsp;</td>
                                        <td class="tbl-usi_bg" valign="top" align="center" style="padding:6px 4px;">
                                            <table class="coll w100 p10h p2v brd2-all">
                                                <colgroup>
                                                    <col width="1%">
                                                    <col>
                                                </colgroup>
                                                <tbody>
                                                <tr class="bg_l">
                                                    <td class="brd2-top brd2-bt b" nowrap>Имя:</td>
                                                    <td class="brd2-top brd2-bt b redd">{{ $user->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="brd2-top brd2-bt b" nowrap>В игре</td>
                                                    <td class="brd2-top brd2-bt b redd">{{ $age }}</td>
                                                </tr>
                                                <tr class="bg_l">
                                                    <td class="brd2-top brd2-bt b" nowrap>Инфо:</td>
                                                    <td class="brd2-top brd2-bt b redd">&nbsp;</td>
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

                            </div>
                            </div></div></div></div></div></div></div>
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

<script>
    {{-- Слайдер медалей за репутацию (заглушка со статическими картинками) --}}
    var MedalsOnPage = 6;
    var position = 0;
    var medalImages = [
        '{{ asset('img/medal/medal_drakon_red.gif') }}',
        '{{ asset('img/medal/medal_eldiv_red.gif') }}',
        '{{ asset('img/medal/medal_krof_red.gif') }}',
        '{{ asset('img/medal/medal_prirody_red.gif') }}',
        '{{ asset('img/medal/medalnoch_red.gif') }}',
        '{{ asset('img/medal/medal_druid3.jpg') }}',
        '{{ asset('img/medal/medal_druidf2.jpg') }}',
        '{{ asset('img/medal/medal_druid4.jpg') }}',
    ];
    var medals = medalImages.map(function (src) {
        return '<img src="' + src + '" width="35" height="35" border="0">';
    });
    {{-- Первый слот пустой — виден только фон medal_c.gif --}}
    medals.unshift('&nbsp;');

    function showMedals() {
        for (var i = 0; i < MedalsOnPage; i++) {
            document.getElementById('medal_' + i).innerHTML = medals[i + position] ? medals[i + position] : '&nbsp;';
        }
        document.getElementById('medal_l').src = position > 0
            ? '{{ asset('img/medal/medal_l_act.gif') }}'
            : '{{ asset('img/medal/medal_l.gif') }}';
        document.getElementById('medal_r').src = medals[position + MedalsOnPage]
            ? '{{ asset('img/medal/medal_r_act.gif') }}'
            : '{{ asset('img/medal/medal_r.gif') }}';
    }

    function moveMedals(shift) {
        var next = position + shift;
        if (next < 0 || next + MedalsOnPage > medals.length) return;
        position = next;
        showMedals();
    }

    showMedals();
</script>
</body>
</html>
