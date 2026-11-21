<!DOCTYPE html>
<html lang="ru">
<head>
    @include('partials.image-tabs-fix')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 12px; }
        .b { font-weight: 700; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }

        .tbl-usi-hdr { background: url(/img/bg/tbl-usi-hdr.gif) no-repeat; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }

        .sell-grid { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
        .sell-grid__section { display: flex; flex-direction: column; gap: 10px; min-width: 0; }
        .sell-grid__heading { margin: 0 auto; }
        .sell-grid__list { display: grid; grid-template-columns: repeat(auto-fill, 72px); grid-auto-rows: 71px; margin: 0; padding: 0; list-style: none; }
        .sell-grid__item { position: relative; width: 72px; height: 71px; cursor: pointer; }
        .sell-grid__item > table { position: absolute; top: 5px; left: 6px; z-index: 1; margin: 0; background-size: cover; }
        .sell-grid__item::after { content: ''; position: absolute; inset: 0; z-index: 2; width: 72px; height: 71px; background: url({{ asset('main/images/user-reward-frame.png') }}) 0 0 no-repeat; pointer-events: none; }
        .sell-grid__item:hover::after { filter: brightness(1.35); }
        .sell-grid__hint { text-align: center; color: #49382d; margin: 0 0 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .sell-grid__item[hidden], .bpdig[hidden] { display: none; }
        .sell-cart-quantity { width: 1%; padding: 4px 6px; text-align: center; vertical-align: middle; white-space: nowrap; }
        .sell-cart-quantity .cart-amount-sell-price { display: inline-block; vertical-align: middle; }
        /* Поиск — как на странице слотов (/slots) */
        .sell-grid__search { text-align: center; margin: 0 0 8px; }
        .b-filter { display: inline-block; position: relative; margin: 0 0 0 5px; }
        .b-filter__icon { position: absolute; z-index: 1; top: -1px; left: -5px; width: 23px; height: 23px; cursor: pointer; border: 0; background: url(/img/icon/btn-search.png) no-repeat; }
        .b-filter__reset { display: none; position: absolute; z-index: 1; top: 2px; right: 3px; width: 17px; height: 17px; cursor: pointer; border: 0; background: url(/img/icon/btn-clear.png) no-repeat; }
        .ff__input-wrap { display: inline-block; width: 210px; height: 23px; vertical-align: middle; background-position: 0 0; }
        .ff__input-wrap, .ff__input-wrap-inner { background: url(/img/bg/input/input.png) no-repeat; }
        .ff__input-wrap-inner { position: relative; height: 100%; margin: 0 0 0 6px; padding: 0 6px 0 0; background-position: 100% -23px; }
        .ff__input-wrap-input { position: relative; height: 100%; margin: 0 15px 0 21px; }
        .ff__input-wrap input, .ff__input-wrap label { position: absolute; top: 4px; left: 0; width: 100% !important; margin: 0; padding: 0; font: 11px Tahoma, Arial, sans-serif; color: #6c382c; border: 0; background: none; outline: none; }
        .ff__input-wrap label { z-index: 1; overflow: hidden; text-align: left; text-overflow: ellipsis; color: #c09f79; cursor: text; }
        .sell-grid__item.item--filtered { filter: grayscale(100%); opacity: .4; pointer-events: none; }
        .sell-grid__empty { text-align: center; padding: 20px; color: #49382d; }
        .bpdig { border: solid 1px #6f4a24; background-color: #6e534c; width: 34px; height: 12px; color: #f6d9a6; font-weight: bold; margin: 2px; text-align: center; font-size: 10px; line-height: 12px; }

        .sell-dialog { display: none; position: fixed; z-index: 9999; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 380px; max-width: calc(100vw - 20px); }
        .sell-dialog.is-open { display: block; }
        .sell-dialog__backdrop { display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0, 0, 0, .45); }
        .sell-dialog__backdrop.is-open { display: block; }
        .sell-dialog .popup_global_content { padding: 16px 20px !important; }
        .sell-dialog__question { margin: 0 0 16px; line-height: 1.5; text-align: center; }
        .sell-dialog__item { display: flex; align-items: center; justify-content: center; gap: 14px; margin: 0 0 16px; }
        .sell-dialog__frame { position: relative; flex: 0 0 72px; width: 72px; height: 71px; }
        .sell-dialog__frame img { position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; object-fit: cover; }
        .sell-dialog__frame::after { content: ''; position: absolute; inset: 0; background: url({{ asset('main/images/user-reward-frame.png') }}) 0 0 no-repeat; pointer-events: none; }
        .coin-icon { display: inline-block; width: 11px; height: 11px; vertical-align: middle; border: 0; }
        .sell-dialog__name { font-weight: bold; line-height: 1.5; overflow-wrap: anywhere; }
        .sell-dialog__qty { display: flex; align-items: center; justify-content: center; gap: 8px; white-space: nowrap; margin: 0 0 12px; }
        .sell-dialog__qty .cart-amount-input-cont { float: none; margin: 0 10px; height: 23px; line-height: 23px; }
        .sell-dialog__qty input[type=text] { box-sizing: content-box; width: 34px; margin: 0; }
        .sell-dialog__total { text-align: center; margin: 0 0 16px; }
        .sell-dialog__actions { display: flex; align-items: center; justify-content: center; gap: 12px; }
    </style>
    {!! $page->itemTooltipScript !!}
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body leftmargin="0" rightmargin="0">
@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1 = 'img/bg/btn/btn-right1.gif';
    $btnLeft2 = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2 = 'img/bg/btn/btn-right2.gif';
    $cart = $page->sellCart;
    // В сетке — остаток после корзины; полностью отложенные скрыты, но остаются в разметке,
    // чтобы вернуться, если в корзине уменьшат количество.
    $gridItems = collect($page->items)
        ->sortBy([['typeName', 'asc'], ['name', 'asc']])
        ->map(fn ($item) => ['dto' => $item, 'left' => $item->count - $cart->quantityOf($item->itemId)])
        ->values();
    $ownedCounts = collect($page->items)->mapWithKeys(fn ($item) => [$item->itemId => $item->count]);
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="60" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 5px;">
            <a href="{{ route($indexRoute ?? 'shop', ['id' => $page->shopId]) }}" title="Купить" class="btn_1">Купить</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="60" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 5px;">
            <a href="{{ route('shop.sell_item', ['id' => $page->shopId]) }}" title="Продать" class="btn_2">Продать</a></td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td width="100%"></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="60" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 5px;">
            <a href="{{ route('location') }}" title="Выход" class="btn_1">Выход</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="left"></td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px">
                        <table class="coll w100 p10h p2v brd2-all" style="margin-bottom: 10px;">
                            <tbody>
                            <tr class="bg_l">
                                <td align="left" nowrap=""><b>Рюкзак:</b> &nbsp;<b class="redd">{{ $page->bagUsed }}/{{ $page->bagCapacity }}</b></td>
                                <td align="right" nowrap="">
                                    <b>Монет:</b>
                                    &nbsp;<b class="redd"><img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="" title="Золотой">&nbsp;{{ format_money($page->money) }}</b>
                                    &nbsp;&nbsp;&nbsp;<b class="redd"><img class="coin-icon" src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt="" title="Бриллиант">&nbsp;{{ format_money($page->diamonds) }}</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="sell-grid__search">
                            <div class="b-filter">
                                <span class="b-filter__icon" title="Поиск по названию">&nbsp;</span>
                                <span class="b-filter__reset" title="Сбросить фильтр">&nbsp;</span>
                                <div class="ff__input-wrap">
                                    <div class="ff__input-wrap-inner">
                                        <div class="ff__input-wrap-input">
                                            <input type="text" id="sell-filter" value="" autocomplete="off">
                                            <label for="sell-filter">Введите название</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sell-grid__hint">Нажмите на предмет, чтобы добавить его в корзину на продажу</div>
                        <div class="sell-grid">
                            @if($gridItems->isNotEmpty())
                                <ul class="sell-grid__list">
                                    @foreach($gridItems as $item)
                                        <li class="sell-grid__item" data-grid-item="{{ $item['dto']->itemId }}" data-title="{{ mb_strtolower($item['dto']->name) }}" @if($item['left'] <= 0) hidden @endif>
                                            <table width="60" height="60" cellpadding="0" cellspacing="0" border="0" style="background-image: url('{{ asset($item['dto']->image) }}');">
                                                <tbody>
                                                <tr>
                                                    <td data-id="{{ $item['dto']->itemId }}"
                                                        data-name="{{ $item['dto']->name }}"
                                                        data-color="{{ $item['dto']->color }}"
                                                        data-image="{{ asset($item['dto']->image) }}"
                                                        data-count="{{ $item['left'] }}"
                                                        data-price="{{ $item['dto']->sellPrice }}"
                                                        onclick="openSellDialog(this)"
                                                        onmouseover="showItemInfo(this,event,2)"
                                                        onmouseout="showItemInfo(this,event,0)"
                                                        valign="bottom">
                                                        &nbsp;
                                                        <div class="bpdig" @if($item['left'] <= 1) hidden @endif>{{ $item['left'] }}</div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="sell-grid__empty">Нет предметов, которые можно продать в магазин.</div>
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
        <td width="10"></td>
        <td valign="top" width="35%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Корзина на продажу</td>
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
                        <form method="post" action="{{ route('shop.sell_item.cart_checkout', ['id' => $page->shopId]) }}" data-cart-quantity-form
                              data-update-url="{{ route('shop.sell_item.cart_update', ['id' => $page->shopId]) }}"
                              data-id-field="item_id"
                              data-save-error="Не удалось сохранить количество. Попробуйте ещё раз.">
                            @csrf
                            @if(! $cart->isEmpty())
                                <table class="coll w100 p10h p4v brd2">
                                    <colgroup>
                                        <col>
                                        <col>
                                        <col width="1%">
                                        <col width="1%">
                                    </colgroup>
                                    <tbody>
                                    @foreach($cart->lines as $line)
                                        <tr class="bg_l" data-cart-row="{{ $line->itemId }}">
                                            <td align="left">
                                                <a href="{{ $line->infoUrl }}"
                                                   data-id="{{ $line->itemId }}"
                                                   onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                   onclick="window.open('{{ $line->infoUrl }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                                                   style="color:{{ $line->color }};" class="b">{{ $line->name }}</a>
                                            </td>
                                            <td class="b red" align="center" nowrap="">
                                                <img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="">&nbsp;<span data-cart-line-price>{{ format_money($line->total()) }}</span>
                                            </td>
                                            @php $lineMax = max($line->quantity, (int) ($ownedCounts[$line->itemId] ?? $line->quantity)); @endphp
                                            <td class="sell-cart-quantity" align="center">
                                                <div class="cart-amount-sell-price">
                                                    <span class="cart-amount-input-cont">
                                                        <span class="b-input">
                                                            <span class="b-input__inner">
                                                                <span class="arrow left @if($line->quantity <= 1) left-disabled @endif" onclick="sellCartCounter(this, -1);" title="Уменьшить кол-во"></span>
                                                                <span class="arrow right @if($line->quantity >= $lineMax) right-disabled @endif" onclick="sellCartCounter(this, 1);" title="Увеличить кол-во"></span>
                                                                <input type="text" name="cart_quantities[{{ $line->itemId }}]" value="{{ $line->quantity }}"
                                                                       data-cart-quantity="{{ $line->itemId }}" data-max="{{ $lineMax }}" inputmode="numeric" aria-label="Количество"
                                                                       class="cart_amount_sell_input" autocomplete="off">
                                                            </span>
                                                        </span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('shop.sell_item.cart_delete', ['id' => $page->shopId, 'cartId' => $line->cartId]) }}" title="Убрать из корзины">
                                                    <img src="{{ asset('img/icon/tbl-shp_x.gif') }}" alt="Убрать" width="11" height="13" border="0">
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
                                    <col width="35%">
                                </colgroup>
                                <tbody>
                                @if(! $cart->isEmpty())
                                    <tr>
                                        <td class="brd2-top brd2-bt" align="left"><b>Вы получите:</b></td>
                                        <td class="brd2-top brd2-bt b red" nowrap="">
                                            <img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="">&nbsp;<span data-cart-total-price>{{ format_money($cart->totalPrice()) }}</span>
                                        </td>
                                    </tr>
                                @endif
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" align="left"><b>У Вас денег:</b></td>
                                    <td class="brd2-top brd2-bt b red" nowrap="">
                                        <img class="coin-icon" src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt="" title="Бриллиант">&nbsp;{{ format_money($page->diamonds) }}
                                        <br>
                                        <img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="" title="Монеты">&nbsp;{{ format_money($page->money) }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            @if(! $cart->isEmpty())
                                <br>
                                <span class="butt1 pointer"><span><input value="Продать товар" type="submit" onclick="if(document._submit)return false;document._submit=true;" class="grnn"></span></span>
                                <br>
                                <span class="butt1 pointer"><span><input value="Очистить корзину" type="button" onclick="location.href='{{ route('shop.sell_item.cart_clear', ['id' => $page->shopId]) }}';" class="redd"></span></span>
                                <br>
                            @endif
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

<div class="sell-dialog__backdrop" id="sell-dialog-backdrop" onclick="closeSellDialog()"></div>
<form class="sell-dialog" id="sell-dialog" method="post" action="{{ route('shop.sell_item.cart_add', ['id' => $page->shopId]) }}"
      role="dialog" aria-modal="true" aria-labelledby="sell-dialog-title" autocomplete="off">
    {{ csrf_field() }}
    <input type="hidden" name="item_id" id="sell-dialog-item">
    <div class="popup_global_container">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title" id="sell-dialog-title">Продажа предмета</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closeSellDialog();"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content">
                    <div class="sell-dialog__question redd">Сколько штук добавить в корзину на продажу?</div>
                    <div class="sell-dialog__item">
                        <span class="sell-dialog__frame"><img id="sell-dialog-image" src="" alt=""></span>
                        <div>
                            <div class="sell-dialog__name" id="sell-dialog-name"></div>
                            <div>Цена за 1 шт: <b id="sell-dialog-unit"></b> <img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt=""></div>
                        </div>
                    </div>
                    <div class="sell-dialog__qty" id="sell-dialog-qty-row">
                        <b>Количество:</b>
                        <span class="cart-amount-input-cont">
                            <span class="b-input">
                                <span class="b-input__inner">
                                    <span class="arrow left" id="sell-dialog-minus" onclick="stepSellAmount(-1)" title="Уменьшить кол-во"></span>
                                    <span class="arrow right" id="sell-dialog-plus" onclick="stepSellAmount(1)" title="Увеличить кол-во"></span>
                                    <input type="text" name="quantity" id="sell-dialog-count" value="1" class="cart_amount_sell_input" inputmode="numeric">
                                </span>
                            </span>
                        </span>
                        <span>из <span id="sell-dialog-max"></span></span>
                        <b class="butt1 pointer"><b><input value="Все" type="button" class="redd" style="width: 60px;" onclick="setSellAmount(sellDialog.max)"></b></b>
                    </div>
                    <div class="sell-dialog__total">Вы получите: <b class="redd" id="sell-dialog-total"></b> <img class="coin-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt=""></div>
                    <div class="sell-dialog__actions">
                        <b class="butt1 pointer"><b><input value="В корзину" type="submit" class="redd" style="width: 100px;"></b></b>
                        <b class="butt1 pointer"><b><input value="Отмена" type="button" class="redd" style="width: 100px;" onclick="closeSellDialog();"></b></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="popup-left-bottom">
            <div class="popup-right-bottom">
                <div class="popup-bottom-center"></div>
            </div>
        </div>
    </div>
</form>

<script>
    const sellDialog = { el: document.getElementById('sell-dialog'), max: 1, price: 0 };
    const sellCount = document.getElementById('sell-dialog-count');

    function formatSellMoney(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function openSellDialog(cell) {
        if (typeof showItemInfo === 'function') {
            showItemInfo(cell, null, 0);
        }
        sellDialog.max = parseInt(cell.dataset.count, 10) || 1;
        sellDialog.price = parseInt(cell.dataset.price, 10) || 0;

        const name = document.getElementById('sell-dialog-name');
        name.textContent = cell.dataset.name;
        name.style.color = cell.dataset.color;
        document.getElementById('sell-dialog-image').src = cell.dataset.image;
        document.getElementById('sell-dialog-unit').textContent = formatSellMoney(sellDialog.price);
        document.getElementById('sell-dialog-max').textContent = sellDialog.max;
        document.getElementById('sell-dialog-qty-row').style.display = sellDialog.max > 1 ? '' : 'none';

        document.getElementById('sell-dialog-item').value = cell.dataset.id;
        setSellAmount(sellDialog.max);

        sellDialog.el.classList.add('is-open');
        document.getElementById('sell-dialog-backdrop').classList.add('is-open');
    }

    function closeSellDialog() {
        sellDialog.el.classList.remove('is-open');
        document.getElementById('sell-dialog-backdrop').classList.remove('is-open');
    }

    function clampSellAmount(value) {
        const parsed = parseInt(value, 10);
        return Math.min(Math.max(1, Number.isNaN(parsed) ? 1 : parsed), sellDialog.max);
    }

    function setSellAmount(value) {
        const amount = clampSellAmount(value);
        sellCount.value = amount;
        document.getElementById('sell-dialog-minus').classList.toggle('left-disabled', amount <= 1);
        document.getElementById('sell-dialog-plus').classList.toggle('right-disabled', amount >= sellDialog.max);
        document.getElementById('sell-dialog-total').textContent = formatSellMoney(amount * sellDialog.price);
    }

    function stepSellAmount(delta) {
        setSellAmount(clampSellAmount(sellCount.value) + delta);
    }

    sellCount.addEventListener('input', () => {
        if (sellCount.value !== '') {
            setSellAmount(sellCount.value);
        }
    });
    sellCount.addEventListener('blur', () => setSellAmount(sellCount.value));
    sellCount.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowUp') { event.preventDefault(); stepSellAmount(1); }
        if (event.key === 'ArrowDown') { event.preventDefault(); stepSellAmount(-1); }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSellDialog();
        }
    });
    sellDialog.el.addEventListener('submit', () => setSellAmount(sellCount.value));

    parent.sendToFrame?.('character-frame', { money: {{ (int) $page->money }}, diamond: {{ (int) $page->diamonds }} });

    @if (session()->has('message'))
        window.parent.showErrorIframe?.(@json(session('message')));
    @endif
</script>

<script>
    // Счётчик количества в корзине на продажу (как в премиум-магазине): сохраняется сразу, без перезагрузки.
    function sellCartValue(input) {
        const max = parseInt(input.dataset.max, 10) || 1;
        return Math.min(max, Math.max(1, parseInt(input.value, 10) || 1));
    }

    function sellCartApply(input) {
        const value = sellCartValue(input);
        const inner = input.closest('.b-input__inner');
        input.value = value;
        inner.querySelector('.left').classList.toggle('left-disabled', value <= 1);
        inner.querySelector('.right').classList.toggle('right-disabled', value >= (parseInt(input.dataset.max, 10) || 1));
        input.dispatchEvent(new CustomEvent('shop-cart-quantity-change', { bubbles: true }));
    }

    function sellCartCounter(arrow, delta) {
        if (arrow.classList.contains('left-disabled') || arrow.classList.contains('right-disabled')) {
            return;
        }
        const input = arrow.parentElement.querySelector('input');
        if (input.disabled) {
            return;
        }
        input.value = sellCartValue(input) + delta;
        sellCartApply(input);
    }

    document.querySelectorAll('[data-cart-quantity]').forEach((input) => {
        input.addEventListener('change', () => sellCartApply(input));
        input.addEventListener('keydown', (event) => {
            const inner = input.closest('.b-input__inner');
            if (event.key === 'ArrowUp') { event.preventDefault(); sellCartCounter(inner.querySelector('.right'), 1); }
            if (event.key === 'ArrowDown') { event.preventDefault(); sellCartCounter(inner.querySelector('.left'), -1); }
        });
    });

    // После сохранения обновляем остаток предмета в сетке слева.
    document.querySelector('[data-cart-quantity-form]')?.addEventListener('shop-cart-quantity-saved', (event) => {
        const { id, data } = event.detail;
        const cell = document.querySelector('[data-grid-item="' + id + '"]');
        if (!cell || data.left === undefined) {
            return;
        }
        const left = Number(data.left);
        cell.hidden = left <= 0;
        cell.querySelector('td[data-count]').dataset.count = left;
        const badge = cell.querySelector('.bpdig');
        badge.textContent = left;
        badge.hidden = left <= 1;
    });
</script>
<script>
    // Поиск предметов по названию (как на странице слотов): неподходящие гаснут.
    (() => {
        const input = document.getElementById('sell-filter');
        const label = document.querySelector('label[for="sell-filter"]');
        const reset = document.querySelector('.sell-grid__search .b-filter__reset');
        const items = document.querySelectorAll('.sell-grid__item[data-title]');

        const toggleLabel = () => { label.style.display = input.value ? 'none' : ''; };

        function applyFilter() {
            const query = input.value.trim().toLowerCase();
            reset.style.display = query ? 'block' : 'none';
            items.forEach((item) => item.classList.toggle('item--filtered', query !== '' && !item.dataset.title.includes(query)));
        }

        input.addEventListener('focus', () => { label.style.display = 'none'; });
        input.addEventListener('blur', toggleLabel);
        input.addEventListener('input', () => { toggleLabel(); applyFilter(); });
        document.querySelector('.sell-grid__search .b-filter__icon').addEventListener('click', () => input.focus());
        reset.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            toggleLabel();
            input.focus();
        });
    })();
</script>
<script src="{{ asset('js/shop-cart-quantity.js') }}?v={{ filemtime(public_path('js/shop-cart-quantity.js')) }}"></script>
</body>
</html>
