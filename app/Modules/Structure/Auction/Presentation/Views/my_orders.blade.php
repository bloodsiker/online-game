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
        a, a:link, a:visited, a:active {
            text-decoration: none;
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

@include('auction::_building_switcher')

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('auction::_tabs', ['activeTab' => 'my_orders'])
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
                    <td align="left" nowrap=""><b>Монет:</b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }} </b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }} </b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            <table class="coll w100 brd2-all" border="0">
                <colgroup>
                    <col width="50">
                    <col class="p6h">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="90">
                    <col class="p6h" align="center" width="100">
                    <col class="p6h" align="center" width="80">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Осталось</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Эскроу</td>
                    <td class="brd2-top brd2" align="center">Действие</td>
                </tr>
                @forelse($orders as $order)
                    <tr height="17" class="brd2-top brd2 {{ $loop->iteration % 2 == 0 ? 'bg_l' : '' }}" align="center">
                        <td class="brd2-top brd2" style="padding: 0" width="50" height="50">
                            @if($order->shareItem->image)
                                <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="margin: 1px; background: url({{ asset($order->shareItem->image) }}); background-size: 50px 50px;">
                                    <tbody><tr><td valign="bottom">&nbsp;</td></tr></tbody>
                                </table>
                            @endif
                        </td>
                        <td align="left">
                            <b class="b">{{ $order->shareItem->name }}</b><br>
                            <span title="Тип предмета">
                                <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $order->shareItem->getTypeName() }}
                            </span>
                        </td>
                        <td nowrap=""><b>{{ $order->count }} шт.</b></td>
                        <td nowrap="">
                            <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ format_money($order->price) }}
                        </td>
                        <td nowrap="">
                            <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ format_money($order->count * $order->price) }}
                        </td>
                        <td nowrap="">
                            <b class="butt2 pointer"><b><input value="отменить" type="submit" class="cancel-btn" data-href="{{ route('auction.order.cancel', ['id' => $auction->id, 'orderId' => $order->id]) }}"></b></b>
                        </td>
                    </tr>
                @empty
                    <tr height="17" class="brd2-top brd2 bg_l">
                        <td colspan="6" align="center">У вас нет активных заявок. <a href="{{ route('auction.new_order', ['id' => $auction->id]) }}" class="b" style="color:#461c0b">Создать заявку</a></td>
                    </tr>
                @endforelse
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Осталось</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Эскроу</td>
                    <td class="brd2-top brd2" align="center">Действие</td>
                </tr>
                </tbody>
            </table>

            <br>
            <small>При отмене заявки эскроу-монеты возвращаются. Комиссия 100 монет за создание заявки не возвращается.</small>
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


    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    document.querySelectorAll('.cancel-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const href = this.getAttribute('data-href');
            if (href) submitAuctionAction(href);
        });
    });

    function submitAuctionAction(url) { const form = document.createElement('form'); form.method = 'POST'; form.action = url; form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'; document.body.appendChild(form); form.submit(); }

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
