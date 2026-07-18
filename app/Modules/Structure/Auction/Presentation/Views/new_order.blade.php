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
        .dbgl2 {
            background-color: #FFFBD6;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
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
            @include('auction::_tabs', ['activeTab' => 'new_order'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10 6 10 6">

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%" style="margin-bottom:8px">
                <tbody>
                <tr class="bg_l">
                    <td align="left" nowrap="">
                        <b>Монет:</b>
                        &nbsp;&nbsp;&nbsp;<b class="redd">
                            <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            &nbsp;{{ format_money($user->money) }}
                        </b>
                    </td>
                    <td align="right" nowrap="" style="color:#888;">
                        Стоимость создания заявки: <b>100</b> монет + эскроу (цена&nbsp;×&nbsp;кол-во)
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellspacing="0" cellpadding="0" border="0" align="center" class="coll w100 h100">
                <tbody>
                <tr>
                    <td align="left" valign="top" width="50%">
                        <table align="center" class="coll" cellpadding="0" cellspacing="0" style="margin:3px auto; width: 100%;">
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
                                        $defaultPrice = $selectedItem ? $selectedItem->price : 0;
                                    @endphp

                                    <script type="text/javascript">
                                        function updateTotal() {
                                            const price = parseInt(document.getElementById('price').value) || 0;
                                            const count = parseInt(document.getElementById('count').value) || 0;
                                            const escrow = price * count;
                                            const total = 100 + escrow;
                                            document.getElementById('escrow_display').innerHTML = escrow;
                                            document.getElementById('total_display').innerHTML = total;
                                        }

                                        document.addEventListener('DOMContentLoaded', function () {
                                            updateTotal();
                                        });
                                    </script>

                                    <form id="new_order_form" method="post" action="{{ route('auction.new_order.save', ['id' => $auction->id]) }}">
                                        @csrf
                                        <input type="hidden" name="form[share_item_id]" value="{{ $selectedItem ? $selectedItem->id : '' }}">
                                        <table class="coll w100" border="0" style="margin-top:6px">
                                            <tbody><tr>
                                                <td class="p6h" width="60" valign="top">
                                                    @if($selectedItem)
                                                        <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url({{ asset($selectedItem->image) }}); background-size: 50px 50px;">
                                                            <tbody><tr><td valign="bottom">&nbsp;</td></tr></tbody>
                                                        </table>
                                                    @else
                                                        <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url({{ asset('img/bg/empty_slot.gif') }}); background-size: 50px 50px;">
                                                            <tbody><tr><td valign="bottom">&nbsp;</td></tr></tbody>
                                                        </table>
                                                    @endif
                                                </td>
                                                <td valign="top" class="b" height="60" width="100%">
                                                    <table height="60" class="coll w100 p6h brd2-all" border="0">
                                                        <colgroup><col width="100%"></colgroup>
                                                        <tbody>
                                                        <tr>
                                                            <td height="20" class="brd2-top brd2 b" colspan="2">
                                                                {{ $selectedItem ? $selectedItem->name : 'Выберите предмет из списка справа' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="28" class="brd2-top brd2 b">Цена за шт:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" nowrap="" style="padding-right: 11px;">
                                                                <input type="text" id="price"
                                                                       name="form[price]" value="{{ $defaultPrice }}"
                                                                       style="height:17px;width:75px;text-align:right;"
                                                                       maxlength="9" class="dbgl2 brd b small"
                                                                       oninput="updateTotal()"
                                                                       onkeyup="setTimeout('updateTotal()', 10)">&nbsp;
                                                                <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                                                            </td>
                                                        </tr>
                                                        <tr class="bg_l">
                                                            <td height="28" class="brd2-top brd2 b">Кол-во:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" nowrap="">
                                                                <input type="text" id="count"
                                                                       name="form[count]" value="1"
                                                                       style="height:17px;width:75px;text-align:right"
                                                                       oninput="updateTotal()"
                                                                       onkeyup="setTimeout('updateTotal()', 10)"
                                                                       class="dbgl2 brd b small">
                                                                <span>шт.</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="28" class="brd2-top brd2 b">Эскроу:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" style="padding-right: 11px;">
                                                                <span id="escrow_display">0</span>&nbsp;<img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                                                            </td>
                                                        </tr>
                                                        <tr class="bg_l">
                                                            <td height="28" class="brd2-top brd2 b">Итого спишется:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="right" style="padding-right: 11px;">
                                                                <span id="total_display">100</span>&nbsp;<img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="28" class="brd2-top brd2 b">Анонимно:</td>
                                                            <td height="28" class="brd2-top brd2 b" align="left" nowrap="">
                                                                <input type="hidden" name="form[is_anonymous]" value="">
                                                                <input type="checkbox" name="form[is_anonymous]" value="1" id="_chk_anon">&nbsp;<label for="_chk_anon"></label>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <br>
                                                    <span class="butt1 pointer">
                                                        <span>
                                                            <input value="Создать заявку" type="submit" onclick="if(document._submit)return false;document._submit=true;" {{ !$selectedItem ? 'disabled' : '' }}>
                                                        </span>
                                                    </span>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </form>

                                    <br>Комиссия за создание заявки — фиксированные <b>100 монет</b>.
                                    <br>Деньги эскроу (цена × кол-во) замораживаются и выплачиваются продавцу при выполнении заявки.
                                    <br>При отмене заявки эскроу возвращается, комиссия 100 монет не возвращается.
                                    <br>Продавец платит налог при выполнении заявки по логарифмической формуле от суммы сделки.
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
                    <td align="left" valign="top" width="50%">
                        <table align="center" class="coll" cellpadding="0" cellspacing="0" style="margin:3px auto; width: 100%;">
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
                                            <td valign="top" style="padding-bottom:20px;">
                                                <br>
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <div id="item_list" class="backpack_overflow lscroll">
                                                                <table class="coll w100 brd2-all" border="0">
                                                                    <colgroup>
                                                                        <col width="50">
                                                                        <col class="p6h">
                                                                        <col class="p6h" align="center" width="70">
                                                                    </colgroup>
                                                                    <tbody>
                                                                    <tr height="17" class="bg_l">
                                                                        <td class="brd2-top brd2" align="center"></td>
                                                                        <td class="brd2-top brd2" align="center">Название</td>
                                                                        <td class="brd2-top brd2" align="center"></td>
                                                                    </tr>
                                                                    @foreach($shareItems as $item)
                                                                        <tr height="17" class="brd2-top brd2 {{ $loop->iteration % 2 == 0 ? 'bg_l' : '' }}" align="center">
                                                                            <td class="brd2-top brd2" style="padding: 0" width="50" height="50">
                                                                                @if($item->image)
                                                                                    <img src="{{ asset($item->image) }}" style="width: 100%" alt="">
                                                                                @endif
                                                                            </td>
                                                                            <td align="left">
                                                                                <b class="b">{{ $item->name }}</b><br>
                                                                                <span title="Тип предмета">
                                                                                    <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $item->getTypeName() }}
                                                                                </span>
                                                                            </td>
                                                                            <td nowrap="">
                                                                                <b class="butt2 pointer"><b><input value="выбрать" type="submit" class="select-item" data-href="{{ route('auction.new_order', ['id' => $auction->id, 'siid' => $item->id]) }}"></b></b>
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
            case 'i': sendDataToGame('{{ route('backpack') }}'); break;
            case 'c': sendDataToGame('{{ route('character') }}'); break;
            case ' ': sendDataToGame('{{ route('location') }}'); break;
            default: return;
        }
        event.preventDefault();
    }

    document.addEventListener('keydown', handleKeydown);

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    document.querySelectorAll('.select-item').forEach(function (button) {
        button.addEventListener('click', function () {
            const href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>