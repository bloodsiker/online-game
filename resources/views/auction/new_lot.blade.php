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

        .dbgl2 {
            background-color: #FFFBD6;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
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

        .c-s-n {
            background: url({{ asset('img/bg/table/c-top-n-s.gif') }}) left top repeat-x;
        }
        .c-s-n-left {
            background: url({{ asset('img/bg/table/c-left-n-s.gif') }}) left top repeat-y;
        }
        .c-s-n-fon {
            background: url({{ asset('img/bg/table/c-fon-n-s.gif') }}) left top repeat;
        }
        .c-s-n-right {
            background: url({{ asset('img/bg/table/c-right-n-s.gif') }}) left top repeat-y;
        }
        .c-s-n-bottom {
            background: url({{ asset('img/bg/table/c-bottom-n-s.gif') }}) left top repeat-x;
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
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
                    <td width="19"><img id="left_1" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="100" id="tab_1" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_1" href="{{ route('auction', ['id' => $auction->id]) }}" title="Купить товар" class="btn_1">Купить товар</a>
                    </td>
                    <td width="19"><img id="right_1" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="80" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('auction.my_lot', ['id' => $auction->id]) }}" title="Мои лоты" class="btn_1">Мои лоты</a></td>
                    <td width="19"><img id="right_2" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="80" id="tab_2" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('auction.new_lot', ['id' => $auction->id]) }}" title="Новый лот" class="btn_2">Новый лот</a></td>
                    <td width="19"><img id="right_2" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

                    <td></td>

                    <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_4" href="{{ route('location') }}" title="Подаренные Вам подарки" class="btn_1">Выход</a></td>
                    <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10 6 10 6">

            <table cellspacing="0" cellpadding="0" border="0" align="center" class="coll w100 h100">
                <tbody>
                <tr>
                    <td align="left" valign="top" width="50%">
                        <table align="center" class="coll" cellpadding="0" cellspacing="0" style="margin:3px auto; width: 100%; ">
                            <tbody>
                            <tr>
                                <td width="12" height="10"><img alt="" src="{{ asset('img/bg/table/c-lt-n-s.gif') }}"></td>
                                <td class="c-s-n" height="10"></td>
                                <td width="10" height="10"><img alt="" src="{{ asset('img/bg/table/c-rt-n-s.gif') }}"></td>
                            </tr>
                            <tr>
                                <td class="c-s-n-left" width="12"></td>
                                <td class="c-s-n-fon" height="10">

                                    @php
                                        $cost = $selectedItem ? $selectedItem->item->itemInfo->price * $selectedItem->count : 0;
                                    @endphp

                                    <script type="text/javascript">
                                        function log10(x) {
                                            return Math.LOG10E * Math.log(x);
                                        }

                                        function recalculate() {
                                            let customPrice = document.getElementById('price').value;
                                            let res = Math.pow(0.5, log10(customPrice) + 2) * customPrice;
                                            res = Math.ceil(res);
                                            res = res.toFixed(0);
                                            res = (isNaN(res) || res <= 0) ? 0 : res;
                                            document.getElementById('commission').innerHTML = (res * 1.0).toFixed(0) + '&nbsp;<img src="https://fun-dwar.com//images/m_game2.gif">';
                                        }

                                        document.addEventListener('DOMContentLoaded', function () {
                                            let availableAmount = document.getElementById('amount');

                                            if (availableAmount) {
                                                availableAmount.addEventListener('input', function () {
                                                    let maxAmount = parseInt(this.getAttribute('data-max-amount'), 10);
                                                    let amount = parseInt(this.value, 10);

                                                    if (amount > maxAmount) {
                                                        this.value = maxAmount;
                                                    }

                                                    if (amount < 1 || isNaN(amount)) {
                                                        this.value = 1;
                                                    }
                                                });
                                            }
                                        });
                                    </script>

                                    <form id="new_message" method="post" action="{{ route('auction.new_lot.save', ['id' => $auction->id]) }}">
                                        @csrf
                                        <input id="artifact_id" type="hidden" name="form[item_id]" value="{{ $selectedItem ? $selectedItem->item->id : null }}">
                                        <table class="coll w100" border="0" style="margin-top:6px">
                                            <tbody><tr>
                                                <td class="p6h" width="60" valign="top">
                                                    @if($selectedItem)
                                                        <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url({{ asset($selectedItem->item->itemInfo->image) }}); background-size: 50px 50px;">
                                                            <tbody>
                                                                <tr><td valign="bottom">&nbsp;</td></tr>
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url({{ asset('img/bg/empty_slot.gif') }}); background-size: 50px 50px;">
                                                            <tbody>
                                                                <tr><td valign="bottom">&nbsp;</td></tr>
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </td>
                                                <td valign="top" class="b" height="60" width="100%">
                                                    <table height="60" class="coll w100 p6h brd2-all" border="0">
                                                        <colgroup>
                                                            <col width="100%">
                                                        </colgroup>
                                                        <tbody>
                                                        <tr class="">
                                                            <td height="28" class="brd2-top brd2 b">Цена:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" nowrap="" style="padding-right: 11px;">
                                                                <input type="text" id="price"
                                                                       name="form[price]" value="{{ $selectedItem ? $cost : 0 }}"
                                                                       style="height:17px;width:75px;text-align:right;"
                                                                       maxlength="9" class="dbgl2 brd b small"
                                                                       oninput="recalculate()"
                                                                       onkeyup="setTimeout('recalculate()', 10)">&nbsp;
                                                                <img src="https://fun-dwar.com//images/m_game2.gif">
                                                            </td>
                                                        </tr>
                                                        <tr class="bg_l">
                                                            <td height="28" class="brd2-top brd2 b">Кол-во:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" nowrap="">
                                                                <input type="text" id="amount"
                                                                       name="form[amount]" value="{{ $selectedItem ? $selectedItem->count : 0 }}"
                                                                       style="height:17px;width:75px;text-align:right"
                                                                       oninput="recalculate()"
                                                                       onkeyup="setTimeout('recalculate()', 10)"
                                                                       data-max-amount="{{ $selectedItem ? $selectedItem->count : 0 }}" class="dbgl2 brd b small">
                                                                <span>шт.</span>
                                                            </td>
                                                        </tr>
                                                        <tr class="">
                                                            <td height="28" class="brd2-top brd2 b">Налог:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right">
                                                                <span id="commission">
                                                                    @if($cost)
                                                                        {{ ceil($cost * 2 / 100) }}&nbsp;<img src="https://fun-dwar.com//images/m_game2.gif">
                                                                    @endif
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr class="bg_l">
                                                            <td height="28" class="brd2-top brd2 b">Продать анонимно:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="left" nowrap="">
                                                                <input type="hidden" name="form[is_anonymous]" value="">
                                                                <input type="checkbox" name="form[is_anonymous]" value="1" title="Функция доступна для премиума от  уровня." id="_chk_0">&nbsp;<label for="_chk_0"></label><br>
                                                            </td>
                                                        </tr>
                                                        <tr class="">
                                                            <td height="28" class="brd2-top brd2 b">Выделить заявку: Цена(<span title="Рубин"><img src="https://fun-dwar.com//images/m_rub.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;1.00)</td>
                                                            <td height="28" class="brd2-top brd2 b" align="left" nowrap=""><input type="hidden" name="form[super]" value="">
                                                                <input type="checkbox" name="form[super]" value="1" id="super" onchange="recalculate();" title="Позиция таких заявок выше остальных.">&nbsp;<label for="_chk_1"></label><br>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <br>
                                                    <span class="butt1 pointer"><span>
                                                            <input value="Выставить" type="submit" onclick="if(document._submit)return false;document._submit=true;">
                                                        </span>
                                                    </span>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </form>

                                    <script type="text/javascript">
                                        recalculate();
                                    </script>

                                    <br>При создании лота с Вас взимается налог, который зависит от гос. стоимости вещи.
                                    <br>При удачной продаже лота, с Вас будет взыскан налог в размере 1% от суммы сделки, о чем Вы получите подробный отчет по почте.
                                    <br>Сумма налога которую Вы заплатите при удачном окончании торгов всегда можно увидеть в разделе "Мои лоты" под суммой текущей ставки и ценой выкупа.
                                    <br>Внимание! В случае если вещь не была куплена, налог за создание лота не возвращается. Во избежание наказаний, внимательно ознакомьтесь с основаниями для наложения проклятий.
                                    <br>Деньги, а так же уведомления о сумме налога за проданные Вами вещи, и вещи, которые не были куплены за время торгов, а также перебитые ставки отправляются Вам почтой.
                                </td>
                                <td class="c-s-n-right" width="10s"></td>
                            </tr>
                            <tr>
                                <td width="12" height="10"><img alt="" src="{{ asset('img/bg/table/c-lb-n-s.gif') }}"></td>
                                <td class="c-s-n-bottom" height="10"></td>
                                <td width="10" height="10"><img alt="" src="{{ asset('img/bg/table/c-rb-n-s.gif') }}"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td align="left" valign="top" width="50%">
                        <table align="center" class="coll" cellpadding="0" cellspacing="0" style="margin:3px auto; width: 100%; ">
                            <tbody>
                            <tr>
                                <td width="12" height="10"><img alt="" src="{{ asset('img/bg/table/c-lt-n-s.gif') }}"></td>
                                <td class="c-s-n" height="10"></td>
                                <td width="10" height="10"><img alt="" src="{{ asset('img/bg/table/c-rt-n-s.gif') }}"></td>
                            </tr>
                            <tr>
                                <td class="c-s-n-left" width="12"></td>
                                <td class="c-s-n-fon" height="10">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                        <tr>
                                            <td height="20" valign="top">
                                                <table class="coll w100 p10h p2v brd2-all">
                                                    <tbody>
                                                    <tr class="bg_l">
                                                        <td nowrap=""><b>Вместимость:</b> <b class="redd">7/20</b></td>
                                                        <td align="right" nowrap="">
                                                            <b>Деньги:</b>
                                                            &nbsp;<b class="redd"><span title="Серебряный"><img src="https://fun-dwar.com//images/m_game2.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ number_format($user->money, 0, '', ' ') }} </b>
                                                            &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Рубин"><img src="https://fun-dwar.com//images/m_rub.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;676.96 </b>
                                                            &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="https://fun-dwar.com//images/m_dmd.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;3.82 </b>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" style="padding-bottom:20px;">
                                                <br>
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td height="1%">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div id="item_list" class="backpack_overflow lscroll">
                                                                <table class="coll w100 brd2-all" border="0" id="item_list">
                                                                    <colgroup>
                                                                        <col width="50">
                                                                        <col class="p6h">
                                                                        <col class="p6h" align="center" width="70">
                                                                        <col class="p6h" align="center" width="90">
                                                                    </colgroup>
                                                                    <tbody>
                                                                    <tr height="17" class="bg_l">
                                                                        <td class="brd2-top brd2" align="center"></td>
                                                                        <td class="brd2-top brd2" align="center">Название</td>
                                                                        <td class="brd2-top brd2" align="center">Кол-во</td>
                                                                        <td class="brd2-top brd2" align="center"></td>
                                                                    </tr>
                                                                    @foreach($itemsToSell as $slot)
                                                                        <tr height="17" class="brd2-top brd2 {{ $loop->iteration % 2 == 0 ? 'bg_l' : '' }}" align="center">
                                                                            <td class="brd2-top brd2" style="padding: 0" width="50" height="50">
                                                                                <img src="{{ $slot->item->itemInfo->image }}" style="width: 100%" alt="">
                                                                            </td>
                                                                            <td align="left">
                                                                                <a href="{{ route('items.info', ['id' => $slot->item->id]) }}"
                                                                                   onclick="showArtifactInfo(22195638,false);return false;"
                                                                                   style="color:#666666" class="b">{{ $slot->item->itemInfo->name }}</a><br>
                                                                                <span title="Тип предмета">
                                                                                    <img src="https://fun-dwar.com/images/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle"> {{ $slot->item->itemInfo->getTypeName() }}
                                                                                </span>
                                                                            </td>
                                                                            <td nowrap="">{{ $slot->count }}</td>
                                                                            <td nowrap="">
                                                                                <b class="butt2 pointer"><b><input value="добавить" type="submit" class="select-item" data-id="{{ $slot->item->id }}" data-href="{{ route('auction.new_lot', ['id' => $auction->id, 'iid' => $slot->item->id]) }}"></b></b>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </td>
                                <td class="c-s-n-right" width="10"></td>
                            </tr>
                            <tr>
                                <td width="12" height="10"><img alt="" src="{{ asset('img/bg/table/c-lb-n-s.gif') }}"></td>
                                <td class="c-s-n-bottom" height="10"></td>
                                <td width="10" height="10"><img alt="" src="{{ asset('img/bg/table/c-rb-n-s.gif') }}"></td>
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

<script>
    function handleKeydown(event) {
        switch (event.key.toLowerCase()) {
            case 'i':
                sendDataToGame('{{ route('backpack') }}');
                break;
            case 'c':
                sendDataToGame('{{ route('character') }}');
                break;
            case ' ':
                sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    }

    document.addEventListener('keydown', handleKeydown);

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

    // document.removeEventListener('keydown', handleKeydown);

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    document.querySelectorAll('.select-item').forEach(function(button) {
        button.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
