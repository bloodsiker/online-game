<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .hero-itm {
            padding: 1px;
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
        .b {
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
            border: 1px solid #f59530;
            font-size: 12px;
            position: absolute;
            padding-bottom: 1px;
            top: 18px;
            left: 0;
            text-align: center;
            background: #f7d675;
            width: 50px;
            text-decoration: none;
            display: none;
        }
        .item-put-off:hover {
            background: #fbbf7f;
        }
        td.item-hero {
            position: relative
        }
        td.item-hero:hover .item-put-off {
            display: block;
        }
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td width="270px">
            <table cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr>
                    <td class="item-hero">
                        @if($playerEquip->helmet)
                            <img src="{{ $playerEquip->helmetSlot->itemInfo->image }}" class="hero-itm" id="i2n1" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->helmetSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif

                    </td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i3n1"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i3n2"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i3n3"></td>
                    <td><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i10n1"></td>
                </tr>
                <tr>
                    <td class="item-hero">
                        @if($playerEquip->handLeft)
                            <img src="{{ $playerEquip->handLeft->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->handLeft->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td align="center" rowspan="4" colspan="3" bgcolor="#FAF0E4">
                        <img src="https://skazanie.com/portrait/mm4.jpg" width="130" height="170" border="0" hspace="0" vspace="0">
                    </td>
                    <td class="item-hero">
                        @if($playerEquip->handRight)
                            <img src="{{ $playerEquip->handRight->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->handRight->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="item-hero">
                        @if($playerEquip->armor)
                            <img src="{{ $playerEquip->armorSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->armorSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                </tr>
                <tr>
                    <td class="item-hero">
                        @if($playerEquip->chain_armor)
                            <img src="{{ $playerEquip->chainArmorSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->chainArmorSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td class="item-hero">
                        @if($playerEquip->cloak)
                            <img src="{{ $playerEquip->cloakSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->cloakSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="item-hero">
                        @if($playerEquip->shoes)
                            <img src="{{ $playerEquip->shoesSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->shoesSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                    <td class="item-hero">
                        @if($playerEquip->gloves)
                            <img src="{{ $playerEquip->glovesSlot->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
                            <a href="{{ route('items.put_off', ['id' => $playerEquip->glovesSlot->id]) }}" class="item-put-off">снять</a>
                        @else
                            <img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm" id="i4n1">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                </tr>
                <tr>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                    <td align="center"><img src="https://skazanie.com/img-item/empty-slot.jpg" class="hero-itm"></td>
                </tr>
                </tbody>
            </table>
        </td>

        <td>

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">

                        <table border="0" cellspacing="0" cellpadding="0" style="position: relative; top: -2px;">
                            <tbody>
                            @php
                                $btnLeft1 = 'img/bg/btn/btn-left1.gif';
                                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                                $btnRight1 = 'img/bg/btn/btn-right1.gif';

                                $btnLeft2 = 'img/bg/btn/btn-left2.gif';
                                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                                $btnRight2 = 'img/bg/btn/btn-right2.gif';
                            @endphp
                            <tr height="21">
                                <td width="19"><img id="left_1" src="{{ asset($group === 'main' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_1" align="center" style="background: url({{ asset($group === 'main' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_1" href="{{ route('backpack', ['group' => 'main']) }}" title="Доспехи, инструменты, амулеты и прочие полезные вещи" class="{{ $group === 'main' ? 'btn_2' : 'btn_1' }}">Вещи</a>
                                </td>
                                <td width="19"><img id="right_1" src="{{ asset($group === 'main' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'key' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'key' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('backpack', ['group' => 'key']) }}" title="Квестовые предметы" class="{{ $group === 'key' ? 'btn_2' : 'btn_1' }}">Ключи</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'key' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'quest' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'quest' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('backpack', ['group' => 'quest']) }}" title="Квестовые предметы" class="{{ $group === 'quest' ? 'btn_2' : 'btn_1' }}">Квесты</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'quest' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_3" src="{{ asset($group === 'artifact' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_3" align="center" style="background: url({{ asset($group === 'artifact' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_3" href="{{ route('backpack', ['group' => 'artifact']) }}" title="Артефакты" class="{{ $group === 'artifact' ? 'btn_2' : 'btn_1' }}">Артефакты</a></td>
                                <td width="19"><img id="right_3" src="{{ asset($group === 'artifact' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_4" src="{{ asset($group === 'gift' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_4" align="center" style="background: url({{ asset($group === 'gift' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_4" href="{{ route('backpack', ['group' => 'gift']) }}" title="Подаренные Вам подарки" class="{{ $group === 'gift' ? 'btn_2' : 'btn_1' }}">Подарки</a></td>
                                <td width="19"><img id="right_4" src="{{ asset($group === 'gift' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">


                        <table cellspacing="1" cellpadding="1" border="0" class="b" width="100%">
                            <tbody>
                            <tr>
                                <td class="tbgr">
                                    <table cellspacing="1" cellpadding="5" border="0" width="100%" class="b">
                                        <tbody>
                                        <tr class="t1">
                                            <td><b>Предмет</b></td>
                                            <td align="center"><b>К-во (шт)</b></td>
                                            <td></td>
                                        </tr>
                                        @if($group === 'main')
                                            @if(isset($items['weapon']) || isset($items['shield']))
                                                <tr class="t0">
                                                    <td colspan="3" align="center"><b>Оружие:</b></td>
                                                </tr>
                                                @if(isset($items['weapon']))
                                                    @foreach($items['weapon'] as $item)
                                                        <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                            <td class="itm">
                                                                <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                    <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                                </a>
                                                            </td>
                                                            <td align="center" width="90">x{{ $item->count }}</td>
                                                            <td>
                                                                [<a href="{{ route('items.put_on', ['id' => $item->item->id, 'group' => $group]) }}" target="game">Надеть</a>]
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif

                                                @if(isset($items['shield']))
                                                    @foreach($items['shield'] as $item)
                                                        <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                            <td class="itm">
                                                                <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                    <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                                </a>
                                                            </td>
                                                            <td align="center" width="90">x{{ $item->count }}</td>
                                                            <td>
                                                                [<a href="{{ route('items.put_on', ['id' => $item->item->id, 'group' => $group]) }}" target="game">Надеть</a>]
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endif


                                            @if(isset($items['armor']))
                                                <tr class="t0">
                                                    <td colspan="3" align="center"><b>Доспехи:</b></td>
                                                </tr>
                                                @foreach($items['armor'] as $item)
                                                    <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                        <td class="itm">
                                                            <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                            </a>
                                                        </td>
                                                        <td align="center" width="90">x{{ $item->count }}</td>
                                                        <td>
                                                            [<a href="{{ route('items.put_on', ['id' => $item->item->id, 'group' => $group]) }}" target="game">Надеть</a>]
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            @if(isset($items['heal']))
                                                <tr class="t0">
                                                    <td colspan="3" align="center"><b>Восстанавливающие:</b>
                                                    </td>
                                                </tr>
                                                @foreach($items['heal'] as $item)
                                                    <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                        <td class="itm">
                                                            <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                            </a>
                                                        </td>
                                                        <td align="center" width="90">x{{ $item->count }}</td>
                                                        <td>
                                                            [<a href="use.php?iid=115041872" target="game">использовать</a>]
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            @if(isset($items['resource']))
                                                <tr class="t0">
                                                    <td colspan="3" align="center"><b>Ресурсы:</b>
                                                    </td>
                                                </tr>
                                                @foreach($items['resource'] as $item)
                                                    <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                        <td class="itm">
                                                            <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                            </a>
                                                        </td>
                                                        <td align="center" width="90">x{{ $item->count }}</td>
                                                        <td>
                                                            [<a href="use.php?iid=115041872" target="game">использовать</a>]
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            @if(isset($items['recipe']))
                                                <tr class="t0">
                                                    <td colspan="3" align="center"><b>Рецепты:</b>
                                                    </td>
                                                </tr>
                                                @foreach($items['recipe'] as $item)
                                                    <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                        <td class="itm">
                                                            <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                                <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                            </a>
                                                        </td>
                                                        <td align="center" width="90">x{{ $item->count }}</td>
                                                        <td>
                                                            [<a href="use.php?iid=115041872" target="game">использовать</a>]
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif

                                        @if($group === 'key' || $group === 'quest' || $group === 'artifact' || $group === 'gift')
                                            @foreach($backpack as $item)
                                                <tr class="{{ $loop->index % 2 == 0 ? 'l0' : 'l1' }}">
                                                    <td class="itm">
                                                        <a href="{{ route('items.info', ['id' => $item->item->id, 'group' => $group]) }}" target="game">
                                                            <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                                        </a>
                                                    </td>
                                                    <td align="center" width="90">x{{ $item->count }}</td>
                                                    <td>
                                                        [<a href="use.php?iid=115041872" target="game">использовать</a>]
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        <tr class="t0">
                                            <td colspan="1"><b>Всего: {{ $countItems }}</b></td>
                                            <td align="right">
                                                <table width="100%" cellpadding="0" cellspasing="0" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td height="10" align="left" background="{{ asset('img/bg/line-bgr.gif') }}">
                                                            <img src="{{ asset('img/bg/line-exp.gif') }}" width="93%" height="10" vspace="0" hspace="0" border="0">
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td><b>110.95</b></td>
                                        </tr>
                                        <tr class="t1">
                                            <td colspan="3">
                                                <small style="float:right;">
                                                    <a href="/help/lib/" target="_blank" class="in5but">Справочник: Одежда и оружие</a>
                                                </small>
                                                <b>Максимальный вес: 120</b>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
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


            <p>
                <a href="#">История передачи предметов</a> »
            </p>
            <p>
                Примечание:
                <br>
                Некоторые вещи использовать из вещевого мешка нельзя. Перед использованием их надо взять в руки либо
                надеть.
            </p></td>
    </tr>
    </tbody>
</table>

<script>
    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif

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
</script>

</body>
</html>
