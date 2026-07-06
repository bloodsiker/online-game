<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подземелья</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            color: #955c4a;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            background: url({{ asset('img/bg/bgg.gif') }});
        }

        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { color: #8b2f00; text-decoration: underline; }

        /* Вертикальные отступы ячеек, как p4v на проде */
        .p4v, .p4v td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .black, .black * { color: #2f1f0b; }
        .grn { color: #1e7a00; }

        .dng-page { padding: 8px 10px; }

        .dng-window { margin-bottom: 10px; }

        .dng-name {
            color: #471f00;
            font-size: 12px;
        }

        .dng-desc {
            margin-top: 3px;
            color: #58401f;
            line-height: 1.35;
        }

        .dng-muted { color: #857767; }

        .dng-msg {
            margin-bottom: 8px;
            text-align: center;
            font-weight: bold;
        }

        .dng-empty {
            padding: 10px;
            text-align: center;
            color: #857767;
        }
    </style>
</head>
<body>

<div class="dng-page">

    @if (session('info'))
        <div class="dng-msg grn">{{ session('info') }}</div>
    @endif

    @if ($errors->has('dungeon'))
        <div class="dng-msg redd">{{ $errors->first('dungeon') }}</div>
    @endif

    {{-- ======== ТЕКУЩИЙ ПОХОД ======== --}}
    @if ($page->activeSession)
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="dng-window">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                <td class="tbl-shp-sml tt" valign="top" align="center">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr height="22">
                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                            <td align="center" class="tbl-usi-hdr mbg">Текущий поход</td>
                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top" style="padding: 10px 10px 12px;">
                    <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr class="bg_l">
                            <td class="b" width="30%" align="right" nowrap>Вы в подземелье:</td>
                            <td class="b black">{{ $page->activeSession->dungeonName }}</td>
                            <td width="240" rowspan="2" align="center">
                                @if ($page->activeSession->canReenter)
                                    <form method="POST" action="{{ route('dungeon.enter', $page->activeSession->dungeonId) }}" style="display:inline; margin:0;">
                                        @csrf
                                        <b class="butt1 pointer"><b><input value="Вернуться" type="submit" style="width: 100px;" class="redd"></b></b>
                                    </form>
                                @else
                                    <b class="butt1 pointer"><b><input value="В данж" type="button" onclick="location.href='{{ route('location') }}'" style="width: 100px;" class="redd"></b></b>
                                @endif
                                <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline; margin:0;">
                                    @csrf
                                    <b class="butt1 pointer"><b><input value="Покинуть" type="submit" style="width: 100px;"></b></b>
                                </form>
                            </td>
                        </tr>
                        <tr class="brd2-top">
                            <td class="b" align="right" nowrap>Осталось времени:</td>
                            <td class="b redd">
                                @if ($page->activeSession->expiresAtTimestamp)
                                    <span id="dungeon-timer">--:--</span>
                                @else
                                    без ограничения
                                @endif
                            </td>
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

        @if ($page->activeSession->expiresAtTimestamp)
            <script>
                (function () {
                    var exp = {{ $page->activeSession->expiresAtTimestamp }};
                    function tick() {
                        var el = document.getElementById('dungeon-timer');
                        if (!el) return;

                        var left = exp - Math.floor(Date.now() / 1000);
                        if (left <= 0) {
                            el.textContent = '00:00';
                            return;
                        }

                        var m = String(Math.floor(left / 60)).padStart(2, '0');
                        var s = String(left % 60).padStart(2, '0');
                        el.textContent = m + ':' + s;
                    }
                    tick();
                    setInterval(tick, 1000);
                })();
            </script>
        @endif
    @endif

    {{-- ======== ДОСТУПНЫЕ ПОДЗЕМЕЛЬЯ ======== --}}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="dng-window">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">Доступные подземелья</td>
                        <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 10px 10px 12px;">
                @if ($page->dungeons === [])
                    <div class="dng-empty">Нет доступных подземелий.</div>
                @else
                    <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr>
                            <td class="b" width="50" align="center">Тир</td>
                            <td class="b">Название</td>
                            <td class="b" width="110" align="center">Требования</td>
                            <td class="b" width="170" align="center">Условия</td>
                            <td class="b" width="130" align="center">Действия</td>
                        </tr>
                        @foreach ($page->dungeons as $dungeon)
                            <tr class="{{ $loop->odd ? 'bg_l' : '' }}">
                                <td class="brd2-top brd2-bt b redd" align="center">{{ $dungeon->tier }}</td>
                                <td class="brd2-top brd2-bt">
                                    <a href="{{ route('dungeon.show', $dungeon->id) }}" class="dng-name" title="Подробнее о подземелье"><b>{{ $dungeon->name }}</b></a>
                                    @if ($dungeon->description)
                                        <div class="dng-desc">{{ $dungeon->description }}</div>
                                    @endif
                                </td>
                                <td class="brd2-top brd2-bt" align="center">
                                    <div class="b">{{ $dungeon->maxPlayers === 1 ? 'Соло' : 'До ' . $dungeon->maxPlayers . ' игроков' }}</div>
                                    <div>Мин. уровень: <b class="redd">{{ $dungeon->minLevel }}</b></div>
                                </td>
                                <td class="brd2-top brd2-bt" align="center">
                                    @if ($dungeon->cooldownSeconds > 0)
                                        <div>Кулдаун: <b>{{ round($dungeon->cooldownSeconds / 3600, 1) }} ч.</b></div>
                                    @endif
                                    @if ($dungeon->timeLimitSeconds)
                                        <div>Таймер: <b>{{ round($dungeon->timeLimitSeconds / 60) }} мин.</b></div>
                                    @endif
                                    @if ($dungeon->requiresKey)
                                        <div>Ключ: <b>{{ $dungeon->entryKeyName ?? '?' }}</b></div>
                                    @endif
                                    <div class="dng-muted">Смерть: {{ $dungeon->deathBehaviorLabel }}</div>
                                    @if (! $dungeon->cooldownSeconds && ! $dungeon->timeLimitSeconds && ! $dungeon->requiresKey)
                                        <div class="dng-muted">Без особых условий</div>
                                    @endif
                                </td>
                                <td class="brd2-top brd2-bt" align="center">
                                    @if (! $page->activeSession)
                                        <form method="POST" action="{{ route('dungeon.enter', $dungeon->id) }}" style="margin:0 0 4px;">
                                            @csrf
                                            <b class="butt1 pointer"><b><input value="Войти" type="submit" style="width: 100px;" class="redd"></b></b>
                                        </form>
                                    @endif
                                    <b class="butt1 pointer"><b><input value="Подробнее" type="button" onclick="location.href='{{ route('dungeon.show', $dungeon->id) }}'" style="width: 100px;"></b></b>
                                </td>
                            </tr>
                        @endforeach
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

</div>

</body>
</html>