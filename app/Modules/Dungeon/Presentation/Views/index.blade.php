<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Данжи</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body {
            min-height: 100%;
            margin: 0;
            color: #2f1f0b;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
        }

        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { color: #8b2f00; text-decoration: underline; }
        table.coll { border-collapse: collapse; border-spacing: 0; }

        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }

        .dng-page {
            padding: 8px;
            box-sizing: border-box;
        }

        .dng-shell {
            max-width: 980px;
            margin: 0 auto;
            border: 1px solid #b77a32;
            background: #f4dc9b;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 238, .75),
                0 2px 0 rgba(112, 73, 21, .3);
        }

        .dng-hero {
            padding: 10px 12px;
            border-bottom: 1px solid #b77a32;
            background: #e8bd67;
        }

        .dng-title {
            margin: 0;
            color: #3f2608;
            font-size: 16px;
            line-height: 1.2;
            text-shadow: 0 1px 0 rgba(255, 242, 194, .8);
        }

        .dng-subtitle {
            margin-top: 4px;
            color: #67431a;
            line-height: 1.35;
        }

        .dng-content {
            padding: 8px;
        }

        .dng-panel {
            margin-bottom: 8px;
            border: 1px solid #b9853b;
            background: #f8e6ad;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 238, .65);
        }

        .dng-panel-title {
            padding: 6px 9px;
            color: #3e2508;
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #b9853b;
            background: #e5b865;
            text-shadow: 0 1px 0 rgba(255, 242, 194, .75);
        }

        .dng-panel-body {
            padding: 8px;
        }

        .dng-alert {
            margin-bottom: 8px;
            padding: 6px 8px;
            border: 1px solid #8fb36a;
            background: #eef8d6;
            color: #2f5a19;
            font-weight: bold;
        }

        .dng-error {
            margin-bottom: 8px;
            padding: 6px 8px;
            border: 1px solid #c56f5f;
            background: #ffe1d6;
            color: #8a1f0c;
            font-weight: bold;
        }

        .active-run-banner {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 7px 8px;
            border: 1px solid #c29343;
            background: #fff2bf;
        }

        .active-run-main {
            min-width: 0;
            line-height: 1.35;
        }

        .active-run-name {
            color: #4a2b08;
            font-weight: bold;
        }

        .dng-actions {
            display: inline-flex;
            gap: 5px;
            align-items: center;
            white-space: nowrap;
        }

        .dng-btn {
            display: inline-block;
            min-height: 23px;
            padding: 4px 9px;
            color: #2f1b07 !important;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            font-weight: bold;
            line-height: 14px;
            text-align: center;
            text-decoration: none !important;
            border: 1px solid #8c5f22;
            background: #f1c36a;
            box-shadow: inset 0 1px 0 rgba(255, 252, 213, .55);
            cursor: pointer;
            box-sizing: border-box;
        }

        .dng-btn:hover {
            background: #ffd87e;
            color: #1f1003 !important;
        }

        .dng-btn-danger {
            color: #fff3dc !important;
            border-color: #7f241a;
            background: #a74735;
        }

        .dng-btn-danger:hover {
            background: #c05b46;
            color: #fff8e8 !important;
        }

        .dng-btn-enter {
            border-color: #477b28;
            background: #9fc866;
        }

        .dng-btn-enter:hover { background: #b8da7a; }

        .dng-badge {
            display: inline-block;
            min-width: 42px;
            padding: 2px 6px;
            color: #3e2508;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #a86f2d;
            background: #f1c36a;
            box-shadow: inset 0 1px 0 rgba(255, 252, 213, .55);
        }

        .dng-name {
            color: #471f00;
            font-weight: bold;
            font-size: 12px;
        }

        .dng-muted {
            color: #735a35;
            line-height: 1.35;
        }

        .dng-desc {
            margin-top: 3px;
            color: #58401f;
            line-height: 1.35;
        }

        .dng-meta {
            color: #5f431d;
            white-space: nowrap;
        }

        .dng-empty {
            padding: 12px 10px;
            color: #806c4c;
            text-align: center;
        }

        .dng-table-row:hover td {
            background: #fff4c8;
        }

        @media (max-width: 760px) {
            .active-run-banner {
                display: block;
            }

            .dng-actions {
                display: flex;
                margin-top: 6px;
            }

            .dng-actions .dng-btn,
            .dng-actions form {
                flex: 1;
            }

            .dng-actions form .dng-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="dng-page">
    <div class="dng-shell">
        <div class="dng-hero">
            <h1 class="dng-title">Данжи</h1>
            <div class="dng-subtitle">Выберите подземелье, проверьте требования и войдите в испытание.</div>
        </div>

        <div class="dng-content">
            @if (session('info'))
                <div class="dng-alert">{{ session('info') }}</div>
            @endif

            @if ($errors->has('dungeon'))
                <div class="dng-error">{{ $errors->first('dungeon') }}</div>
            @endif

            @if ($page->activeSession)
                <div class="active-run-banner">
                    <div class="active-run-main">
                        <span class="active-run-name">Вы в данже:</span> {{ $page->activeSession->dungeonName }}
                        @if ($page->activeSession->expiresAtTimestamp)
                            <span class="dng-muted">- осталось <b id="dungeon-timer"></b></span>
                            <script>
                                (function() {
                                    const exp = {{ $page->activeSession->expiresAtTimestamp }};
                                    function tick() {
                                        const el = document.getElementById('dungeon-timer');
                                        if (!el) {
                                            return;
                                        }

                                        const left = exp - Math.floor(Date.now() / 1000);
                                        if (left <= 0) {
                                            el.textContent = '00:00';
                                            return;
                                        }

                                        const m = String(Math.floor(left / 60)).padStart(2, '0');
                                        const s = String(left % 60).padStart(2, '0');
                                        el.textContent = m + ':' + s;
                                    }
                                    tick();
                                    setInterval(tick, 1000);
                                })();
                            </script>
                        @endif
                    </div>

                    <div class="dng-actions">
                        @if ($page->activeSession->canReenter)
                            <form method="POST" action="{{ route('dungeon.enter', $page->activeSession->dungeonId) }}" style="display:inline;">
                                @csrf
                                <button class="dng-btn dng-btn-enter">Вернуться в данж</button>
                            </form>
                        @else
                            <a href="{{ route('location') }}" class="dng-btn">В данж</a>
                        @endif
                        <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline;">
                            @csrf
                            <button class="dng-btn dng-btn-danger">Покинуть</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="dng-panel">
                <div class="dng-panel-title">Доступные данжи</div>
                <div class="dng-panel-body">
                    @if ($page->dungeons === [])
                        <div class="dng-empty">Нет доступных данжей.</div>
                    @else
                        <table class="coll w100 p10h p4v brd2" border="0" width="100%" style="margin-top:4px;margin-bottom:4px">
                            <colgroup>
                                <col width="70">
                                <col>
                                <col width="120">
                                <col width="160">
                                <col width="115">
                            </colgroup>
                            <tbody>
                            <tr>
                                <td class="b" align="center" title="Сложность подземелья">Тир</td>
                                <td class="b" align="center" title="Название и описание данжа">Название</td>
                                <td class="b" align="center" title="Требования к игроку или группе">Требования</td>
                                <td class="b" align="center" title="Кулдаун, таймер, ключ, точка входа и смерть">Условия</td>
                                <td class="b" align="center" title="Доступные действия">Действия</td>
                            </tr>

                            @foreach ($page->dungeons as $dungeon)
                                <tr class="dng-table-row {{ $loop->even ? 'bg_l' : '' }}">
                                    <td align="center" valign="top">
                                        <span class="dng-badge">Тир {{ $dungeon->tier }}</span>
                                    </td>
                                    <td valign="top">
                                        <div class="dng-name">{{ $dungeon->name }}</div>
                                        @if ($dungeon->description)
                                            <div class="dng-desc">{{ $dungeon->description }}</div>
                                        @else
                                            <div class="dng-muted">Описание пока не добавлено.</div>
                                        @endif
                                    </td>
                                    <td align="center" valign="top">
                                        <div class="dng-meta">{{ $dungeon->maxPlayers === 1 ? 'Соло' : 'До ' . $dungeon->maxPlayers }}</div>
                                        <div class="dng-muted">Мин. ур.: {{ $dungeon->minLevel }}</div>
                                    </td>
                                    <td align="center" valign="top">
                                        @if ($dungeon->cooldownSeconds > 0)
                                            <div class="dng-meta">КД: {{ round($dungeon->cooldownSeconds / 3600, 1) }} ч.</div>
                                        @endif
                                        @if ($dungeon->timeLimitSeconds)
                                            <div class="dng-meta">Таймер: {{ round($dungeon->timeLimitSeconds / 60) }} мин.</div>
                                        @endif
                                        @if ($dungeon->requiresKey)
                                            <div class="dng-muted">Ключ: {{ $dungeon->entryKeyName ?? '?' }}</div>
                                        @endif
                                        @if ($dungeon->entryLocationId)
                                            <div class="dng-muted">Вход: лок. #{{ $dungeon->entryLocationId }}</div>
                                        @endif
                                        <div class="dng-muted">Смерть: {{ $dungeon->deathBehaviorLabel }}</div>
                                        @if ($dungeon->deathReturnLocationId)
                                            <div class="dng-muted">Возврат: лок. #{{ $dungeon->deathReturnLocationId }}</div>
                                        @endif
                                        @if (! $dungeon->cooldownSeconds && ! $dungeon->timeLimitSeconds && ! $dungeon->requiresKey && ! $dungeon->entryLocationId && ! $dungeon->deathReturnLocationId)
                                            <div class="dng-muted">Без особых условий</div>
                                        @endif
                                    </td>
                                    <td align="center" valign="top" nowrap="">
                                        <a href="{{ route('dungeon.show', $dungeon->id) }}" class="dng-btn">Подробнее</a>
                                        @if (!$page->activeSession)
                                            <form method="POST" action="{{ route('dungeon.enter', $dungeon->id) }}" style="margin-top:4px;">
                                                @csrf
                                                <button class="dng-btn dng-btn-enter" style="width:100%;">Войти</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
