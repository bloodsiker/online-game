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
        .clan-icon { width: 13px; height: 13px; vertical-align: middle; margin-right: 3px; }
        .clan-tag { font-size: 11px; color: #5B4736; margin-right: 3px; }
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
            @include('auction::_tabs', ['activeTab' => 'buy'])
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
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }} </b>
                    &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }} </b>
                    </td>
                </tr>
                </tbody>
            </table>

            <form method="post" action="area_auction.php?&amp;mode=request">
                <table class="coll w100" border="0" style="margin-bottom:3px;margin-top:8px">
                    <tbody>
                    <tr>
                        <td nowrap="" width="60">Название:</td>
                        <td nowrap="" width=""><input name="filter[title]" type="text" size="20" maxlength="60" value=""
                                                      class="dbgl2 brd b small" style="width:100%"></td>
                        <td nowrap="" width="150" align="center">Кол-во:&nbsp;<input name="filter[count_min]"
                                                                                     type="text" size="2" maxlength="4"
                                                                                     value="" class="dbgl2 brd b small"
                                                                                     style="width:30px;text-align:center">&nbsp;-&nbsp;<input
                                name="filter[count_max]" type="text" size="2" maxlength="4" value=""
                                class="dbgl2 brd b small" style="width:30px;text-align:center"></td>
                        <td nowrap="" width="100" align="center">
                            <nobr>Вид: <select name="filter[kind]" class="dbgl2 b small" style="width:70px;">
                                    <option value="">--</option>
                                    <option value="g1_168,12,169,114,44,17,113,10" class="redd">Оружие и щиты</option>
                                    <option value="g1_168">&nbsp;&nbsp;&nbsp;&nbsp;Большой лук</option>
                                    <option value="g1_12">&nbsp;&nbsp;&nbsp;&nbsp;Двуручное</option>
                                    <option value="g1_169">&nbsp;&nbsp;&nbsp;&nbsp;Зачарованное оружие</option>
                                    <option value="g1_114">&nbsp;&nbsp;&nbsp;&nbsp;Колчан</option>
                                    <option value="g1_44">&nbsp;&nbsp;&nbsp;&nbsp;Левая рука</option>
                                    <option value="g1_17">&nbsp;&nbsp;&nbsp;&nbsp;Легкий щит</option>
                                    <option value="g1_113">&nbsp;&nbsp;&nbsp;&nbsp;Лук</option>
                                    <option value="g1_10">&nbsp;&nbsp;&nbsp;&nbsp;Основное</option>
                                    <option value="g2_20,21,3,7,5,2,4,6,1" class="redd">Доспехи</option>
                                    <option value="g2_20">&nbsp;&nbsp;&nbsp;&nbsp;Кираса</option>
                                    <option value="g2_21">&nbsp;&nbsp;&nbsp;&nbsp;Кольчуга</option>
                                    <option value="g2_3">&nbsp;&nbsp;&nbsp;&nbsp;Куртка</option>
                                    <option value="g2_7">&nbsp;&nbsp;&nbsp;&nbsp;Наплечники</option>
                                    <option value="g2_5">&nbsp;&nbsp;&nbsp;&nbsp;Наручи</option>
                                    <option value="g2_2">&nbsp;&nbsp;&nbsp;&nbsp;Обувь</option>
                                    <option value="g2_4">&nbsp;&nbsp;&nbsp;&nbsp;Одежда</option>
                                    <option value="g2_6">&nbsp;&nbsp;&nbsp;&nbsp;Поножи</option>
                                    <option value="g2_1">&nbsp;&nbsp;&nbsp;&nbsp;Шлем</option>
                                    <option value="g3_72,23,22" class="redd">Свитки, амулеты, эликсиры</option>
                                    <option value="g3_72">&nbsp;&nbsp;&nbsp;&nbsp;Амулеты</option>
                                    <option value="g3_23">&nbsp;&nbsp;&nbsp;&nbsp;Свиток</option>
                                    <option value="g3_22">&nbsp;&nbsp;&nbsp;&nbsp;Эликсир</option>
                                    <option value="g5_25,62,76,31,30" class="redd">Аксессуары</option>
                                    <option value="g5_25">&nbsp;&nbsp;&nbsp;&nbsp;Амулет</option>
                                    <option value="g5_62">&nbsp;&nbsp;&nbsp;&nbsp;Боевые амулеты</option>
                                    <option value="g5_76">&nbsp;&nbsp;&nbsp;&nbsp;Кольца</option>
                                    <option value="g5_31">&nbsp;&nbsp;&nbsp;&nbsp;Пояс</option>
                                    <option value="g5_30">&nbsp;&nbsp;&nbsp;&nbsp;Рюкзак</option>
                                    <option value="g6_36,59,38,45,37,92,50,46,52" class="redd">Ингредиенты</option>
                                    <option value="g6_36">&nbsp;&nbsp;&nbsp;&nbsp;Драгоценные камни</option>
                                    <option value="g6_59">&nbsp;&nbsp;&nbsp;&nbsp;Колба</option>
                                    <option value="g6_38">&nbsp;&nbsp;&nbsp;&nbsp;Кристалл</option>
                                    <option value="g6_45">&nbsp;&nbsp;&nbsp;&nbsp;Порошок</option>
                                    <option value="g6_37">&nbsp;&nbsp;&nbsp;&nbsp;Растения</option>
                                    <option value="g6_92">&nbsp;&nbsp;&nbsp;&nbsp;Реагенты</option>
                                    <option value="g6_50">&nbsp;&nbsp;&nbsp;&nbsp;Рыба</option>
                                    <option value="g6_46">&nbsp;&nbsp;&nbsp;&nbsp;Травяной сбор</option>
                                    <option value="g6_52">&nbsp;&nbsp;&nbsp;&nbsp;Чернила</option>
                                    <option value="g7_138,48,171,132,66,110,75,32,39,107,28,90" class="redd">Разное
                                    </option>
                                    <option value="g7_138">&nbsp;&nbsp;&nbsp;&nbsp;Аркат</option>
                                    <option value="g7_48">&nbsp;&nbsp;&nbsp;&nbsp;Еда</option>
                                    <option value="g7_171">&nbsp;&nbsp;&nbsp;&nbsp;Карты</option>
                                    <option value="g7_132">&nbsp;&nbsp;&nbsp;&nbsp;Карты сокровищ</option>
                                    <option value="g7_66">&nbsp;&nbsp;&nbsp;&nbsp;Квестовые ресурсы</option>
                                    <option value="g7_110">&nbsp;&nbsp;&nbsp;&nbsp;Коллекции</option>
                                    <option value="g7_75">&nbsp;&nbsp;&nbsp;&nbsp;Ларцы, сундуки</option>
                                    <option value="g7_32">&nbsp;&nbsp;&nbsp;&nbsp;Подарки</option>
                                    <option value="g7_39">&nbsp;&nbsp;&nbsp;&nbsp;Руна</option>
                                    <option value="g7_107">&nbsp;&nbsp;&nbsp;&nbsp;Скрижаль</option>
                                    <option value="g7_28">&nbsp;&nbsp;&nbsp;&nbsp;Фолиант</option>
                                    <option value="g7_90">&nbsp;&nbsp;&nbsp;&nbsp;Шахматные фигуры</option>
                                </select></nobr>
                        </td>
                        <td nowrap="" width="115" align="center">
                            <nobr>
                                <b class="butt2 pointer">
                                    <b><input value="Ок" type="submit" onclick="if(document._submit)return false;document._submit=true;" name="filterapply" style="width:35px"></b>
                                </b>
                                &nbsp;
                                <b class="butt2 pointer">
                                    <b><input value="Сброс" type="submit" onclick="if(document._submit)return false;document._submit=true;" name="filterclear" style="width:35px"></b>
                                </b>
                            </nobr>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>

            <br>


            <table class="coll w100 brd2-all" border="0" id="item_list">
                <colgroup>
                    <col width="50">
                    <col class="p6h">
                    <col class="p6h" align="center" width="200">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="110">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Продавец</td>
                    <td class="brd2-top brd2" align="center">Кол-во</td>
                    <td class="brd2-top brd2" align="center">Цена</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Купить</td>
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
                        </td>
                        <td align="left">
                            <a href="{{ route('items.info', ['id' => $slot->item->id]) }}"
                               onclick="showArtifactInfo(22195638,false);return false;"
                               style="color:#666666" class="b">{{ $slot->item->itemInfo->name }}</a><br>
                            <span title="Тип предмета">
                                <img src="{{ asset('img/icon/tbl-shp_item-icon.gif') }}" width="11" height="10" align="absmiddle"> {{ $slot->item->itemInfo->getTypeName() }}
                            </span>
                        </td>
                        <td nowrap="">
                            @if(!$slot->is_anonymous)
                                @php $slotClan = $slot->user->clanMembership?->clan; @endphp
                                <a href="#" onclick="sendPrivate('{{ addslashes($slot->user->name) }}'); return false;" title="Написать в приват">
                                    <img src="{{ asset('main/images/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                </a>
                                @if($slotClan)
                                    @if($slotClan->icon)
                                        <img class="clan-icon" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($slotClan->icon) }}" title="{{ $slotClan->name }}" alt="{{ $slotClan->name }}">
                                    @else
                                        <span class="clan-tag">[{{ $slotClan->name }}]</span>
                                    @endif
                                @endif
                                <b>{{ $slot->user->name }}&nbsp;[{{ $slot->user->player->lvl }}]</b>
                                <a href="#" onclick="whoOpenUserInfo({{ $slot->user->id }}); return false;" title="Информация о персонаже">
                                    <img src="{{ asset('main/images/player_info.gif') }}" border="0" width="10" height="10" align="absmiddle">
                                </a>
                            @else
                                <i>Анонимно</i>
                            @endif
                        </td>
                        <td nowrap="">{{ $slot->count }}</td>
                        <td nowrap="">
                            <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ format_money($slot->price) }}
                        </td>
                        <td nowrap="">
                            <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ format_money((int) round($slot->price / $slot->count)) }}
                        </td>
                        <td nowrap="">
                            <b class="butt2 pointer"><b><input value="купить" type="submit" class="buy-item" data-id="{{ $slot->item->id }}" data-href="{{ route('auction.buy_item', ['id' => $auction->id, 'itemId' => $slot->item->id]) }}"></b></b>
                        </td>
                    </tr>
                @endforeach
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Продавец</td>
                    <td class="brd2-top brd2" align="center">Кол-во</td>
                    <td class="brd2-top brd2" align="center">Цена</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Купить</td>
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

    // Написать в приват — вставляет команду prv[Ник] в поле ввода чата.
    // Страница аукциона грузится в game-frame (соседний с chat-frame), поэтому
    // до bottom-frame идём через chat-frame, а не напрямую через window.parent.
    function sendPrivate(name) {
        try {
            var chatFrame = window.parent.document.getElementById('chat-frame');
            var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
            if (bottomFrame && bottomFrame.contentWindow) {
                bottomFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (e) {}
    }

    function whoOpenUserInfo(userId) {
        window.open('{{ url('/info/u') }}/' + userId, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
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
                submitAuctionAction(href, {count: countBuy});
            }
        });
    });

    function submitAuctionAction(url, data) {
        const form = document.createElement('form'); form.method = 'POST'; form.action = url;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        Object.entries(data || {}).forEach(([key, value]) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = key; input.value = value; form.appendChild(input); });
        document.body.appendChild(form); form.submit();
    }

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
