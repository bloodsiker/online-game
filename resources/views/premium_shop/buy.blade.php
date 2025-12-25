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
        a {
            text-decoration: none;
        }
        /*img {*/
        /*    vertical-align: middle;*/
        /*}*/

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
        .store-list-item {
            width: 340px;
            height: 90px;
            border: 1px solid #DB9F73;
            border-radius: 5px;
            background-image: url(/img/bg/tbl-usi_bg.gif);
            background-repeat: repeat;
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    @php
        $btnLeft1 = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1 = 'img/bg/btn/btn-right1.gif';

        $btnLeft2 = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2 = 'img/bg/btn/btn-right2.gif';

        $active = false;
    @endphp
    <tr height="21">
        @foreach($shop->categories as $category)
            @php
                $active = $activeCategoryId === $category->id;
            @endphp
            <td width="19"><img id="left_1" src="{{ asset($active ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td width="60" id="tab_1" align="center" style="background: url({{ asset($active ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0 2px 6px;">
                <a id="center_1" href="{{ route('premium.shop', ['category_id' => $category->id]) }}" title="Купить" class="{{ $active ? 'btn_2' : 'btn_1' }}">{{ $category->name }}</a>
            </td>
            <td width="19"><img id="right_1" src="{{ asset($active ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach

        <td></td>

        <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_4" href="{{ route('location') }}" title="Подаренные Вам подарки" class="btn_1">Выход</a></td>
        <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>
<br>
<table class="coll w100" height="100%">
    <tbody>
    <tr>
        <td valign="top" height="100%">
            <table class="coll w100">
                <tbody>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;" align="left">
                        @if($items->count())
                            @foreach($items as $item)
                                <form method="post" action="" style="margin: 3px; display: inline-block;">
                                    <table border="0" cellspacing="0" cellpadding="0" id="item_list" class="store-list-item">
                                        <tbody>
                                        <tr>
                                            <td align="left" width="60" valign="top">
                                                <div style="margin: 8px; background: url('{{ asset($item->item->image) }}'); background-size: cover;  width: 50px; height: 50px;">
                                                    <div class="art-item-bg">
                                                    </div>
                                                    <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                           style="position: absolute; z-index:10;">
                                                        <tbody>
                                                        <tr>
                                                            <td store="1" act1="0" act2="0" act3="0" art_id="8365"
                                                                div_id="15635" onmouseover="artifactAlt(this,event,2)"
                                                                onmouseout="artifactAlt(this,event,0)" valign="bottom">
                                                                &nbsp;
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </td>
                                            <td valign="top" style="padding: 5px; width: 100%;">
                                                <input type="hidden" name="form[item_id]" value="15635">
                                                <table class="w100 coll" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td colspan="3">
                                                            <a href="#"
                                                               onclick="showArtifactInfo(false,8365);return false;"
                                                               style="color:#ff0000;  text-overflow: ellipsis; display: block; overflow: hidden; white-space: nowrap; width: 250px;"
                                                               class="b">{{ $item->item->name }}</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td nowrap="" title="Тип предмета">
                                                            <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $item->item->getTypeName() }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="b" title="Цена" style="padding-top: 13px;" valign="top">
                                                            @if($item->price)
                                                                <div title="Бриллиант">
                                                                    <img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle">
                                                                    &nbsp;{{ format_money($item->price) }}
                                                                </div>
                                                            @endif

                                                            @if($item->diamond)
                                                                <div title="Бриллиант">
                                                                    <img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle">
                                                                    &nbsp;{{ format_money($item->diamond) }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td align="center" valign="top" style="padding-top: 16px; width: 60px;">
                                                            <div class="cart-amount-sell-price">
                                                        <span class="cart-amount-input-cont">
                                                            <span class="b-input">
                                                                <span class="b-input__inner">
                                                                    <span class="arrow left left-disabled" onclick="shopItemCounter(this);" title="Уменьшить кол-во"></span>
                                                                    <span class="arrow right" onclick="shopItemCounter(this);" title="Увеличить кол-во"></span>
                                                                    <input type="text" data-id="1" value="1" class="cart_amount_sell_input count_buy" autocomplete="off">
                                                                </span>
                                                            </span>
                                                        </span>
                                                            </div>
                                                        </td>
                                                        <td align="center" valign="top" style="padding-top: 16px; width: 80px;">
                                                            <b class="butt2 pointer "><b>
                                                                    <input value="В корзину" type="submit" onclick="if(document._submit)return false;document._submit=true;" style="width:63px">
                                                                </b>
                                                            </b>
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


{{--                        <form method="post" action="" style="margin: 3px; display: inline-block;">--}}
{{--                            <table border="0" cellspacing="0" cellpadding="0" id="item_list" class="store-list-item">--}}
{{--                                <tbody>--}}
{{--                                <tr>--}}
{{--                                    <td align="left" width="60" valign="top">--}}
{{--                                        <div style="margin: 8px; background: url('https://feo-dwar.com/images/data/artifacts/codex_rage_6.gif');  width: 60px; height: 60px;">--}}
{{--                                            <div class="art-item-bg">--}}
{{--                                            </div>--}}
{{--                                            <table width="60" height="60" cellpadding="0" cellspacing="0" border="0"--}}
{{--                                                   style="position: absolute; z-index:10;">--}}
{{--                                                <tbody>--}}
{{--                                                <tr>--}}
{{--                                                    <td store="1" act1="0" act2="0" act3="0" art_id="8365"--}}
{{--                                                        div_id="15635" onmouseover="artifactAlt(this,event,2)"--}}
{{--                                                        onmouseout="artifactAlt(this,event,0)" valign="bottom">--}}
{{--                                                        &nbsp;--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                                </tbody>--}}
{{--                                            </table>--}}
{{--                                        </div>--}}

{{--                                    </td>--}}
{{--                                    <td valign="top" style="padding: 5px; width: 100%;">--}}
{{--                                        <input type="hidden" name="form[item_id]" value="15635">--}}
{{--                                        <table class="w100 coll" border="0">--}}
{{--                                            <tbody>--}}
{{--                                            <tr>--}}
{{--                                                <td colspan="3">--}}
{{--                                                    <a href="#"--}}
{{--                                                       onclick="showArtifactInfo(false,8365);return false;"--}}
{{--                                                       style="color:#ff0000;  text-overflow: ellipsis; display: block; overflow: hidden; white-space: nowrap; width: 250px;"--}}
{{--                                                       class="b">Кодекс «Воинское искусство»</a>--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                <td nowrap="" title="Тип предмета">--}}
{{--                                                    <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> Фолиант--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                <td class="b" title="Цена"--}}
{{--                                                    style="padding-top: 13px;" valign="top">--}}
{{--                                                    <div title="Бриллиант">--}}
{{--                                                        <img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle">--}}
{{--                                                        &nbsp;100.00--}}
{{--                                                    </div>--}}

{{--                                                    <div style="padding-top: 5px">--}}
{{--                                                        <img src="{{ asset('img/resource/q_gorst.gif') }}" height="22" alt="" align="absmiddle">&nbsp;--}}
{{--                                                        <b class="red">100 шт.</b>--}}
{{--                                                    </div>--}}

{{--                                                </td>--}}
{{--                                                <td align="center" valign="top" style="padding-top: 16px; width: 60px;">--}}
{{--                                                    <div class="cart-amount-sell-price">--}}
{{--                                                        <span class="cart-amount-input-cont">--}}
{{--                                                            <span class="b-input">--}}
{{--                                                                <span class="b-input__inner">--}}
{{--                                                                    <span class="arrow left left-disabled" onclick="shopItemCounter(this);" title="Уменьшить кол-во"></span>--}}
{{--                                                                    <span class="arrow right" onclick="shopItemCounter(this);" title="Увеличить кол-во"></span>--}}
{{--                                                                    <input type="text" data-id="1" value="1" class="cart_amount_sell_input count_buy" autocomplete="off">--}}
{{--                                                                </span>--}}
{{--                                                            </span>--}}
{{--                                                        </span>--}}
{{--                                                    </div>--}}
{{--                                                </td>--}}
{{--                                                <td align="center" valign="top" style="padding-top: 16px; width: 80px;">--}}
{{--                                                    <b class="butt2 pointer "><b>--}}
{{--                                                            <input value="В корзину" type="submit" onclick="if(document._submit)return false;document._submit=true;" style="width:63px">--}}
{{--                                                        </b>--}}
{{--                                                    </b>--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                            </tbody>--}}
{{--                                        </table>--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </form>--}}
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="10"></td>
        <td valign="top" width="33%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Ваша корзина</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 4px">
                        <form method="post"
                              action="area_store.php?source=premium&amp;mode=store&amp;category_id=283&amp;page=0&amp;action=go"
                              id="action_form">
                            <table class="coll w100 p10h p4v brd2">
                                <colgroup>
                                    <col>
                                    <col>
                                    <col>
                                    <col width="1%">
                                </colgroup>
                                <tbody>
                                <tr class="bg_l">
                                    <td><a href="#" onclick="showArtifactInfo(false, );return false;" class="redd b">Необычайное кольцо вседозволенности</a></td>
                                    <td class="b red" align="center">
                                        <div style="padding-top:5px;" art_id="15629">
                                            <img
                                                style="cursor: pointer;" height="15" width="15"
                                            src="{{ asset('img/resource/q_gorst.gif') }}" store="1" art_id="15629"
                                            onclick="showArtifactInfo(false, 15629, null, event); return false;"
                                            div_id="AA_15629" onmouseover="artifactAlt(this,event,2)"
                                            onmouseout="artifactAlt(this,event,0)">&nbsp;<span
                                                class="current_amount_artikul" title="Цена">35</span>/<span
                                                class="owned_amount_artikul" title="В рюкзаке">0</span></div>
                                        <span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11"
                                                                     height="11" align="absmiddle"></span>&nbsp;35.00
                                    </td>
                                    <td>1 шт</td>
                                    <td>
                                        <a href="area_store.php?source=premium&amp;mode=store&amp;category_id=283&amp;page=0&amp;action=delete_cart&amp;ref=33164" title="Удалить">
                                            <img src="{{ asset('img/icon/tbl-shp_x.gif') }}" width="11" height="13" border="0"></a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <br>
                            <table class="coll w100 p10h p4v brd2-all">
                                <colgroup>
                                    <col>
                                    <col width="30%">
                                </colgroup>
                                <tbody>
                                <tr class="">
                                    <td class="brd2-top brd2-bt"><b>На общую сумму:</b></td>
                                    <td class="brd2-top brd2-bt b red"><span title="Бриллиант"><img
                                                src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11"
                                                align="absmiddle"></span>&nbsp;35.00<br></td>
                                </tr>
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt"><b>У Вас денег:</b></td>
                                    <td class="brd2-top brd2-bt b red"><span title="Бриллиант"><img
                                                src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11"
                                                align="absmiddle"></span>&nbsp;45.00<br><span title="Серебряный"><img
                                                src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11"
                                                align="absmiddle"></span>&nbsp;10<br></td>
                                </tr>
                                </tbody>
                            </table>
                            <img src="images/d.gif" width="1" height="5"><br>
                            <span class="butt1 pointer "><span><input value="Оплатить товар" type="submit"
                                                                      onclick="if(document._submit)return false;document._submit=true;"
                                                                      class="grnn"></span></span><br>
                            <span class="butt1 pointer "><span><input value="Очистить корзину" type="button"
                                                                      onclick="if(document._submit)return false;document._submit=true;location.href='area_store.php?source=premium&amp;mode=store&amp;category_id=283&amp;page=0&amp;action=clear_cart';"
                                                                      class="redd"></span></span><br>
                        </form>

                        <br>
                        <div class="p10h p2v brd2-all bg_l" align="left">
                            В магазине Вы можете приобрести необходимую Вам экипировку, снадобья и прочие предметы,
                            которые помогут Вашему персонажу.
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

    document.querySelectorAll('.buy-item').forEach(function(button) {
        button.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            const itemId = this.getAttribute('data-id');
            const inputElement = document.querySelector('.count_buy[data-id="' + itemId + '"]');
            if (href) {
                let countBuy = 1
                if (inputElement) {
                    countBuy = inputElement.value;
                }
                window.location.href = href + '?count=' + countBuy;  // Переход по URL
            }
        });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
