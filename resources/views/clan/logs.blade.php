<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан — Логи</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .grnn  { color: #114d01 !important; }
        .dbgl  { background-color: #FFE7C5; }
        .brd, .brd td { border: 1px solid #C49485; }
        .w100 { width: 100%; }

        .pg, .pg td {
            color: #8D2616;
            height: 17px;
            text-align: center;
            vertical-align: center !important;
            padding-left: 1px;
            padding-right: 1px;
        }
        .pg-inact {
            margin: 1px; padding: 1px 0 0 2px; text-align: center;
            background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center;
            height: 17px; width: 17px;
        }
        .pg-act {
            margin: 1px; padding: 1px 0 0 2px; text-align: center;
            background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center;
            height: 17px; width: 17px;
        }
        .pg-inact_lnk { color: #C50000 !important; font-size: 9px; font-weight: bold; }
        .pg-act_lnk   { color: #FFF3D2 !important; font-size: 9px; font-weight: bold; }

        table.log-table {
            border-top: #db9f73 1px solid;
            border-left: #db9f73 1px solid;
            border-collapse: separate !important;
        }
        .log-table td, .log-table th {
            height: 15px;
            padding: 4px;
            color: #631c0b;
            border-right: #db9f73 1px solid;
            border-bottom: #db9f73 1px solid;
            vertical-align: top;
        }
        .log-table th { color: #955c4a; white-space: nowrap; }
        .log-table tr.bg_l td { background-image: url(/img/bg/info/bg_l.gif); }
    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.logs'])

<table class="coll" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody><tr height="22">
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                <td align="center" class="tbl-usi_label-center">Логи клана</td>
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                            </tr></tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 0;">

                        {{-- Фильтр --}}
                        <form method="get" action="{{ route('clan.logs') }}">
                            <table border="0" cellpadding="0" cellspacing="0" class="pg" style="margin-bottom:6px;width:60%">
                                <tbody><tr>
                                    <td class="b" style="padding: 3px 6px; white-space:nowrap">Тип события:</td>
                                    <td style="padding:2px 4px">
                                        <select name="action" class="dbgl brd" style="font-family:Tahoma;font-size:11px;color:#631c0b">
                                            <option value="">— все —</option>
                                            @foreach($actions as $action)
                                                <option value="{{ $action->value }}" {{ $filterAction === $action->value ? 'selected' : '' }}>
                                                    {{ $action->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="b" style="padding: 3px 6px; white-space:nowrap">Игрок:</td>
                                    <td style="padding:2px 4px">
                                        <input type="text" name="player" value="{{ $filterUser }}" class="dbgl brd" style="width:128px;font-family:Tahoma;font-size:11px">
                                    </td>
                                    <td style="padding:2px 6px">
                                        <b class="butt2 pointer"><b><input value="Применить" type="submit" style="width:80px"></b></b>
                                    </td>
                                    @if($filterAction || $filterUser)
                                        <td style="padding:2px 6px">
                                            <b class="butt2 pointer"><b><a href="{{ route('clan.logs') }}" style="color:#8b0000"><input value="Сбросить" type="button" onclick="location.href='{{ route('clan.logs') }}'" style="width:80px"></a></b></b>
                                        </td>
                                    @endif
                                </tr></tbody>
                            </table>
                        </form>

                        {{-- Пагинация сверху --}}
                        @include('clan.partials.logs-pagination', ['paginator' => $logs])

                        {{-- Таблица логов --}}
                        <table class="log-table coll" style="margin-top:4px;width:60%">
                            <thead>
                            <tr>
                                <th width="120">Дата</th>
                                <th width="140">Тип события</th>
                                <th width="110">Игрок</th>
                                <th>Описание</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                                    <td nowrap>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                    <td nowrap><b>{{ $log->action->label() }}</b></td>
                                    <td nowrap>{{ $log->user?->name ?? '—' }}</td>
                                    <td>{{ $log->details }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" align="center" style="padding:10px;color:#888">Записей не найдено</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        {{-- Пагинация снизу --}}
                        @include('clan.partials.logs-pagination', ['paginator' => $logs])

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