<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин — {{ $page->reputation->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 12px; }
        a { color: #000000; }
        a:hover { color: #353434; }
        .grnn, .grnn * { color: #114d01 !important; }
        .redd, .redd * { color: #8a0108 !important; }
        .b { font-weight: bold; }
        .red { color: #a00000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2 { border: 1px solid #DB9F73; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .bg_l2 { background-image: url(/img/bg/info/bg_l2.gif); cursor: pointer; }
        .p10h > tbody > tr > td, .p10h > tr > td { padding-left: 10px; padding-right: 10px; }
        .p4v > tbody > tr > td, .p4v > tr > td { padding-top: 4px; padding-bottom: 4px; }
        .p2v > tbody > tr > td, .p2v > tr > td { padding-top: 2px; padding-bottom: 2px; }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 6px; }
        .msg-error   { color: #a00000; font-weight: bold; padding: 4px 6px; }
        .points-req { color: #7a1c00; font-size: 10px; }
        .req-item { display: inline-block; background: #f5e4c0; border: 1px solid #c8a052; border-radius: 3px; padding: 1px 4px; font-size: 10px; color: #5a3600; margin: 1px; }
        .store-list-item {
            width: 340px; height: 90px; border: 1px solid #DB9F73; border-radius: 5px;
            background-image: url(/img/bg/tbl-usi_bg.gif); background-repeat: repeat;
        }
    </style>

    {!! $page->itemTooltipScript !!}
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body>

@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif';   $btnCenter1 = 'img/bg/btn/btn-cent1.gif';   $btnRight1 = 'img/bg/btn/btn-right1.gif';
    $btnLeft2 = 'img/bg/btn/btn-left2.gif';   $btnCenter2 = 'img/bg/btn/btn-cent2.gif';   $btnRight2 = 'img/bg/btn/btn-right2.gif';
@endphp

{{-- Табы категорий + кнопка возврата в одной строке (кнопка прилипает к правому краю) --}}
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($page->categories as $category)
            @php $active = $page->activeCategoryId === $category->id; @endphp
            <td width="19"><img src="{{ asset($active ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"></td>
            <td width="80" align="center" style="background: url({{ asset($active ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0 2px 6px;">
                <a href="{{ route('reputation.shop', ['id' => $page->reputation->id, 'category_id' => $category->id]) }}" class="{{ $active ? 'btn_2' : 'btn_1' }}">{{ $category->name }}</a>
            </td>
            <td width="19"><img src="{{ asset($active ? $btnRight2 : $btnRight1) }}" width="19" height="21"></td>
        @endforeach

        {{-- пустая ячейка забирает свободное место, толкая кнопку вправо --}}
        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"></td>
        <td width="140" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Вернуться в локацию</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"></td>
    </tr>
    </tbody>
</table>
<br>

{{-- Строка баланса на всю ширину --}}
<table class="coll w100 p10h p2v brd2-all" style="margin-bottom: 8px;">
    <tbody>
    <tr class="bg_l">
        <td nowrap><b>Ваши очки репутации:</b> &nbsp;<b class="redd">{{ $page->pr->points }}</b></td>
        <td align="right" nowrap>
            <b>Монет:</b>
            &nbsp;<b class="redd"><span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->player->user->money) }} </b>
            &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->player->user->diamond) }} </b>
        </td>
    </tr>
    </tbody>
</table>

<table class="coll w100" height="100%">
    <tbody>
    <tr>
        {{-- Витрина товаров --}}
        <td valign="top" height="100%">
            @if($page->message)
                <div class="{{ $page->messageType === 'success' ? 'msg-success' : 'msg-error' }}">{{ $page->message }}</div>
            @endif

            @if($page->items->count())
                @foreach($page->items as $item)
                    @php $locked = $page->pr->points < $item->min_points; @endphp
                    <form method="post" action="{{ route('reputation.add_cart', ['id' => $page->reputation->id]) }}" style="margin: 3px; display: inline-block;">
                        @csrf
                        <input type="hidden" name="shop_item_id" value="{{ $item->id }}">
                        <table border="0" cellspacing="0" cellpadding="0" class="store-list-item {{ $locked ? 'locked' : '' }}">
                            <tbody>
                            <tr>
                                <td align="left" width="60" valign="top">
                                    <div style="margin: 8px; background: url('{{ asset($item->item->image) }}'); background-size: cover; width: 50px; height: 50px;">
                                        <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="position: absolute; z-index:10;">
                                            <tbody>
                                            <tr>
                                                <td data-id="{{ $item->item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">&nbsp;</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                                <td valign="top" style="padding: 5px; width: 100%;">
                                    <table class="w100 coll" border="0">
                                        <tbody>
                                        <tr>
                                            <td colspan="3">
                                                <span class="b" style="color:#ff0000; text-overflow: ellipsis; display: block; overflow: hidden; white-space: nowrap; width: 250px;">{{ $item->item->name }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td nowrap title="Тип предмета">
                                                <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $item->item->getTypeName() }}
                                                @if($item->min_points > 0)
                                                    &nbsp;<span class="points-req">реп. {{ $item->min_points }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="b" title="Цена" style="padding-top: 13px;" valign="top">
                                                @if($item->price)
                                                    <div title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle">&nbsp;{{ format_money($item->price) }}</div>
                                                @endif
                                                @if($item->diamond)
                                                    <div title="Кристаллов"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle">&nbsp;{{ format_money($item->diamond) }}</div>
                                                @endif
                                                @if($item->requirements->count())
                                                    <div style="padding-top: 3px;">
                                                        @foreach($item->requirements as $req)
                                                            <span class="req-item">{{ $req->item->name ?? '?' }} ×{{ $req->quantity }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td align="center" valign="top" style="padding-top: 16px; width: 60px;">
                                                @unless($locked)
                                                    <div class="cart-amount-sell-price">
                                                        <span class="cart-amount-input-cont">
                                                            <span class="b-input">
                                                                <span class="b-input__inner">
                                                                    <span class="arrow left left-disabled" onclick="shopItemCounter(this);" title="Уменьшить кол-во"></span>
                                                                    <span class="arrow right" onclick="shopItemCounter(this);" title="Увеличить кол-во"></span>
                                                                    <input type="text" name="quantity" data-id="1" value="1" class="cart_amount_sell_input count_buy" autocomplete="off">
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                @endunless
                                            </td>
                                            <td align="center" valign="top" style="padding-top: 16px; width: 80px;">
                                                @if($locked)
                                                    <span style="color:#8a0108; font-size:10px;">Нужно {{ $item->min_points }} реп.</span>
                                                @else
                                                    <b class="butt2 pointer"><b>
                                                        <input value="В корзину" type="submit" onclick="if(document._submit)return false;document._submit=true;" style="width:63px">
                                                    </b></b>
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                @endforeach
            @else
                <div align="center" style="color: #49382D"><b>В этой категории товаров нет!</b></div>
            @endif
        </td>

        <td width="10"></td>

        {{-- Корзина (в рамке, справа, как в премиуме) --}}
        <td valign="top" width="35%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr height="22">
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                <td align="center" style="background-image: url(/img/bg/info/tbl-usi_label-center.gif); background-repeat: repeat-x; height: 19px; font-weight: bold; font-size: 11px; color: #FCF5B7; padding: 0 10px 3px;">Ваша корзина</td>
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                            </tr>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 4px">
                        <form method="post" action="{{ route('reputation.buy', ['id' => $page->reputation->id]) }}">
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
                                            <td><span class="redd b">{{ $cartItem->shopItem->item->name }}</span></td>
                                            <td class="b red" align="center">
                                                @if($cartItem->shopItem->price)
                                                    <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($cartItem->shopItem->price * $cartItem->quantity) }}<br>
                                                @endif
                                                @if($cartItem->shopItem->diamond)
                                                    <span title="Кристаллов"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($cartItem->shopItem->diamond * $cartItem->quantity) }}<br>
                                                @endif
                                            </td>
                                            <td nowrap>{{ $cartItem->quantity }} шт</td>
                                            <td>
                                                <a href="{{ route('reputation.delete_cart', ['id' => $page->reputation->id, 'cartId' => $cartItem->id]) }}" title="Удалить">
                                                    <img src="{{ asset('img/icon/tbl-shp_x.gif') }}" alt="delete" width="11" height="13" border="0">
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <b>Корзина пуста!</b><br>
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
                                            @if($page->cart->getTotalPrice())
                                                <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->cart->getTotalPrice()) }}<br>
                                            @endif
                                            @if($page->cart->getTotalDiamond())
                                                <span title="Кристаллов"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->cart->getTotalDiamond()) }}<br>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt"><b>У Вас денег:</b></td>
                                    <td class="brd2-top brd2-bt b red">
                                        <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->player->user->money) }}<br>
                                        <span title="Кристаллов"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($page->player->user->diamond) }}<br>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <br>
                            @if($page->cart->getItems()->count())
                                <span class="butt1 pointer"><span>
                                    <input value="Оплатить товар" type="submit" onclick="if(document._submit)return false;document._submit=true;" class="grnn">
                                </span></span>
                                <br>
                                <span class="butt1 pointer"><span>
                                    <input value="Очистить корзину" type="button" onclick="location.href='{{ route('reputation.clear_cart', ['id' => $page->reputation->id]) }}';" class="redd">
                                </span></span>
                                <br>
                            @endif
                        </form>

                        <br>
                        <div class="p10h p2v brd2-all bg_l" align="left" style="padding: 6px 10px;">
                            Товары в магазине доступны за очки репутации «{{ $page->reputation->name }}». Набирайте репутацию, выполняя задания, чтобы открыть новые предметы.
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
        if (current.classList.contains('left-disabled')) return false;
        let left = current, right = current;
        if (current.classList.contains('left')) right = current.nextElementSibling;
        else if (current.classList.contains('right')) left = current.previousElementSibling;
        const input = current.parentElement.querySelector('input');
        if (current === left) {
            const value = shopCounterValue(input);
            if (value > 1) { input.value = value - 1; shopChangeCounter(input); }
        } else if (current === right) {
            input.value = shopCounterValue(input) + 1; shopChangeCounter(input);
        }
        return true;
    }
    function shopCounterValue(el) { return Math.max(1, parseInt(el.value) || 1); }
    function shopChangeCounter(el) {
        const value = shopCounterValue(el);
        const left = el.closest('.b-input__inner').querySelector('.left');
        if (value <= 1) left.classList.add('left-disabled'); else left.classList.remove('left-disabled');
        el.value = value;
    }

    @if($page->messageType === 'success' && $page->message)
        try {
            let money = parseInt('{{ $page->player->user->money }}');
            let diamond = parseInt('{{ $page->player->user->diamond }}');
            let experience = parseFloat('{{ $page->player->getPercentExp() }}');
            let lvl = parseInt('{{ $page->player->lvl }}');
            let hp = parseFloat('{{ $page->player->getPercentHp() }}');
            let mp = parseFloat('{{ $page->player->getPercentMp() }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
