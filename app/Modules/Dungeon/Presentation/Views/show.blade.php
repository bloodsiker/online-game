<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $page->dungeon->name }}</title>
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

        .dng-page { padding: 8px 10px; }

        .dng-window { margin-bottom: 10px; }

        .dng-desc {
            padding: 0 2px 8px;
            color: #58401f;
            line-height: 1.45;
            text-align: left;
        }

        .dng-muted { color: #857767; }

        .dng-msg {
            margin-bottom: 8px;
            text-align: center;
            font-weight: bold;
        }

        .dng-actions {
            padding-top: 10px;
            text-align: center;
        }
        .dng-actions form { display: inline; margin: 0; }
    </style>
</head>
<body>

<div class="dng-page">

    @if ($errors->any())
        <div class="dng-msg redd">{{ $errors->first('dungeon') }}</div>
    @endif

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="dng-window">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">{{ $page->dungeon->name }}</td>
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

                @if ($page->dungeon->description)
                    <div class="dng-desc">{{ $page->dungeon->description }}</div>
                @endif

                <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr class="bg_l">
                        <td class="b" width="34%" align="right" nowrap>Тир:</td>
                        <td class="b redd">{{ $page->dungeon->tier }}</td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Игроков:</td>
                        <td class="black">{{ $page->dungeon->maxPlayers === 1 ? 'Соло' : 'до ' . $page->dungeon->maxPlayers }}</td>
                    </tr>
                    <tr class="bg_l brd2-top">
                        <td class="b" align="right" nowrap>Минимальный уровень:</td>
                        <td class="b redd">{{ $page->dungeon->minLevel }}</td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Кулдаун:</td>
                        <td class="black">
                            @if ($page->dungeon->cooldownSeconds > 0)
                                <b>{{ round($page->dungeon->cooldownSeconds / 3600, 1) }} ч.</b> ({{ $page->dungeon->cooldownType }})
                            @else
                                <span class="dng-muted">нет</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg_l brd2-top">
                        <td class="b" align="right" nowrap>Таймер прохождения:</td>
                        <td class="black">
                            @if ($page->dungeon->timeLimitSeconds)
                                <b>{{ round($page->dungeon->timeLimitSeconds / 60) }} мин.</b>
                            @else
                                <span class="dng-muted">без ограничения</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Ключ:</td>
                        <td class="black">
                            @if ($page->dungeon->requiresKey)
                                <b>{{ $page->dungeon->entryKeyName ?? '-' }}</b>
                            @else
                                <span class="dng-muted">не требуется</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg_l brd2-top">
                        <td class="b" align="right" nowrap>Точка входа:</td>
                        <td class="black">
                            @if ($page->dungeon->entryLocationId)
                                локация #{{ $page->dungeon->entryLocationId }}
                            @else
                                <span class="dng-muted">любая допустимая</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Монстры:</td>
                        <td class="black">{{ $page->dungeon->monsterRespawn ? 'респавнятся' : 'не респавнятся' }}</td>
                    </tr>
                    <tr class="bg_l brd2-top">
                        <td class="b" align="right" nowrap>Смерть в данже:</td>
                        <td class="black">{{ $page->dungeon->deathBehaviorLabel }}</td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Локация после смерти:</td>
                        <td class="black">
                            @if ($page->dungeon->deathReturnLocationId)
                                локация #{{ $page->dungeon->deathReturnLocationId }}
                            @else
                                <span class="dng-muted">по умолчанию для выбранного режима</span>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="dng-actions">
                    @if ($page->activeSession && $page->activeSession->dungeonId === $page->dungeon->id)
                        @if ($page->activeSession->canReenter)
                            <form method="POST" action="{{ route('dungeon.enter', $page->dungeon->id) }}">
                                @csrf
                                <b class="butt1 pointer"><b><input value="Вернуться" type="submit" style="width: 100px;" class="redd"></b></b>
                            </form>
                        @else
                            <b class="butt1 pointer"><b><input value="В данж" type="button" onclick="location.href='{{ route('location') }}'" style="width: 100px;" class="redd"></b></b>
                        @endif
                        <form method="POST" action="{{ route('dungeon.exit') }}">
                            @csrf
                            <b class="butt1 pointer"><b><input value="Покинуть" type="submit" style="width: 100px;"></b></b>
                        </form>
                    @elseif (! $page->activeSession)
                        <form method="POST" action="{{ route('dungeon.enter', $page->dungeon->id) }}">
                            @csrf
                            <b class="butt1 pointer"><b><input value="Войти" type="submit" style="width: 100px;" class="redd"></b></b>
                        </form>
                    @endif
                    <b class="butt1 pointer"><b><input value="Назад" type="button" onclick="location.href='{{ route('dungeon.index') }}'" style="width: 100px;"></b></b>
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

</div>

</body>
</html>