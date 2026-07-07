<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банк — Вклады</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 12px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .redd, .redd * { color: #BA0000 !important; }
        .tbl-usi-hdr { background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        .brd { border: 1px solid #b08060; }
        .dbgl { background: #FFE7C5; color: #461c0b; }
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
                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a href="{{ route('bank', ['id' => request('id')]) }}" class="btn_1">Банк</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
                    <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
                        <a href="{{ route('bank.deposits', ['id' => request('id')]) }}" class="btn_2">Депозит</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>
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

            {{-- Кошелёк --}}
            <table class="coll w100 p6h brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l" height="22">
                    <td align="left" nowrap="">
                        <b>У Вас:</b>
                        &nbsp;<b class="redd"><span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }}</b>
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            {{-- Блок «Депозит» --}}
                        <p>Владелец банка готов занять у Вас средства на длительный срок под выгодный процент.
                            Проценты начисляются за каждый день, но к вкладу не прибавляются до его окончания.
                            Если Вы заберёте свой вклад до окончания оговорённого срока, то не получите проценты.
                            Пополнять вклады нельзя.</p>

                        <p><b>Доступные вклады:</b></p>
                        <table border="0" class="coll w100 brd2-all">
                            <tbody>
                            <tr height="17" class="bg_l">
                                <td align="center" class="brd2-top brd2 p6h" width="300px">Название</td>
                                <td align="center" class="brd2-top brd2 p6h">Срок окончания</td>
                                <td align="center" class="brd2-top brd2 p6h">% в день</td>
                                <td align="center" class="brd2-top brd2 p6h">Мин. сумма</td>
                                <td align="center" class="brd2-top brd2 p6h">Макс. сумма</td>
                                <td align="center" class="brd2-top brd2 p6h">Действие</td>
                            </tr>
                            @foreach($page->terms as $days => $term)
                                <tr height="17" align="center" class="brd2-top brd2">
                                    <td align="left" width="60" class="brd2-top brd2 p6h">
                                        <b>{{ $term['label'] }}</b>
                                    </td>
                                    <td class="p6h">
                                        {{ now()->addDays($days)->locale('ru')->translatedFormat('j F Y H:i') }}
                                    </td>
                                    <td class="p6h">
                                        {{ $term['percent'] }}%
                                    </td>
                                    <td nowrap="nowrap" class="p6h">
                                        <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($term['min']) }}
                                    </td>
                                    <td nowrap="nowrap" class="p6h">
                                        <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($term['max']) }}
                                    </td>
                                    <td class="p6h">
                                        @if($page->hasDepositFor($days))
                                            <b class="redd">У вас уже есть такой депозит!</b>
                                        @else
                                            <form method="post" action="{{ route('bank.deposits', ['id' => request('id')]) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="open">
                                                <input type="hidden" name="term" value="{{ $days }}">
                                                <b class="butt2 pointer"><b><input value="Сделать депозит" type="submit" onclick="if(document._submit)return false;document._submit=true;"></b></b>&nbsp;
                                                <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span> &nbsp;
                                                <input type="text" name="amount" maxlength="9" class="brd dbgl" style="width:70px;text-align:center" onkeypress="return /[0-9]/.test(String.fromCharCode(event.which || event.keyCode))">
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <p><b>Ваши вклады:</b></p>
                        <table border="0" class="coll w100 brd2-all">
                            <tbody>
                            <tr height="17" class="bg_l">
                                <td align="center" class="brd2-top brd2 p6h" width="300px">Название</td>
                                <td align="center" class="brd2-top brd2 p6h">Срок окончания</td>
                                <td align="center" class="brd2-top brd2 p6h">% в день</td>
                                <td align="center" class="brd2-top brd2 p6h">Сумма вклада</td>
                                <td align="center" class="brd2-top brd2 p6h">Накопленные проценты</td>
                                <td align="center" class="brd2-top brd2 p6h">Действие</td>
                            </tr>
                            @forelse($page->openDeposits as $deposit)
                                <tr height="17" align="center" class="brd2-top brd2">
                                    <td align="center" class="brd2-top brd2 p6h">{{ $page->terms[$deposit->term_days]['label'] ?? 'На '.$deposit->term_days.' дней' }}</td>
                                    <td align="center" class="brd2-top brd2 p6h">{{ $deposit->matures_at->locale('ru')->translatedFormat('j F Y H:i') }}</td>
                                    <td align="center" class="brd2-top brd2 p6h">{{ $deposit->percent }}%</td>
                                    <td align="center" class="brd2-top brd2 p6h">
                                        <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($deposit->amount) }}
                                    </td>
                                    <td align="center" class="brd2-top brd2 p6h">
                                        <span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($deposit->accruedInterest()) }}
                                    </td>
                                    <td align="center" class="brd2-top brd2 p6h">
                                        <form method="post" action="{{ route('bank.deposits', ['id' => request('id')]) }}" id="form_close_deposit{{ $deposit->id }}">
                                            @csrf
                                            <input type="hidden" name="action" value="claim">
                                            <input type="hidden" name="deposit_id" value="{{ $deposit->id }}">
                                            @if($deposit->isMatured())
                                                <b class="butt2 pointer"><b><input value="Забрать вклад" type="submit" onclick="if(document._submit)return false;document._submit=true;"></b></b>
                                            @else
                                                <b class="butt2 pointer"><b><input value="Отменить депозит" type="submit" onclick="return window.parent.systemConfirm('Возвращается только сумма вклада без накопленных процентов!', 'Отмена депозита', document.getElementById('form_close_deposit{{ $deposit->id }}'));"></b></b>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr height="17" align="center" class="brd2-top brd2">
                                    <td colspan="6" align="center" class="brd2-top brd2 p6h" style="padding: 8px;">У вас нет активных вкладов</td>
                                </tr>
                            @endforelse
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
    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>