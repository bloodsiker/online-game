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
            font-size: 13px;
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
                    <td width="19"><img id="left_1" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="2%" id="tab_1" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_1" href="{{ route('warehouse', ['id' => $warehouse->id]) }}" title="Купить" class="btn_2">Оставить</a>
                    </td>
                    <td width="19"><img id="right_1" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('warehouse.take_item', ['id' => $warehouse->id]) }}" title="Продать" class="btn_1">Забрать</a></td>
                    <td width="19"><img id="right_2" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

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

            <table class="w100" border="0" width="100%">
                <tbody>
                <tr height="5">
                    <td align="left" width="33%" nowrap=""></td>
                </tr>
                </tbody>
            </table>

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" width="33%" nowrap=""><b>Монет:</b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Золотой"><img src="https://fun-dwar.com//images/m_game3.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ number_format($user->money, 0, '', ' ') }} </b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Рубин"><img src="https://fun-dwar.com//images/m_rub.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;676.96 </b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="https://fun-dwar.com//images/m_dmd.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;3.82 </b>
                    </td>
                    <td align="right" nowrap>
                        <b>Вместимость:</b>
                        <b class="red">{{ $countInWarehouse }}/{{ $user->warehouse_count }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>


            <form action="{{ route('warehouse', ['id' => $warehouse->id]) }}" method="post">
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
                            <b class="butt2 pointer" style="margin-bottom: 3px"><b><input value="оставить" type="submit" class="sell-item"></b></b>
                        </td>
                        <td class="brd2-top brd2" align="center">К-во (шт)</td>
                        <td class="brd2-top brd2" align="center"></td>
                        <td class="brd2-top brd2" align="center">Товар</td>
                    </tr>
                    @foreach($putItems as $item)
                        <tr height="17" class="brd2-top brd2 " align="center">
                            <td nowrap="" align="center" style="text-align: center" class="sell-item-checkbox">
                                <label for="checkbox-sell-{{ $item->id }}">
                                    <input type="checkbox" class="input-custom" name="item[{{ $item->item_id }}][selected]" value="1" id="checkbox-sell-{{ $item->id }}" />
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
                                <img src="{{ $item->item->itemInfo->image }}" style="width: 50px; height: 50px" alt="">
                            </td>
                            <td align="left" >
                                <a href="{{ route('items.info', ['id' => $item->item->id]) }}"
                                   style="color:#666666" class="b">{{ $item->item->itemInfo->name }}</a>
                                <br>
                                <span title="Тип предмета">
                                <img src="https://fun-dwar.com/images/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle"> {{ $item->item->itemInfo->getTypeName() }}
                            </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr height="17" class="bg_l">
                        <td class="brd2-top brd2" align="center">
                            <b class="butt2 pointer" style="margin-bottom: 3px"><b><input value="оставить" type="submit" class="sell-item"></b></b>
                        </td>
                        <td class="brd2-top brd2" align="center">К-во (шт)</td>
                        <td class="brd2-top brd2" align="center"></td>
                        <td class="brd2-top brd2" align="center">Товар</td>
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

        if (current.classList.contains('left-disabled') || current.classList.contains('right-disabled')) {
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
        const max = parseInt(input.dataset.max) || Infinity;
        let value = shopCounterValue(input);

        if (current === left) {
            if (value > 1) {
                value--;
            }
        } else if (current === right) {
            if (value < max) {
                value++;
            }
        }

        input.value = value;
        shopChangeCounter(input);
        return true;
    }

    function shopCounterValue(el) {
        const max = parseInt(el.dataset.max) || Infinity;
        let value = parseInt(el.value) || 1;
        return Math.min(Math.max(1, value), max);
    }

    function shopChangeCounter(el) {
        const value = shopCounterValue(el);
        const max = parseInt(el.dataset.max) || Infinity;
        const container = el.closest('.b-input__inner');
        const left = container.querySelector('.left');
        const right = container.querySelector('.right');

        if (value <= 1) {
            left.classList.add('left-disabled');
        } else {
            left.classList.remove('left-disabled');
        }

        if (value >= max) {
            right.classList.add('right-disabled');
        } else {
            right.classList.remove('right-disabled');
        }

        el.value = value;
    }

    // Обработчики событий
    document.querySelectorAll('.cart_amount_sell_input').forEach(input => {
        input.addEventListener('keydown', function (event) {
            shopCounterKeypress(event, this);
        });

        input.addEventListener('input', function () {
            shopChangeCounter(this);
        });

        input.addEventListener('blur', function () {
            this.value = shopCounterValue(this);
            shopChangeCounter(this);
        });
    });

    function shopCounterKeypress(e, el) {
        const key = e.keyCode || e.which;
        const parent = el.closest('.b-input__inner');

        if (key === 38) { // Up Arrow
            shopItemCounter(parent.querySelector('.right'));
        } else if (key === 40) { // Down Arrow
            shopItemCounter(parent.querySelector('.left'));
        }
    }

    // document.removeEventListener('keydown', handleKeydown);

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    document.querySelectorAll('.buy-item').forEach(function(button) {
        button.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;  // Переход по URL
            }
        });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
