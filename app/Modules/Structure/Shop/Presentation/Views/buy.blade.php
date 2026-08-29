<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * {
            font-family: Tahoma, Geneva, sans-serif;
            font-size: 11px;
        }
        .b {
            font-weight: 700;
        }
        a {
            text-decoration: none;
        }

        .bg {
            background-color: #000;
            background-image: url({{ asset('img/bg/bg.gif') }});
            background-attachment: fixed;
            background-position: 0 5px;
        }
        .tbl-sts b {
            background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-lb b {
            background-position: 0 -170px;
        }
        .tbl-sts-rb b {
            background-position: 0 -219px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-lbt b {
            background-position: 0 -150px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .tbl-sts-rbt b {
            background-position: 0 -200px;
            height: 20px;
        }
        .tbl-sts_left {
            background-image: url({{ asset('img/bg/tbl-sts_left.gif') }});
            background-repeat: repeat-y;
            width: 19px;
            background-position: right;
        }
        .tbl-sts_right {
            background-image: url({{ asset('img/bg/tbl-sts_right.gif') }});
            background-repeat: repeat-y;
            width: 19px;
        }
        .bgg {
            background-image: url({{ asset('img/bg/bgg.gif') }});
        }



        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd2-all {
            border: 1px solid #db9f73;
        }
        .brd2-top {
            border-top: 1px solid #db9f73;
        }
        .brd2, .brd2 td {
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
        .regblk, .regblk * {
            color: #49382d;
        }
        .bg_l {
            background-image: url(/img/bg/bg_l.gif);
        }
        .p6h, .p6h td {
            padding-left: 6px;
            padding-right: 6px;
        }

        .pointer, .pointer input {
            cursor: pointer;
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
        .store-list-item {
            width: 340px;
            height: 90px;
            border: 1px solid #DB9F73;
            border-radius: 5px;
            background-image: url(/img/bg/tbl-usi_bg.gif);
            background-repeat: repeat;
        }
        .barter-store-grid {
            margin: -6px;
        }
        .barter-store-item {
            display: inline-block;
            box-sizing: border-box;
            width: 360px;
            height: 116px;
            margin: 6px;
            overflow: hidden;
            border: 1px solid #db9f73;
            border-radius: 5px;
            background: url({{ asset('img/bg/tbl-usi_bg.gif') }}) repeat;
            vertical-align: top;
        }
        .barter-store-item__form {
            display: inline-block;
            width: 330px;
            height: 110px;
            margin: 3px;
        }
        .barter-store-item__table {
            width: 330px;
            height: 100%;
        }
        .barter-store-item__main-row {
            height: 76px;
        }
        .barter-store-item__image {
            width: 60px;
            height: 60px;
            margin: 8px;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
        }
        .barter-store-item__details {
            width: 100%;
            padding: 5px 5px 0 0;
        }
        .barter-store-item__name {
            display: block;
            width: 245px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .barter-store-item__money {
            white-space: nowrap;
        }
        .barter-store-item__money span + span {
            margin-left: 6px;
        }
        .barter-store-item__cost-label {
            width: 76px;
            padding-left: 8px;
            color: darkgreen;
            white-space: nowrap;
        }
        .barter-store-item__costs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(68px, 1fr));
            align-items: start;
            gap: 2px;
            margin-top: 1px;
        }
        .barter-store-item__cost {
            display: flex;
            align-items: center;
            min-width: 0;
            white-space: nowrap;
        }
        .barter-store-item__cost img {
            width: 22px;
            height: 22px;
            margin-right: 4px;
            object-fit: contain;
            cursor: help;
        }
        .barter-store-item__available {
            color: red;
        }
    </style>

    {!! $playerStatsScript !!}
    {!! $page->itemTooltipScript !!}
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body leftmargin="0" rightmargin="0">

@php
    $isBarterShop = $page->shopType === \App\Modules\Structure\Infrastructure\Persistence\Models\Structure::TYPE_BARTER_SHOP;
    $indexRoute = $isBarterShop ? 'barter_shop' : 'shop';
    $addCartRoute = $isBarterShop ? 'barter_shop.add_cart' : 'shop.add_cart';
    $deleteCartRoute = $isBarterShop ? 'barter_shop.delete_cart' : 'shop.delete_cart';
    $clearCartRoute = $isBarterShop ? 'barter_shop.clear_cart' : 'shop.clear_cart';
    $purchaseRoute = $isBarterShop ? 'barter_shop.purchase' : 'shop.purchase';

    $btnLeft1 = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1 = 'img/bg/btn/btn-right1.gif';

    $btnLeft2 = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2 = 'img/bg/btn/btn-right2.gif';
@endphp
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        <td width="19"><img id="left_1" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_1" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_1" href="{{ route($indexRoute, ['id' => $page->shopId]) }}" title="Купить" class="btn_2">Купить</a>
        </td>
        <td width="19"><img id="right_1" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        @unless($isBarterShop)
            <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
            <td width="60" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a id="center_2" href="{{ route('shop.sell_item', ['id' => $page->shopId]) }}" title="Продать" class="btn_1">Продать</a></td>
            <td width="19"><img id="right_2" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
        @endunless

        <td></td>

        <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_4" href="{{ route('location') }}" title="Выход" class="btn_1">Выход</a></td>
        <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

@if(count($page->categories) > 0)
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
        <tbody>
        <tr height="21">
            @foreach($page->categories as $category)
                @php $active = $page->activeCategoryId === $category['id']; @endphp
                <td width="19"><img src="{{ asset($active ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                <td width="60" align="center" style="background: url({{ asset($active ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0 2px 6px;">
                    <a href="{{ route($indexRoute, ['id' => $page->shopId, 'category_id' => $category['id']]) }}" class="{{ $active ? 'btn_2' : 'btn_1' }}">{{ $category['name'] }}</a>
                </td>
                <td width="19"><img src="{{ asset($active ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
            @endforeach
            <td></td>
        </tr>
        </tbody>
    </table>
@endif

<br>
<table class="coll w100" height="100%">
    <tbody>
    <tr>
        <td valign="top" height="100%">
                        @if(count($page->items) > 0)
                            @if($isBarterShop)
                                <div class="barter-store-grid">
                            @endif
                            @foreach($page->items as $item)
                                @if($isBarterShop)
                                    <div class="barter-store-item">
                                @endif
                                <form method="post" action="{{ route($addCartRoute, ['id' => $page->shopId]) }}"
                                      class="{{ $isBarterShop ? 'barter-store-item__form' : '' }}"
                                      @unless($isBarterShop) style="margin: 3px; display: inline-block;" @endunless>
                                    @csrf
                                    <table border="0" cellspacing="0" cellpadding="0" id="item_list"
                                           class="{{ $isBarterShop ? 'barter-store-item__table' : 'store-list-item' }}">
                                        <tbody>
                                        <tr @if($isBarterShop) class="barter-store-item__main-row" @endif>
                                            <td align="left" width="60" @unless($isBarterShop) valign="top" @endunless>
                                                <div class="{{ $isBarterShop ? 'barter-store-item__image' : '' }}"
                                                     style="position: relative; margin: 8px; background-image: url('{{ $item->image }}'); @unless($isBarterShop) background-size: cover; width: 50px; height: 50px; @endunless">
                                                    <table width="{{ $isBarterShop ? 60 : 50 }}" height="{{ $isBarterShop ? 60 : 50 }}" cellpadding="0" cellspacing="0" border="0" style="position: absolute; inset: 0; z-index: 10;">
                                                        <tbody>
                                                        <tr>
                                                            <td data-id="{{ $item->itemId }}" style="cursor:pointer;"
                                                                onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                                onclick="window.open('{{ $item->infoUrl }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                                                                valign="bottom">
                                                                &nbsp;
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                            <td valign="top" class="{{ $isBarterShop ? 'barter-store-item__details' : '' }}" @unless($isBarterShop) style="padding: 5px; width: 100%;" @endunless>
                                                <input type="hidden" name="shop_item_id" value="{{ $item->shopItemId }}">
                                                <table class="w100 coll" border="0">
                                                    <colgroup>
                                                        <col>
                                                        <col width="10%">
                                                        <col width="20%">
                                                    </colgroup>
                                                    <tbody>
                                                    <tr>
                                                        <td colspan="3">
                                                            <a href="{{ $item->infoUrl }}"
                                                               onclick="window.open('{{ $item->infoUrl }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                                                               class="b {{ $isBarterShop ? 'barter-store-item__name' : '' }}"
                                                               style="color:{{ $item->color }}; @unless($isBarterShop) text-overflow: ellipsis; display: block; overflow: hidden; white-space: nowrap; width: 250px; @endunless">{{ $item->name }}</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" nowrap="" title="Тип предмета">
                                                            <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $item->typeName }}
                                                        </td>
                                                    </tr>
                                                    @if($item->requiredLevel !== null)
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="2" title="Требуемый уровень" nowrap>
                                                                <img src="{{ asset('img/icon/tbl-shp_level-icon.gif') }}" width="11" height="10" align="absmiddle">
                                                                Уровень <b class="red">{{ $item->requiredLevel }}</b>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td class="b {{ $isBarterShop ? 'barter-store-item__money' : '' }}" title="Цена" @unless($isBarterShop) style="padding-top: 13px;" @endunless valign="top">
                                                            @if($item->price)
                                                                <span title="Монеты" @unless($isBarterShop) style="display: block;" @endunless>
                                                                    <img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle">
                                                                    &nbsp;{{ format_money($item->price) }}
                                                                </span>
                                                            @endif
                                                            @if($item->diamond)
                                                                <span title="Бриллиант" @unless($isBarterShop) style="display: block;" @endunless>
                                                                    <img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle">
                                                                    &nbsp;{{ format_money($item->diamond) }}
                                                                </span>
                                                            @endif
                                                            @unless($isBarterShop)
                                                                @foreach($item->requirements as $requirement)
                                                                    <div title="{{ $requirement['name'] }}">
                                                                        <img src="{{ $requirement['image'] }}"
                                                                             data-id="{{ $requirement['id'] }}"
                                                                             onmouseover="showItemInfo(this,event,2)"
                                                                             onmouseout="showItemInfo(this,event,0)"
                                                                             width="18" height="18" align="absmiddle"
                                                                             style="object-fit: contain; cursor: help;">
                                                                        &nbsp;{{ $requirement['quantity'] }}
                                                                    </div>
                                                                @endforeach
                                                            @endunless
                                                        </td>
                                                        <td align="center" valign="top" style="{{ $isBarterShop ? '' : 'padding-top: 16px;' }} width: 60px;">
                                                            <div class="cart-amount-sell-price">
                                                                <span class="cart-amount-input-cont">
                                                                    <span class="b-input">
                                                                        <span class="b-input__inner">
                                                                            <span class="arrow left left-disabled" onclick="shopItemCounter(this);" title="Уменьшить кол-во"></span>
                                                                            <span class="arrow right" onclick="shopItemCounter(this);" title="Увеличить кол-во"></span>
                                                                            <input type="text" name="quantity" data-id="{{ $item->shopItemId }}" value="1" class="cart_amount_sell_input count_buy" autocomplete="off">
                                                                        </span>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td align="center" valign="top" style="{{ $isBarterShop ? '' : 'padding-top: 16px;' }} width: 80px;">
                                                            <b class="butt2 pointer "><b>
                                                                    <input value="В корзину" type="submit" onclick="if(document._submit)return false;document._submit=true;" style="width:{{ $isBarterShop ? 55 : 63 }}px">
                                                                </b>
                                                            </b>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        @if($isBarterShop && count($item->requirements) > 0)
                                            <tr>
                                                <td class="brd2-top barter-store-item__cost-label">
                                                    <b>Стоимость:</b>
                                                </td>
                                                <td class="brd2-top">
                                                    <div class="barter-store-item__costs">
                                                        @foreach($item->requirements as $requirement)
                                                            <div class="barter-store-item__cost" title="{{ $requirement['name'] }}">
                                                                <img src="{{ $requirement['image'] }}"
                                                                     data-id="{{ $requirement['id'] }}"
                                                                     onmouseover="showItemInfo(this,event,2)"
                                                                     onmouseout="showItemInfo(this,event,0)"
                                                                     alt="{{ $requirement['name'] }}">
                                                                <b>{{ $requirement['quantity'] }}</b>&nbsp;/&nbsp;<b class="barter-store-item__available">{{ $requirement['availableQuantity'] }}</b>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </form>
                                @if($isBarterShop)
                                    </div>
                                @endif
                            @endforeach
                            @if($isBarterShop)
                                </div>
                            @endif
                        @else
                            <div align="center" style="color: #49382D"><b>В этой категории товаров нет!</b></div>
                        @endif
        </td>
        <td width="10"></td>
        <td valign="top" width="{{ $isBarterShop ? '30%' : '35%' }}">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Ваша корзина</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 4px">
                                    <form method="post" action="{{ route($purchaseRoute, ['id' => $page->shopId]) }}" id="action_form">
                                        @csrf
                                        @if($page->cart->getItems()->count())
                                            <table class="coll w100 p10h p4v brd2">
                                                <colgroup>
                                                    <col>
                                                    <col>
                                                    <col>
                                                    <col width="1%">
                                                </colgroup>
                                                <tbody>
                                                @foreach($page->cart->getItems() as $cartItem)
                                                    <tr class="bg_l">
                                                        <td>
                                                            <a href="{{ route('items.info.share', ['id' => $cartItem->shopItem->item->id]) }}"
                                                               data-id="{{ $cartItem->shopItem->item->id }}"
                                                               onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                               onclick="window.open('{{ route('items.info.share', ['id' => $cartItem->shopItem->item->id]) }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                                                               style="color:{{ $cartItem->shopItem->item->rarity?->color() ?? '#666666' }};"
                                                               class="b" title="">{{ $cartItem->shopItem->item->name }}</a>
                                                        </td>
                                                        <td class="b red" align="center">
                                                            @if($cartItem->shopItem->diamond)
                                                                <span title="Бриллиант">
                                                                    <img src="{{ asset('img/icon/m_dmd.gif') }}" alt="diamond" border="0" width="11" height="11" align="absmiddle">
                                                                </span>&nbsp;{{ format_money($cartItem->shopItem->diamond * $cartItem->quantity) }}
                                                                <br>
                                                            @endif
                                                            @if($cartItem->shopItem->price)
                                                                <span title="Монет">
                                                                    <img src="{{ asset('img/icon/m_game.gif') }}" alt="money" border="0" width="11" height="11" align="absmiddle">
                                                                </span>&nbsp;{{ format_money($cartItem->shopItem->price * $cartItem->quantity) }}
                                                                <br>
                                                            @endif
                                                            @foreach($cartItem->shopItem->requirements as $requirement)
                                                                <span title="{{ $requirement->item?->name }}">
                                                                    <img src="{{ $requirement->item?->image }}"
                                                                         data-id="{{ $requirement->share_item_id }}"
                                                                         onmouseover="showItemInfo(this,event,2)"
                                                                         onmouseout="showItemInfo(this,event,0)"
                                                                         width="18" height="18" align="absmiddle"
                                                                         style="object-fit: contain; cursor: help;">
                                                                </span>&nbsp;{{ $requirement->quantity * $cartItem->quantity }}<br>
                                                            @endforeach
                                                        </td>
                                                        <td>{{ $cartItem->quantity }} шт</td>
                                                        <td>
                                                            <a href="{{ route($deleteCartRoute, ['id' => $page->shopId, 'cartId' => $cartItem->id]) }}" title="Удалить">
                                                                <img src="{{ asset('img/icon/tbl-shp_x.gif') }}" alt="delete" width="11" height="13" border="0">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <b>Корзина пуста!</b>
                                            <br>
                                        @endif

                                        <br>
                                        <table class="coll w100 p10h p4v brd2-all">
                                            <colgroup>
                                                <col>
                                                <col width="30%">
                                            </colgroup>
                                            <tbody>
                                            @if($page->cart->getItems()->count())
                                                <tr>
                                                    <td class="brd2-top brd2-bt"><b>На общую сумму:</b></td>
                                                    <td class="brd2-top brd2-bt b red">
                                                        @if($page->cart->getTotalDiamond())
                                                            <span title="Бриллиант">
                                                                <img src="{{ asset('img/icon/m_dmd.gif') }}" alt="diamond" border="0" width="11" height="11" align="absmiddle">
                                                            </span>&nbsp;{{ format_money($page->cart->getTotalDiamond()) }}<br>
                                                        @endif
                                                        @if($page->cart->getTotalPrice())
                                                            <span title="Монет">
                                                                <img src="{{ asset('img/icon/m_game.gif') }}" alt="money" border="0" width="11" height="11" align="absmiddle">
                                                            </span>&nbsp;{{ format_money($page->cart->getTotalPrice()) }}<br>
                                                        @endif
                                                        @foreach($page->cart->getRequirementTotals() as $requirement)
                                                            <span title="{{ $requirement['item']->name }}">
                                                                <img src="{{ $requirement['item']->image }}"
                                                                     data-id="{{ $requirement['item']->id }}"
                                                                     onmouseover="showItemInfo(this,event,2)"
                                                                     onmouseout="showItemInfo(this,event,0)"
                                                                     width="18" height="18" align="absmiddle"
                                                                     style="object-fit: contain; cursor: help;">
                                                            </span>&nbsp;{{ $requirement['quantity'] }}<br>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="bg_l">
                                                <td class="brd2-top brd2-bt"><b>У Вас денег:</b></td>
                                                <td class="brd2-top brd2-bt b red">
                                                    <span title="Бриллиант">
                                                        <img src="{{ asset('img/icon/m_dmd.gif') }}" alt="diamond" border="0" width="11" height="11" align="absmiddle">
                                                    </span>&nbsp;{{ format_money($page->diamonds) }}
                                                    <br>
                                                    <span title="Серебряный">
                                                        <img src="{{ asset('img/icon/m_game.gif') }}" alt="money" border="0" width="11" height="11" align="absmiddle">
                                                    </span>&nbsp;{{ format_money($page->money) }}
                                                    <br>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <span class="butt1 pointer ">
                                            <span>
                                                <input value="Оплатить товар" type="submit" onclick="if(document._submit)return false;document._submit=true;" class="grnn">
                                            </span>
                                        </span>
                                        <br>
                                        <span class="butt1 pointer ">
                                            <span>
                                                <input value="Очистить корзину" type="button" onclick="location.href='{{ route($clearCartRoute, ['id' => $page->shopId]) }}';" class="redd">
                                            </span>
                                        </span>
                                        <br>
                                    </form>

                                    <br>
                                    <div class="p10h p2v brd2-all bg_l" align="left">
                                        @if($isBarterShop)
                                            В «{{ $page->shopName }}» товары можно приобретать за монеты, алмазы и указанные предметы. Все составляющие цены списываются одновременно.
                                        @else
                                            В магазине Вы можете приобрести необходимую Вам экипировку, снадобья и прочие предметы,
                                            которые помогут Вашему персонажу.
                                        @endif
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


    function shopItemCounter(el) {
        const current = el;

        if (current.classList.contains('left-disabled')) {
            return false;
        }

        let left = current;
        let right = current;

        if (current.classList.contains('left')) {
            right = current.nextElementSibling;
        } else if (current.classList.contains('right')) {
            left = current.previousElementSibling;
        }

        const input = current.parentElement.querySelector('input');

        if (current === left) {
            const value = shopCounterValue(input);
            if (value > 1) {
                input.value = value - 1;
                shopChangeCounter(input);
            }
        } else if (current === right) {
            input.value = shopCounterValue(input) + 1;
            shopChangeCounter(input);
        }
        return true;
    }

    function shopCounterValue(el) {
        return Math.max(1, parseInt(el.value) || 1);
    }

    function shopChangeCounter(el) {
        const value = shopCounterValue(el);
        const left = el.closest('.b-input__inner').querySelector('.left');

        if (value <= 1) {
            left.classList.add('left-disabled');
        } else {
            left.classList.remove('left-disabled');
        }

        el.value = value;
    }

    function shopCounterKeypress(e, el) {
        const key = e.keyCode || e.which;
        const parent = el.parentElement;

        if (key === 38) { // Up Arrow
            shopItemCounter(parent.querySelector('.right'));
        } else if (key === 40) { // Down Arrow
            shopItemCounter(parent.querySelector('.left'));
        }
    }

    // Добавляем обработчики событий
    document.querySelectorAll('.cart_amount_sell_input').forEach(input => {
        // Обработчик для стрелок вверх/вниз
        input.addEventListener('keydown', function (event) {
            shopCounterKeypress(event, this);
        });

        // Обработчик ввода вручную
        input.addEventListener('input', function () {
            shopChangeCounter(this);
        });
    });

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    let money = parseInt('{{ $page->money }}');
    let diamond = parseInt('{{ $page->diamonds }}');

    parent.sendToFrame('character-frame', { money, diamond });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
