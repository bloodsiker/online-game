<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хранилище клана</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 12px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .regblk, .regblk * { color: #49382d; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
    </style>

    {!! $playerStatsScript !!}
    {!! $itemTooltipScript !!}

    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('clan.warehouse._tabs', ['activeTab' => 'put'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px">

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" nowrap="">
                        <b>Клан:</b> {{ $clan->name }}
                    </td>
                    <td align="right" nowrap="">
                        <b>Вместимость хранилища:</b>
                        <b class="redd">{{ $countInWarehouse }}/{{ $clan->warehouse_capacity }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            <form action="{{ route('clan.warehouse', ['id' => $clanWarehouse->id]) }}" method="post">
                {{ csrf_field() }}
                <table class="coll w100 brd2-all" border="0" id="item_list">
                    <colgroup>
                        <col width="70">
                        <col width="80">
                        <col width="50">
                        <col class="p6h">
                    </colgroup>
                    <tbody>
                    <tr height="17" class="bg_l">
                        <td class="brd2-top brd2" align="center">
                            <b class="butt2 pointer" style="margin-bottom: 3px"><b><input value="положить" type="submit" class="sell-item"></b></b>
                        </td>
                        <td class="brd2-top brd2" align="center">К-во (шт)</td>
                        <td class="brd2-top brd2" align="center"></td>
                        <td class="brd2-top brd2" align="center">Предмет</td>
                    </tr>
                    @forelse($backpackItems as $item)
                        <tr height="17" class="brd2-top brd2" align="center">
                            <td nowrap="" align="center" class="sell-item-checkbox">
                                <label for="chk-{{ $item->id }}">
                                    <input type="checkbox" class="input-custom" name="item[{{ $item->item_id }}][selected]" value="1" id="chk-{{ $item->id }}">
                                    <span class="custom-checkbox"></span>
                                </label>
                            </td>
                            <td nowrap="">
                                <div class="cart-amount-sell-price">
                                    <span class="cart-amount-input-cont">
                                        <span class="b-input">
                                            <span class="b-input__inner">
                                                <span class="arrow left @if($item->count === 1) left-disabled @endif" onclick="shopItemCounter(this);" title="Уменьшить кол-во"></span>
                                                <span class="arrow right right-disabled" onclick="shopItemCounter(this);" title="Увеличить кол-во"></span>
                                                <input type="text" data-id="{{ $item->id }}" data-max="{{ $item->count }}" name="item[{{ $item->item_id }}][count]" value="{{ $item->count }}" class="cart_amount_sell_input count_sell" autocomplete="off">
                                            </span>
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="brd2-top brd2" style="padding: 0" width="50" height="50">
                                <div style="background: url('{{ $item->item->itemInfo->image }}'); background-size: cover; width: 50px; height: 50px; position: relative;">
                                    <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="position: absolute; z-index: 10;">
                                        <tbody>
                                        <tr>
                                            <td data-id="{{ $item->item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                &nbsp;
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                            <td align="left">
                                <a href="{{ route('items.info', ['id' => $item->item->id]) }}" style="color:#666666" class="b">{{ $item->item->itemInfo->name }}</a>
                                <br>
                                <span title="Тип предмета">
                                    <img src="https://fun-dwar.com/images/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle"> {{ $item->item->itemInfo->getTypeName() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" align="center" style="padding: 10px;">Нет предметов в рюкзаке</td>
                        </tr>
                    @endforelse
                    <tr height="17" class="bg_l">
                        <td class="brd2-top brd2" align="center">
                            <b class="butt2 pointer" style="margin-bottom: 3px"><b><input value="положить" type="submit" class="sell-item"></b></b>
                        </td>
                        <td class="brd2-top brd2" align="center">К-во (шт)</td>
                        <td class="brd2-top brd2" align="center"></td>
                        <td class="brd2-top brd2" align="center">Предмет</td>
                    </tr>
                    </tbody>
                </table>
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

<script>
    function shopItemCounter(el) {
        if (el.classList.contains('left-disabled') || el.classList.contains('right-disabled')) return false;
        let left = el, right = el;
        if (el.classList.contains('left')) right = el.nextElementSibling;
        else left = el.previousElementSibling;
        const input = el.parentElement.querySelector('input');
        const max = parseInt(input.dataset.max) || Infinity;
        let value = shopCounterValue(input);
        if (el === left) { if (value > 1) value--; }
        else { if (value < max) value++; }
        input.value = value;
        shopChangeCounter(input);
        return true;
    }

    function shopCounterValue(el) {
        return Math.min(Math.max(1, parseInt(el.value) || 1), parseInt(el.dataset.max) || Infinity);
    }

    function shopChangeCounter(el) {
        const value = shopCounterValue(el);
        const max = parseInt(el.dataset.max) || Infinity;
        const container = el.closest('.b-input__inner');
        container.querySelector('.left').classList.toggle('left-disabled', value <= 1);
        container.querySelector('.right').classList.toggle('right-disabled', value >= max);
        el.value = value;
    }

    document.querySelectorAll('.cart_amount_sell_input').forEach(input => {
        input.addEventListener('input', function() { shopChangeCounter(this); });
        input.addEventListener('blur', function() { this.value = shopCounterValue(this); shopChangeCounter(this); });
    });


    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
