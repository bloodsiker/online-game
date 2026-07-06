<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал хранилища клана</title>
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
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .action-put  { color: #2a7a2a; font-weight: bold; }
        .action-take { color: #a02020; font-weight: bold; }
        .pg, .pg td { color: #8D2616; height: 17px; text-align: center; vertical-align: middle; padding-left: 1px; padding-right: 1px; }
        .pg-act { margin: 1px; text-align: center; background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-inact { margin: 1px; text-align: center; background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-act_lnk { color: #FFF3D2 !important; font-size: 9px; font-weight: bold; }
        .pg-inact_lnk { color: #C50000 !important; font-size: 9px; font-weight: bold; }
    </style>

    {!! $playerStatsScript !!}
    {!! $itemTooltipScript !!}

    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('clan.warehouse._tabs', ['activeTab' => 'logs'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px">

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" nowrap=""><b>Клан:</b> {{ $clan->name }}</td>
                    <td align="right" nowrap=""><b>Хранилище:</b> {{ $clanWarehouse->name }}</td>
                </tr>
                </tbody>
            </table>

            <br>

            <table class="coll w100 brd2-all" border="0">
                <colgroup>
                    <col width="130">
                    <col width="50">
                    <col class="p6h">
                    <col width="60">
                    <col width="130">
                </colgroup>
                <tbody>
                <tr height="17" class="bg_l">
                    <td class="brd2 p6h" align="center"><b>Дата</b></td>
                    <td class="brd2 p6h" align="center"><b>Игрок</b></td>
                    <td class="brd2 p6h" align="center"><b>Предмет</b></td>
                    <td class="brd2 p6h" align="center"><b>К-во</b></td>
                    <td class="brd2 p6h" align="center"><b>Действие</b></td>
                </tr>
                @forelse($logs as $log)
                    <tr height="17" class="brd2-top">
                        <td class="brd2 p6h" align="center" style="font-size:11px;color:#888;">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            {{ $log->user->name }}
                        </td>
                        <td class="brd2 p6h" align="left">
                            <span data-id="{{ $log->item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" style="display:inline-block; position:relative;">
                                <img src="{{ $log->item->itemInfo->image }}" style="width:20px;height:20px;vertical-align:middle;" alt="">
                            </span>
                            {{ $log->item->itemInfo->name }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            {{ $log->count }}
                        </td>
                        <td class="brd2 p6h" align="center">
                            @if($log->action->isPut())
                                <span class="action-put">{{ $log->action->label() }}</span>
                            @else
                                <span class="action-take">{{ $log->action->label() }}</span>
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
                    $window = 5;
                    $pgFrom = max(1, $cur - $window);
                    $pgTo   = min($last, $cur + $window);
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

                        <td width="1%" style="text-align:right" nowrap="">
                            @if($logs->onFirstPage())
                                <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                            @else
                                <a href="{{ $logs->previousPageUrl() }}">
                                    <img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                </a>
                            @endif
                            <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                            @if($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}">
                                    <img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая">
                                </a>
                            @else
                                <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="Следующая">
                            @endif
                        </td>
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
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i': window.parent.sendDataToGame('{{ route('backpack') }}'); break;
            case 'c': window.parent.sendDataToGame('{{ route('character') }}'); break;
            case ' ': window.parent.sendDataToGame('{{ route('location') }}'); break;
            default: return;
        }
        event.preventDefault();
    });
</script>

</body>
</html>
