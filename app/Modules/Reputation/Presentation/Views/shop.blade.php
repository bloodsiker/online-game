<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин — {{ $page->reputation->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        a { color: #000000; }
        a:hover { color: #353434; }
        table[cellpadding="0"][border="0"] {
            border-spacing: 0;
            border-collapse: collapse;
        }
        .tbl-usi_label-center {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 3px;
        }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .bg_l2 { background-image: url(/img/bg/info/bg_l2.gif); cursor: pointer; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .locked { opacity: 0.5; }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 6px; }
        .msg-error   { color: #a00000; font-weight: bold; padding: 4px 6px; }
        .req-item {
            display: inline-block;
            background: #f5e4c0;
            border: 1px solid #c8a052;
            border-radius: 3px;
            padding: 1px 4px;
            font-size: 10px;
            color: #5a3600;
            margin: 1px;
        }
        .price-tag {
            font-size: 11px;
            font-weight: bold;
        }
        .price-money { color: #7a4e00; }
        .price-diamond { color: #4060b0; }
        .points-req { color: #7a1c00; font-size: 10px; }
    </style>
</head>
<body>

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
        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"></td>
        <td width="100" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('reputation.index', $page->reputation->id) }}" class="btn_2">← Репутация</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"></td>

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"></td>
        <td width="140" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Вернуться в локацию</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"></td>
    </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr><td height="10"></td></tr>
    <tr>
        <td>
            <table class="coll w100" height="100%">
                <tbody>
                <tr>
                    {{-- Main Content --}}
                    <td valign="top" width="100%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr height="22">
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                            <td align="center" class="tbl-usi_label-center">Магазин — {{ $page->reputation->name }}</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    <table class="coll w100 p6h p2v brd2-all">
                                        <tbody>

                                        {{-- Message --}}
                                        @if($page->message)
                                            <tr>
                                                <td colspan="3" class="{{ $page->messageType === 'success' ? 'msg-success' : 'msg-error' }}">
                                                    {{ $page->message }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Points header --}}
                                        <tr class="bg_l">
                                            <td class="brd2-top brd2-bt" colspan="3" style="padding: 5px 10px;">
                                                Ваши очки репутации: <b>{{ $page->pr->points }}</b>
                                            </td>
                                        </tr>

                                        @forelse($page->reputation->shopItems as $shopItem)
                                            @php $locked = $page->pr->points < $shopItem->min_points; @endphp
                                            <tr class="{{ $locked ? 'bg_l' : 'bg_l' }}" @if(!$locked) onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'" @endif>
                                                <td class="brd2-top brd2-bt" width="1%" @if($locked) style="opacity:0.5" @endif>
                                                    <img src="{{ asset('img/icon/qst_store.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt" @if($locked) style="color:#999;" @endif>
                                                    <b>{{ $shopItem->item->name ?? 'Предмет' }}</b>
                                                    <br>
                                                    @if($shopItem->min_points > 0)
                                                        <span class="points-req">Требуется репутация: {{ $shopItem->min_points }}</span>
                                                        <br>
                                                    @endif
                                                    @if($shopItem->price > 0)
                                                        <span class="price-tag price-money">{{ number_format($shopItem->price, 0, '', ' ') }} монет</span>
                                                    @endif
                                                    @if($shopItem->diamond > 0)
                                                        @if($shopItem->price > 0) &nbsp;+&nbsp; @endif
                                                        <span class="price-tag price-diamond">{{ $shopItem->diamond }} кристаллов</span>
                                                    @endif
                                                    @if($shopItem->requirements->count())
                                                        <br>
                                                        <small>Также требуется:</small>
                                                        @foreach($shopItem->requirements as $req)
                                                            <span class="req-item">{{ $req->item->name ?? '?' }} ×{{ $req->quantity }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    @if($locked)
                                                        <span style="color:#aaa; font-size:10px;">Заблокировано</span>
                                                    @else
                                                        <form method="POST"
                                                              action="{{ route('reputation.buy', [$page->reputation->id, $shopItem->id]) }}"
                                                              onclick="event.stopPropagation()">
                                                            @csrf
                                                            <b class="butt2 pointer"><b>
                                                                <input value="Купить" type="submit" style="width:60px">
                                                            </b></b>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="bg_l">
                                                <td class="brd2-top brd2-bt" colspan="3" style="padding: 6px 10px; color:#888;">
                                                    В магазине нет товаров.
                                                </td>
                                            </tr>
                                        @endforelse

                                        {{-- Back to reputation page --}}
                                        <tr class="bg_l"
                                            onclick="location.href='{{ route('reputation.index', $page->reputation->id) }}'"
                                            onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                            <td class="brd2-top brd2-bt" width="1%" height="28"></td>
                                            <td class="brd2-top brd2-bt">« Вернуться к репутации</td>
                                            <td class="brd2-top brd2-bt" align="right"></td>
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
                    </td>

                    <td width="16"><img src="images/d.gif" width="16" height="1"></td>

                    {{-- NPC Side Panel --}}
                    <td valign="top" width="202" height="100%">
                        <table width="240" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr height="22">
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                            <td align="center" class="tbl-usi_label-center">{{ $page->reputation->npc->name ?? $page->reputation->name }}</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    @if($page->reputation->icon)
                                        <img src="{{ asset($page->reputation->icon) }}" alt="{{ $page->reputation->name }}" width="190">
                                    @elseif($page->reputation->npc && $page->reputation->npc->icon)
                                        <img src="{{ asset($page->reputation->npc->icon) }}" alt="{{ $page->reputation->npc->name }}" width="190">
                                    @endif
                                    @if($page->reputation->description)
                                        <div class="p2v" style="padding: 4px 6px;">{{ $page->reputation->description }}</div>
                                    @endif
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
        </td>
    </tr>
    </tbody>
</table>

<script>
    @if($page->messageType === 'success' && $page->message)
        try {
            let money = parseInt('{{ $page->player->user->money }}');
            let diamond = parseInt('{{ $page->player->user->diamond }}');
            let experience = parseFloat('{{ $page->player->getPercentExp() }}');
            let lvl = parseInt('{{ $page->player->lvl }}');
            let hp = parseFloat('{{ $page->player->getPercentHp() }}');
            let mp = parseFloat('{{ $page->player->getPercentMp() }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
