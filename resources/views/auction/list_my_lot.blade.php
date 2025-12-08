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
        a, a:link, a:visited, a:active {
            text-decoration: none;
        }
        .b {
            font-weight: 700;
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
        .bpdig {
            border: solid 1px #6f4a24 !important;
            background-color: #6e534c !important;
            width: 32px !important;
            height: 12px !important;
            color: #f6d9a6 !important;
            font-weight: bold !important;
            margin: 2px !important;
            text-align: center !important;
            font-size: 10px;
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

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="80" id="tab_2" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('auction.my_lot', ['id' => $auction->id]) }}" title="Мои лоты" class="btn_2">Мои лоты</a></td>
                    <td width="19"><img id="right_2" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="80" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_3" href="{{ route('auction.new_lot', ['id' => $auction->id]) }}" title="Новый лот" class="btn_1">Новый лот</a></td>
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
                </tr>
                </tbody>
            </table>

            <br>

            <table class="coll w100 brd2-all" border="0" id="item_list">
                <colgroup>
                    <col width="50">
                    <col class="p6h">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="110">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Кол-во</td>
                    <td class="brd2-top brd2" align="center">Цена</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Действия</td>
                </tr>
                @foreach($auctionSlots as $slot)
                    <tr height="17" class="brd2-top brd2 {{ $loop->iteration % 2 == 0 ? 'bg_l' : '' }}" align="center">
                        <td class="brd2-top brd2" style="padding: 0" width="50" height="50">
                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="margin: 1px; background: url({{ asset($slot->item->itemInfo->image) }}); background-size: 50px 50px;">
                                <tbody>
                                <tr>
                                    <td
                                        onmouseover="artifactAlt(this,event,2)"
                                        onmouseout="artifactAlt(this,event,0)"
                                        valign="bottom"
                                        style="background-image: url({{ asset('img/icon.d.gif') }}); cursor: pointer;">
                                        @if($slot->count > 1)
                                            <div class="bpdig">{{ $slot->count }}</div>
                                        @endif
                                    </td>
                                </tr>
                                </tbody>
                            </table>
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
                            <span title="Золотой"><img src="https://fun-dwar.com//images/m_game3.gif" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ number_format($slot->price, 0, ',', ' ') }}
                        </td>
                        <td nowrap="">
                            <span title="Золотой"><img src="https://fun-dwar.com//images/m_game3.gif" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ number_format($slot->price / $slot->count , 0, ',', ' ') }}
                        </td>
                        <td nowrap="">
                            <b class="butt2 pointer"><b><input value="изменить" type="submit" class="action-item" data-id="{{ $slot->id }}" data-href="{{ route('auction.my_lot.edit', ['id' => $auction->id, 'slotId' => $slot->id]) }}"></b></b>
                            <br>
                            <b class="butt2 pointer"><b><input value="отменить" type="submit" class="action-item" data-id="{{ $slot->id }}" data-href="{{ route('auction.my_lot.cancel', ['id' => $auction->id, 'slotId' => $slot->id]) }}"></b></b>
                        </td>
                    </tr>
                @endforeach
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Кол-во</td>
                    <td class="brd2-top brd2" align="center">Цена</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Действия</td>
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

    document.querySelectorAll('.action-item').forEach(function(button) {
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
