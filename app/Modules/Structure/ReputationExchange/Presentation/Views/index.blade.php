<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .regcolor, .regcolor * { color: #955c4a; }
        .redd, .redd * { color: #ba0000 !important; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
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
        .relic-img { width: 50px; height: 50px; object-fit: contain; background: rgba(0,0,0,.05); }
        .relic-badge { font-weight: 700; padding: 1px 5px; border-radius: 3px; color: #fff; font-size: 10px; }
        .relic-badge.active { background: #489200; }
        .relic-badge.inactive { background: #999; }
        .cart-amount-sell-price input { width: 40px; padding: 0 4px; text-align: center; }
        .pointer, .pointer input { cursor: pointer; }
        .relic-name { color: #006699; font-weight: 700; cursor: pointer; text-decoration: underline; }
        .relic-name:hover { text-decoration: underline; }
        .cart-amount-sell-price.disabled,
        .relic-exchange-btn.disabled { opacity: .45; }
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
<body leftmargin="0" rightmargin="0">
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0;top: 0"></div>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
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
        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="120" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('reputation_exchange', ['id' => $page->structureId]) }}" class="btn_2">Обмен реликтов</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Выход</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left"></td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10 6 10 6">

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" width="50%" nowrap="">
                        <b>Репутация «{{ $page->reputationName }}»:</b>
                        <b class="redd">{{ number_format($page->currentPoints, 0, '', ' ') }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                @foreach($page->items as $item)
                    @php
                        $canExchange = $item->isCurrentBracket && $item->availableCount > 0;
                        $maxCount = max(1, $item->availableCount);
                    @endphp
                    <tr class="relic-row">
                        <td colspan="3">
                            <table class="coll w100 p10h p6v brd2-all">
                                <tbody>
                                <tr class="bg_l">
                                    <td width="60">
                                        <img src="{{ asset($item->image) }}" class="relic-img" alt="{{ $item->name }}"
                                             data-id="{{ $item->shareItemId }}"
                                             onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                             onclick="window.open('{{ route('items.info.share', ['id' => $item->shareItemId]) }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">
                                    </td>
                                    <td>
                                        <div style="margin-bottom: 3px;">
                                            <a href="{{ route('items.info.share', ['id' => $item->shareItemId]) }}" class="relic-name"
                                               style="color: {{ $item->rarityColor }}"
                                               data-id="{{ $item->shareItemId }}"
                                               onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                               onclick="window.open(this.href, '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $item->name }}</a>
                                        </div>
                                        <div class="text-muted" style="margin-bottom: 3px;">Репутация {{ $item->minReputation }}–{{ $item->maxReputation }}</div>
                                        <div>
                                            <span class="relic-badge {{ $item->isCurrentBracket ? 'active' : 'inactive' }}">
                                                {{ $item->isCurrentBracket ? 'принимается сейчас' : 'не подходит для текущей репутации' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td align="center" width="90">
                                        <div>За 1 шт.: <b>+{{ $item->points }}</b> реп.</div>
                                        <div>У вас: <b class="redd">{{ $item->availableCount }}</b> шт.</div>
                                    </td>
                                    <td align="center" width="110">
                                        <div class="cart-amount-sell-price {{ $canExchange ? '' : 'disabled' }}">
                                            <span class="cart-amount-input-cont">
                                                <span class="b-input">
                                                    <span class="b-input__inner">
                                                        <span class="arrow left left-disabled" onclick="relicCounter(this);" title="Уменьшить кол-во"></span>
                                                        <span class="arrow right {{ $maxCount <= 1 ? 'right-disabled' : '' }}" onclick="relicCounter(this);" title="Увеличить кол-во"></span>
                                                        <input type="text" data-points="{{ $item->points }}" data-min="1" data-max="{{ $maxCount }}"
                                                               value="1" class="cart_amount_sell_input relic_count_input" autocomplete="off"
                                                               form="relic-form-{{ $item->shareItemId }}" name="count" {{ $canExchange ? '' : 'disabled' }}>
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                        <div>= <b class="redd relic_total">{{ $item->points }}</b> реп.</div>
                                    </td>
                                    <td align="right" width="80">
                                        <form id="relic-form-{{ $item->shareItemId }}" action="{{ route('reputation_exchange.apply', ['id' => $page->structureId]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="share_item_id" value="{{ $item->shareItemId }}">
                                            <span class="butt1 pointer relic-exchange-btn {{ $canExchange ? '' : 'disabled' }}">
                                                <span><input value="Обменять" type="submit" {{ $canExchange ? '' : 'disabled' }}></span>
                                            </span>
                                        </form>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr><td colspan="3"><img src="{{ asset('img/bg/blank.gif') }}" height="5" alt=""></td></tr>
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

<script>
    function relicCounterValue(el) {
        const min = parseInt(el.dataset.min) || 1;
        const max = parseInt(el.dataset.max) || min;

        let value = parseInt(el.value);
        if (isNaN(value)) value = min;

        return Math.min(Math.max(value, min), max);
    }

    function relicChangeCounter(el) {
        const value = relicCounterValue(el);
        const min = parseInt(el.dataset.min) || 1;
        const max = parseInt(el.dataset.max) || min;

        const container = el.closest('.b-input__inner');
        const left = container.querySelector('.left');
        const right = container.querySelector('.right');

        left.classList.toggle('left-disabled', value <= min);
        right.classList.toggle('right-disabled', value >= max);

        el.value = value;
    }

    function recalcRelicRow(input) {
        const row = input.closest('tr');
        const totalEl = row.querySelector('.relic_total');
        const points = parseInt(input.dataset.points) || 0;
        const count = relicCounterValue(input);

        totalEl.textContent = points * count;
    }

    function relicCounter(el) {
        if (el.classList.contains('left-disabled') || el.classList.contains('right-disabled')) {
            return false;
        }

        const container = el.closest('.b-input__inner');
        const input = container.querySelector('input');
        let value = relicCounterValue(input);

        if (el.classList.contains('left') && value > (parseInt(input.dataset.min) || 1)) {
            value--;
        }
        if (el.classList.contains('right') && value < (parseInt(input.dataset.max) || value)) {
            value++;
        }

        input.value = value;
        relicChangeCounter(input);
        recalcRelicRow(input);
    }

    document.querySelectorAll('.relic_count_input').forEach(function (input) {
        relicChangeCounter(input);
        recalcRelicRow(input);

        input.addEventListener('input', function () {
            relicChangeCounter(this);
            recalcRelicRow(this);
        });

        input.addEventListener('blur', function () {
            this.value = relicCounterValue(this);
            relicChangeCounter(this);
            recalcRelicRow(this);
        });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
