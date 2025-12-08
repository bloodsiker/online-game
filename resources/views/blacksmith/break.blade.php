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
        .collections-title, .collection-body {
            padding: 5px;
        }
        .collections-divider {
            display: block;
            height: 5px;
            margin: 0 0 5px;
            font-size: 0;
            border-bottom: #db9f73 1px solid;
        }
        .collection-slot {
            display: inline-block;
            position: relative;
            width: 52px;
            height: 70px;
            overflow: hidden;
            vertical-align: top;
        }
        .collection-slot__img {
            display: block;
            width: 50px;
            height: 50px;
            padding: 1px;
            background: url(../images/slot-empty.png) no-repeat;
        }
        .collection-slot.active .collection-slot__qty, .collection-slot.active .collection-slot__qty-current {
            color: #489200;
        }
        .collection-slot__img.grayscale {
            background: #000;
        }
        .collection-slot__img.grayscale img {
            opacity: .3;
        }
        .collection-slot__qty {
            display: block;
            font-weight: 700;
            text-align: center;
        }
        .collection-slot__qty, .collection-slot__qty-current {
            font-size: 11px;
        }
        .collection-slot__qty-current {
            color: #c00000;
        }
        .collection-ico {
            display: inline-block;
            height: 65px;
            padding: 5px 0 0;
            vertical-align: top;
            font-weight: 700;
            font-size: 40px;
        }
        .collection-resource-img {
            width: 100%;
        }
        .regcolor, .regcolor * {
            color: #955c4a;
        }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">

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
                    <td width="90" id="tab_1" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_1" href="{{ route('blacksmith', ['id' => $blacksmith->id]) }}" title="Купить" class="btn_1">Крафтить</a>
                    </td>
                    <td width="19"><img id="right_1" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

                    <td width="19"><img id="left_2" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="120" id="tab_2" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_2" href="{{ route('blacksmith.break', ['id' => $blacksmith->id]) }}" title="Продать" class="btn_2">Разбыть предмет</a></td>
                    <td width="19"><img id="right_2" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

                    <td></td>

                    <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a id="center_4" href="{{ route('location') }}" title="Выход" class="btn_1">Выход</a></td>
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

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                @foreach($items as $item)
                    <tr class="collection-group-13">
                        <input type="hidden" name="collection_id" value="202" disabled="">
                        <td colspan="3">
                            <table class="coll w100 p10h p2v brd2-all">
                                <tbody>
                                <tr class="bg_l">
                                    <td>
                                        <div class="collections-title">
                                            <b class="collection-name" style="color: #3300ff">{{ $item->item->itemInfo->name }}</b>
                                        </div>
                                        <span class="collections-divider"></span>
                                        <div class="collections-body">
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <span class="collection-slot">
                                                            <span class="collection-slot__img">
                                                                <a href="{{ $item->item->id }}" class="collection-resource redd">
                                                                    <img src="{{ $item->item->itemInfo->image }}" class="collection-resource-img" alt="">
                                                                </a>
                                                            </span>
                                                             <span class="collection-slot__qty"></span>
                                                            <div class="collect-btn">
                                                                <b class="butt2 pointer">
                                                                    <b>
                                                                        <button type="button" class="pointer break-item" data-href="{{ route('blacksmith.break', ['id' => $blacksmith->id, 'iid' => $item->item->id]) }}" style="width: 36px;">сломать</button>
                                                                    </b>
                                                                </b>
                                                            </div>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <b class="collection-ico">=</b>
                                                    </td>
                                                    <td>
                                                        <span class="collection-slot">
                                                            <span class="collection-slot__img">
                                                                <a href="#" class="collection-resource redd">
                                                                    <img src="{{ $crystal->image }}" class="collection-resource-img" alt="">
                                                                </a>
                                                            </span>
                                                             <span class="collection-slot__qty">{{ $item->item->itemInfo->break_crystal }}</span>
                                                        </span>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="canc-sort">
                        <td colspan="3"><img src="{{ asset('img/bg/blank.gif') }}" height="5" alt=""></td>
                    </tr>
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

    document.querySelectorAll('.break-item').forEach(function(button) {
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
