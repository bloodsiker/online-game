<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Игра</title>
    <style>
        *{
            font-size: 11px;
        }
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
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .hero-itm {
            display: block;
            padding: 0;
            width: 50px;
            height: 50px;
            border: 0px;
        }
        td.itm {
            padding: 0px 10px 0px 0px;
            white-space: nowrap;
        }
        img.itm {
            width: 50px;
            height: 50px;
            border: 0px;
            margin: 0px 10px 0px 0px;
            padding: 0px;
            vertical-align: middle;
            border-right: 1px solid #CEBBAA;
        }
        .border {
            background-color: #CEBBAA;
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
        .redd, .redd * {
            color: #BA0000 !important;
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
        .item-put-off {
            border: 0;
            font-size: 11px;
            position: absolute;
            padding: 0;
            top: 18px;
            left: 0;
            text-align: center;
            background: url({{ asset('img/bg/backpack/slot_button.png') }}) center center / 100% 100% no-repeat;
            width: 50px;
            height: 13px;
            line-height: 11px;
            text-decoration: none;
            display: none;
            font-weight: 600;
        }
        .item-put-off:hover {
            filter: brightness(1.08);
            text-decoration: none;
            color: #955C4A;
        }
        td.item-hero {
            position: relative;
        }
        td.item-hero:hover .item-put-off {
            display: block;
        }
        .equip-grid {
            border-collapse: separate;
            border-spacing: 2px;
        }
    </style>

    {!! $itemTooltipScript !!}
    <script>
        window.gebi = window.gebi || function (id) { return document.getElementById(id); };

        function hideEquippedItemTooltip() {
            try {
                showItemInfo({ dataset: {} }, event, 0);
            } catch (e) {
                const tooltip = window.top?.document?.getElementById('artifact_alt')
                    || document.getElementById('artifact_alt');
                if (tooltip) {
                    tooltip.style.display = 'none';
                }
            }
        }
    </script>
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body class="regcolor">
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>

<table cellspacing="0" cellpadding="10" width="100%" height="100%" id="item_list">
    <tbody>
    <tr valign="top">
        <td width="270px">
            <table class="equip-grid" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr>
                    <td class="item-hero"
                        @if($playerEquip->helmet) data-id="{{ $playerEquip->helmetSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->helmet)
                            <img src="{{ $playerEquip->helmetSlot->itemInfo->image }}" class="hero-itm" id="i2n1" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->helmetSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif

                    </td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i3n1"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i3n2"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i3n3"></td>
                    <td><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i10n1"></td>
                </tr>
                <tr>
                    <td class="item-hero"
                        @if($playerEquip->handLeft) data-id="{{ $playerEquip->handLeft->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->handLeft)
                            <img src="{{ $playerEquip->handLeft->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->handLeft->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td align="center" rowspan="4" colspan="3" bgcolor="#FAF0E4">
                        <img src="https://game.elders.com.ua/img/avatar/dark_elf.jpg" width="130" height="170" border="0" hspace="0" vspace="0">
                    </td>
                    <td class="item-hero"
                        @if($playerEquip->handRight) data-id="{{ $playerEquip->handRight->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->handRight)
                            <img src="{{ $playerEquip->handRight->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->handRight->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="item-hero"
                        @if($playerEquip->armor) data-id="{{ $playerEquip->armorSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->armor)
                            <img src="{{ $playerEquip->armorSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->armorSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                </tr>
                <tr>
                    <td class="item-hero"
                        @if($playerEquip->chain_armor) data-id="{{ $playerEquip->chainArmorSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->chain_armor)
                            <img src="{{ $playerEquip->chainArmorSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->chainArmorSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td class="item-hero"
                        @if($playerEquip->cloak) data-id="{{ $playerEquip->cloakSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->cloak)
                            <img src="{{ $playerEquip->cloakSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->cloakSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="item-hero"
                        @if($playerEquip->shoes) data-id="{{ $playerEquip->shoesSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->shoes)
                            <img src="{{ $playerEquip->shoesSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->shoesSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td class="item-hero"
                        @if($playerEquip->gloves) data-id="{{ $playerEquip->glovesSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->gloves)
                            <img src="{{ $playerEquip->glovesSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->glovesSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                    <td align="center"><img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm"></td>
                </tr>
                </tbody>
            </table>
            <table class="equip-grid" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr>
                    <td align="center" width="30"></td>

                    <td class="item-hero" align="center"
                        @if($playerEquip->belt_first) data-id="{{ $playerEquip->beltFirstSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->belt_first)
                            <img src="{{ $playerEquip->beltFirstSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->beltFirstSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm">
                        @endif
                    </td>

                    <td class="item-hero" align="center"
                        @if($playerEquip->belt_second) data-id="{{ $playerEquip->beltSecondSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->belt_second)
                            <img src="{{ $playerEquip->beltSecondSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->beltSecondSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm">
                        @endif
                    </td>

                    <td class="item-hero" align="center"
                        @if($playerEquip->bag_first) data-id="{{ $playerEquip->bagFirstSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->bag_first)
                            <img src="{{ $playerEquip->bagFirstSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->bagFirstSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm">
                        @endif
                     </td>

                    <td class="item-hero" align="center"
                        @if($playerEquip->bag_second) data-id="{{ $playerEquip->bagSecondSlot->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
                        @if($playerEquip->bag_second)
                            <img src="{{ $playerEquip->bagSecondSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->bagSecondSlot->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">снять</a>
                        @else
                            <img src="{{ asset('img/bg/empty_slot.gif') }}" class="hero-itm">
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<script>
    {{-- Страница вложена в обёртку раздела «Вещи»: top — игровое окно, parent — обёртка --}}
    @if (session()->has('message'))
        try { window.top.showErrorIframe('{{ session('message') }}') } catch(e) {}
    @endif
    @if (session()->has('hotbar_refresh'))
        try { window.top.refreshHotbar(); } catch(e) {}
    @endif
    @if (session()->has('equip_changed'))
        try { window.parent.reloadBag(); } catch(e) {}
    @endif

    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case ' ':
                window.top.sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    });
</script>

</body>
</html>
