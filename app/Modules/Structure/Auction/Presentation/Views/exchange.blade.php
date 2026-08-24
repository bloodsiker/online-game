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
        .clan-icon { width: 13px; height: 13px; vertical-align: middle; margin-right: 3px; }
        .clan-tag { font-size: 11px; color: #5B4736; margin-right: 3px; }
        .dbgl2 {
            background-color: #FFFBD6;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
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
        .count-input {
            height: 15px;
            width: 45px;
            text-align: right;
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
            @include('auction::_tabs', ['activeTab' => 'exchange'])
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
                    <td align="right" nowrap=""><b>Биржа — заявки на покупку от других игроков. Продайте свои предметы по выгодным ценам.</b></td>
                </tr>
                </tbody>
            </table>

            <form method="get" action="{{ route('auction.exchange', ['id' => $auction->id]) }}">
                <table class="coll w100" border="0" style="margin-bottom:3px;margin-top:8px">
                    <tbody>
                    <tr>
                        <td nowrap="" width="110">
                            <input type="hidden" name="filter[matching]" value="0">
                            <input type="checkbox" name="filter[matching]" value="1" id="filter_matching"
                                   {{ ($filter['matching'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label for="filter_matching">Подходящие</label>
                        </td>
                        <td nowrap="" width="60">Название:</td>
                        <td nowrap="" width=""><input name="filter[name]" type="text" size="20" maxlength="60"
                                                      value="{{ $filter['name'] ?? '' }}"
                                                      class="dbgl2 brd b small" style="width:100%"></td>
                        <td nowrap="" width="150" align="center">Кол-во:&nbsp;<input name="filter[count_min]"
                                                                                     type="text" size="2" maxlength="6"
                                                                                     value="{{ $filter['count_min'] ?? '' }}"
                                                                                     class="dbgl2 brd b small"
                                                                                     style="width:30px;text-align:center">&nbsp;-&nbsp;<input
                                name="filter[count_max]" type="text" size="2" maxlength="6"
                                value="{{ $filter['count_max'] ?? '' }}"
                                class="dbgl2 brd b small" style="width:30px;text-align:center"></td>
                        <td nowrap="" width="100" align="center">
                            <nobr>Вид: <select name="filter[type]" class="dbgl2 b small" style="width:70px;">
                                    <option value="">--</option>
                                    @foreach(\App\Modules\Share\Domain\Enums\ShareItemType::cases() as $type)
                                        <option value="{{ $type->value }}" {{ ($filter['type'] ?? '') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                    @endforeach
                                </select></nobr>
                        </td>
                        <td nowrap="" width="115" align="center">
                            <nobr>
                                <b class="butt2 pointer">
                                    <b><input value="Ок" type="submit" onclick="if(document._submit)return false;document._submit=true;" style="width:35px"></b>
                                </b>
                                &nbsp;
                                <b class="butt2 pointer">
                                    <b><input value="Сброс" type="button" onclick="window.location='{{ route('auction.exchange', ['id' => $auction->id]) }}'" style="width:35px"></b>
                                </b>
                            </nobr>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>

            <br>

            <table class="coll w100 brd2-all" border="0">
                <colgroup>
                    <col width="50">
                    <col class="p6h">
                    <col class="p6h" align="center" width="180">
                    <col class="p6h" align="center" width="80">
                    <col class="p6h" align="center" width="90">
                    <col class="p6h" align="center" width="70">
                    <col class="p6h" align="center" width="110">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Покупатель</td>
                    <td class="brd2-top brd2" align="center">Требуется</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Продать кол-во</td>
                    <td class="brd2-top brd2" align="center">Продать</td>
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
                        <td nowrap="">
                            @if(!$order->is_anonymous)
                                @php $orderClan = $order->user->clanMembership?->clan; @endphp
                                <a href="#" onclick="sendPrivate('{{ addslashes($order->user->name) }}'); return false;" title="Написать в приват">
                                    <img src="{{ asset('main/images/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                </a>
                                @if($orderClan)
                                    @if($orderClan->icon)
                                        <img class="clan-icon" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($orderClan->icon) }}" title="{{ $orderClan->name }}" alt="{{ $orderClan->name }}">
                                    @else
                                        <span class="clan-tag">[{{ $orderClan->name }}]</span>
                                    @endif
                                @endif
                                <b>{{ $order->user->name }}&nbsp;[{{ $order->user->player->lvl }}]</b>
                                <a href="#" onclick="whoOpenUserInfo({{ $order->user->id }}); return false;" title="Информация о персонаже">
                                    <img src="{{ asset('main/images/player_info.gif') }}" border="0" width="10" height="10" align="absmiddle">
                                </a>
                            @else
                                <i>Анонимно</i>
                            @endif
                        </td>
                        <td nowrap=""><b>{{ $order->count }} шт.</b></td>
                        <td nowrap="">
                            <span title="Монет"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>
                            {{ format_money($order->price) }}
                        </td>
                        <td nowrap="" align="center">
                            <input type="text" class="dbgl2 brd b count-input fulfill-count" data-max="{{ $order->count }}" value="1" style="text-align:center">
                        </td>
                        <td nowrap="">
                            <b class="butt2 pointer"><b><input value="продать" type="submit" class="fulfill-btn" data-href="{{ route('auction.order.fulfill', ['id' => $auction->id, 'orderId' => $order->id]) }}"></b></b>
                        </td>
                    </tr>
                @empty
                    <tr height="17" class="brd2-top brd2 bg_l">
                        <td colspan="7" align="center">Нет активных заявок на покупку</td>
                    </tr>
                @endforelse
                <tr height="17" class="bg_l">
                    <td class="brd2-top brd2" align="center"></td>
                    <td class="brd2-top brd2" align="center">Название</td>
                    <td class="brd2-top brd2" align="center">Покупатель</td>
                    <td class="brd2-top brd2" align="center">Требуется</td>
                    <td class="brd2-top brd2" align="center">Цена за шт</td>
                    <td class="brd2-top brd2" align="center">Продать кол-во</td>
                    <td class="brd2-top brd2" align="center">Продать</td>
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

    // Написать в приват — вставляет команду prv[Ник] в поле ввода чата.
    // Страница биржи грузится в game-frame (соседний с chat-frame), поэтому
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

    document.querySelectorAll('.fulfill-count').forEach(function (input) {
        input.addEventListener('input', function () {
            const max = parseInt(this.getAttribute('data-max'), 10);
            let val = parseInt(this.value, 10);
            if (isNaN(val) || val < 1) this.value = 1;
            else if (val > max) this.value = max;
        });
    });

    document.querySelectorAll('.fulfill-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const countInput = row.querySelector('.fulfill-count');
            const count = countInput ? countInput.value : 1;
            const href = this.getAttribute('data-href');
            if (href) {
                submitAuctionAction(href, {count: count});
            }
        });
    });

    function submitAuctionAction(url, data) { const form = document.createElement('form'); form.method = 'POST'; form.action = url; form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'; Object.entries(data || {}).forEach(([key, value]) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = key; input.value = value; form.appendChild(input); }); document.body.appendChild(form); form.submit(); }

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
