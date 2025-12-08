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
            font-size: 14px;
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
                        <a id="center_1" href="{{ route('shop', ['id' => $id]) }}" title="Купить" class="btn_2">Купить</a>
                    </td>
                    <td width="19"><img id="right_1" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" id="tab_2" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('shop.sell_item', ['id' => $id]) }}" title="Продать" class="btn_1">Продать</a></td>
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
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Золотой"><img src="https://fun-dwar.com//images/m_game3.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ $user->money }} </b>
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Рубин"><img src="https://fun-dwar.com//images/m_rub.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;676.96 </b>
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="https://fun-dwar.com//images/m_dmd.gif" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;3.82 </b>
                    </td>
                </tr>
                </tbody>
            </table>

            <div style="margin-top: 20px">
                <p>Вы купили товар "<b>{{ $shareItem->name }}</b>"...</p>
                <p>После покупки осталось <b>{{ number_format($user->money, 0, ',', '') }}</b> монеты.</p>
                <p><a href="{{ route('shop.buy_item', ['id' => $id, 'itemId' => $shareItem->id]) }}">Купить такой же предмет</a> »</p>
                <p><a href="{{ route('shop', ['id' => $id]) }}">Список товаров</a> »</p>
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
