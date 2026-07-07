<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банк</title>
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
        .action-deposit      { color: #2a7a2a; font-weight: bold; }
        .action-withdraw     { color: #a02020; font-weight: bold; }
        .action-transfer_out { color: #a05000; font-weight: bold; }
        .action-transfer_in  { color: #005a8e; font-weight: bold; }
        .account-number { font-family: monospace; font-size: 13px; letter-spacing: 1px; color: #461c0b; font-weight: bold; }
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
                $btnLeft1 = 'img/bg/btn/btn-left1.gif'; $btnCenter1 = 'img/bg/btn/btn-cent1.gif'; $btnRight1 = 'img/bg/btn/btn-right1.gif';
                $btnLeft2 = 'img/bg/btn/btn-left2.gif'; $btnCenter2 = 'img/bg/btn/btn-cent2.gif'; $btnRight2 = 'img/bg/btn/btn-right2.gif';
            @endphp
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
                <tbody>
                <tr height="21">
                    <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a href="{{ route('bank', ['id' => request('id')]) }}" class="btn_2">Банк</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>
                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a href="{{ route('bank.deposits', ['id' => request('id')]) }}" class="btn_1">Депозит</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
                    <td></td>
                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a href="{{ route('location') }}" class="btn_1">Выход</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
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

            {{-- Balance info --}}
            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" nowrap="">
                        <b>Счёт №:</b> <span class="account-number">{{ $page->bankAccount }}</span>
                    </td>
                    <td align="center" nowrap="">
                        <b>Кошелёк:</b>
                        <b class="redd">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($page->money) }}
                        </b>
                    </td>
                    <td align="right" nowrap="">
                        <b>На счёте в банке:</b>
                        <b class="redd">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($page->bankBalance) }}
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
                    <td class="brd2 p6h" style="padding: 8px 10px;" width="33%">
                        <div style="text-align: center;">
                            <b>Положить в банк</b><br><br>
                            <form action="{{ route('bank') }}" method="post">
                                @csrf
                                <input type="hidden" name="action" value="deposit">
                                <input type="number" name="amount" min="1" value="" class="money-input" placeholder="Сумма">
                                &nbsp;
                                <b class="butt1 pointer"><b><input type="submit" value="Положить" class="pointer btn_1"></b></b>
                            </form>
                        </div>
                    </td>
                    <td class="brd2 p6h" style="padding: 8px 10px;" width="33%">
                        <div style="text-align: center;">
                            <b>Снять из банка</b><br><br>
                            <form action="{{ route('bank') }}" method="post">
                                @csrf
                                <input type="hidden" name="action" value="withdraw">
                                <input type="number" name="amount" min="1" value="" class="money-input" placeholder="Сумма">
                                &nbsp;
                                <b class="butt1 pointer"><b><input type="submit" value="Снять" class="pointer btn_1"></b></b>
                            </form>
                        </div>
                    </td>
                    <td class="brd2 p6h" style="padding: 8px 10px;" width="34%">
                        <div style="text-align: center;">
                            <b>Перевод</b> <span style="color:#888;font-size:11px;">(комиссия 1%)</span><br><br>
                            <form id="transfer-form" action="{{ route('bank') }}" method="post">
                                @csrf
                                <input type="hidden" name="action" value="transfer_out">
                                <input type="text" id="transfer-account" name="account" class="money-input" style="width:90px;" placeholder="№ счёта" maxlength="12">
                                <input type="number" id="transfer-amount" name="amount" min="1" value="" class="money-input" placeholder="Сумма">
                                &nbsp;
                                <b class="butt1 pointer"><b><input type="button" id="transfer-btn" value="Перевести" class="pointer btn_1"></b></b>
                            </form>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            {{-- Logs --}}
            <table class="coll w100 brd2-all" border="0">
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2 p6h" align="center"><b>Дата</b></td>
                    <td class="brd2 p6h" align="center"><b>Сумма</b></td>
                    <td class="brd2 p6h" align="center"><b>Баланс счёта</b></td>
                    <td class="brd2 p6h" align="center"><b>Получатель</b></td>
                    <td class="brd2 p6h" align="center"><b>Действие</b></td>
                </tr>
                @forelse($page->logs as $log)
                    <tr height="17" class="brd2-top">
                        <td class="brd2 p6h" align="center" style="font-size:11px;color:#888;">
                            {{ $log['createdAt'] }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($log['amount']) }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                            {{ format_money($log['balanceAfter']) }}
                        </td>
                        <td class="brd2 p6h" align="center" style="color:#666;">
                            {{ $log['relatedUserName'] }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            <span class="action-{{ $log['actionValue'] }}">{{ $log['actionLabel'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" align="center" style="padding: 10px;">История операций пуста</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            @if($page->pagination['hasPages'])
                @php
                    $cur    = $page->pagination['currentPage'];
                    $last   = $page->pagination['lastPage'];
                    $pgFrom = $page->pagination['pageFrom'];
                    $pgTo   = $page->pagination['pageTo'];
                @endphp
                <table border="0" cellpadding="0" cellspacing="0" class="pg w100" style="margin-top: 6px;">
                    <tbody>
                    <tr>
                        <td class="b" width="10"><nobr>Страницы:&nbsp;</nobr></td>
                        @if($pgFrom > 1)
                            <td class="pg-inact"><a href="{{ $page->pagination['urls'][1] }}" class="pg-inact_lnk">1</a></td>
                            @if($pgFrom > 2)<td class="b" style="padding:0 2px">…</td>@endif
                        @endif
                        @for($p = $pgFrom; $p <= $pgTo; $p++)
                            <td class="{{ $p === $cur ? 'pg-act' : 'pg-inact' }}">
                                <a href="{{ $page->pagination['urls'][$p] }}" class="{{ $p === $cur ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
                            </td>
                        @endfor
                        @if($pgTo < $last)
                            @if($pgTo < $last - 1)<td class="b" style="padding:0 2px">…</td>@endif
                            <td class="pg-inact"><a href="{{ $page->pagination['urls'][$last] }}" class="pg-inact_lnk">{{ $last }}</a></td>
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
    document.getElementById('transfer-btn').addEventListener('click', function () {
        var account = document.getElementById('transfer-account').value.trim();
        var amount  = parseInt(document.getElementById('transfer-amount').value, 10);

        if (!account) { window.parent.systemInfo('Введите номер счёта.'); return; }
        if (!amount || amount <= 0) { window.parent.systemInfo('Введите корректную сумму.'); return; }

        var commission = Math.ceil(amount * 0.01);
        var total      = amount + commission;
        var form       = document.getElementById('transfer-form');

        fetch('{{ route('bank.lookup') }}?account=' + encodeURIComponent(account))
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok) {
                    window.parent.systemInfo(res.data.error || 'Ошибка', 'Ошибка');
                } else {
                    var msg =
                        'Получатель: <b>' + res.data.name + '</b><br>' +
                        'Счёт: <b>' + account + '</b><br>' +
                        'Получит: <b>' + amount.toLocaleString('ru') + '</b><br>' +
                        'Комиссия (1%): <b>' + commission.toLocaleString('ru') + '</b><br>' +
                        'Спишется с вас: <b>' + total.toLocaleString('ru') + '</b>';
                    window.parent.systemConfirm(msg, 'Подтверждение перевода', form);
                }
            })
            .catch(function () { window.parent.systemInfo('Ошибка соединения.'); });
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
