<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Казна клана</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 12px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .action-deposit  { color: #2a7a2a; font-weight: bold; }
        .action-withdraw { color: #a02020; font-weight: bold; }
        .pg, .pg td { color: #8D2616; height: 17px; text-align: center; vertical-align: middle; padding-left: 1px; padding-right: 1px; }
        .pg-act { margin: 1px; text-align: center; background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-inact { margin: 1px; text-align: center; background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-act_lnk { color: #FFF3D2 !important; font-size: 9px; font-weight: bold; }
        .pg-inact_lnk { color: #C50000 !important; font-size: 9px; font-weight: bold; }
        .money-input { width: 100px; border: 1px solid #b08060; padding: 2px 4px; background: #FFE7C5; }
        .money-input::-webkit-outer-spin-button,
        .money-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .money-input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @php
                $btnLeft1   = 'img/bg/btn/btn-left1.gif';
                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                $btnRight1  = 'img/bg/btn/btn-right1.gif';
                $btnLeft2   = 'img/bg/btn/btn-left2.gif';
                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                $btnRight2  = 'img/bg/btn/btn-right2.gif';

                $activeTab = 'treasury';

                $tabs = [
                    'treasury' => ['route' => route('clan.treasury', ['id' => $clanWarehouse->id]), 'label' => 'Казна'],
                    'clan'     => ['route' => route('clan.member'), 'label' => 'Клан'],
                ];
            @endphp
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
                <tbody>
                <tr height="21">
                    @foreach($tabs as $key => $tab)
                        @php $isActive = ($activeTab ?? '') === $key; @endphp
                        <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                        <td width="2%" align="center" style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                            <a href="{{ $tab['route'] }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tab['label'] }}</a>
                        </td>
                        <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                        @if($key === 'treasury')
                            <td></td>
                        @endif
                    @endforeach
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px">

            @if(session('message'))
                <div style="padding: 4px 8px; margin-bottom: 8px; background: #fff3cc; border: 1px solid #db9f73; color: #461c0b;">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Info row --}}
            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" nowrap="">
                        <b>Клан:</b> {{ $clan->name }}
                        &nbsp;
                        <b>Казна:</b>
                        <b class="redd">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($clan->treasury) }}
                        </b>
                    </td>
                    <td align="right" nowrap="">
                        <b>Ваши монеты:</b>
                        <b class="redd">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($membership->user->money) }}
                        </b>
                        &nbsp;&nbsp;&nbsp;<b class="redd">
                            <img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($membership->user->diamond) }}
                        </b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            {{-- Actions --}}
            <table class="coll w100 brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td class="brd2 p6h" style="padding: 8px 10px;" width="50%">
                        <div style="text-align: center;">
                            <b>Пополнить казну</b><br><br>
                            <form action="{{ route('clan.treasury', ['id' => $clanWarehouse->id]) }}" method="post">
                                @csrf
                                <input type="hidden" name="action" value="deposit">
                                <input type="number" name="amount" min="1" value="" class="money-input" placeholder="Сумма">
                                &nbsp;
                                <b class="butt1 pointer"><b><input type="submit" value="Внести" class="pointer btn_1"></b></b>
                            </form>
                        </div>
                    </td>
                    @if($canWithdraw)
                    <td class="brd2 p6h" style="padding: 8px 10px;" width="50%">
                        <div style="text-align: center;">
                            <b>Снять из казны</b><br><br>
                            <form action="{{ route('clan.treasury', ['id' => $clanWarehouse->id]) }}" method="post">
                                @csrf
                                <input type="hidden" name="action" value="withdraw">
                                <input type="number" name="amount" min="1" value="" class="money-input" placeholder="Сумма">
                                &nbsp;
                                <b class="butt1 pointer"><b><input type="submit" value="Снять" class="pointer btn_1"></b></b>
                            </form>
                        </div>
                    </td>
                    @else
                    <td class="brd2 p6h" style="padding: 8px 10px; color: #999; text-align: center;" width="50%">
                        <b>Снять из казны</b><br><br>
                        У вас нет прав снимать деньги из казны клана.
                    </td>
                    @endif
                </tr>
                </tbody>
            </table>

            <br>

            {{-- Logs --}}
            <table class="coll w100 brd2-all" border="0">
                <colgroup>
                    <col width="130">
                    <col>
                    <col width="110">
                    <col width="110">
                    <col width="90">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2 p6h" align="center"><b>Дата</b></td>
                    <td class="brd2 p6h" align="center"><b>Игрок</b></td>
                    <td class="brd2 p6h" align="center"><b>Сумма</b></td>
                    <td class="brd2 p6h" align="center"><b>Баланс казны</b></td>
                    <td class="brd2 p6h" align="center"><b>Действие</b></td>
                </tr>
                @forelse($logs as $log)
                    <tr height="17" class="brd2-top">
                        <td class="brd2 p6h" align="center" style="font-size:11px;color:#888;">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="brd2 p6h" align="center">{{ $log->user->name }}</td>
                        <td class="brd2 p6h" align="center">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($log->amount) }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($log->balance_after) }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            @if($log->action === 'deposit')
                                <span class="action-deposit">Пополнение</span>
                            @else
                                <span class="action-withdraw">Снятие</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" align="center" style="padding: 10px;">Журнал пуст</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            @if($logs->hasPages())
                @php
                    $cur    = $logs->currentPage();
                    $last   = $logs->lastPage();
                    $pgFrom = max(1, $cur - 5);
                    $pgTo   = min($last, $cur + 5);
                @endphp
                <table border="0" cellpadding="0" cellspacing="0" class="pg w100" style="margin-top: 6px;">
                    <tbody>
                    <tr>
                        <td class="b" width="10"><nobr>Страницы:&nbsp;</nobr></td>
                        @if($pgFrom > 1)
                            <td class="pg-inact"><a href="{{ $logs->url(1) }}" class="pg-inact_lnk">1</a></td>
                            @if($pgFrom > 2)<td class="b" style="padding:0 2px">…</td>@endif
                        @endif
                        @for($p = $pgFrom; $p <= $pgTo; $p++)
                            <td class="{{ $p === $cur ? 'pg-act' : 'pg-inact' }}">
                                <a href="{{ $logs->url($p) }}" class="{{ $p === $cur ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
                            </td>
                        @endfor
                        @if($pgTo < $last)
                            @if($pgTo < $last - 1)<td class="b" style="padding:0 2px">…</td>@endif
                            <td class="pg-inact"><a href="{{ $logs->url($last) }}" class="pg-inact_lnk">{{ $last }}</a></td>
                        @endif
                    </tr>
                    </tbody>
                </table>
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

<script>
    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
