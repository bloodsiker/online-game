<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $page->dungeon->name }}</title>
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
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }

        .dng-page {
            padding: 8px;
            box-sizing: border-box;
        }

        .dng-shell {
            max-width: 880px;
            margin: 0 auto;
            border: 1px solid #b77a32;
            background: #f4dc9b;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 238, .75),
                0 2px 0 rgba(112, 73, 21, .3);
        }

        .dng-hero {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
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

        .dng-error {
            margin-bottom: 8px;
            padding: 6px 8px;
            border: 1px solid #c56f5f;
            background: #ffe1d6;
            color: #8a1f0c;
            font-weight: bold;
        }

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

        .dng-badge-soft {
            background: #fff0bd;
        }

        .dng-desc {
            color: #58401f;
            line-height: 1.45;
        }

        .dng-muted {
            color: #735a35;
        }

        .dng-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 8px;
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

        .dng-btn-enter {
            border-color: #477b28;
            background: #9fc866;
        }

        .dng-btn-enter:hover { background: #b8da7a; }

        .dng-btn-danger {
            color: #fff3dc !important;
            border-color: #7f241a;
            background: #a74735;
        }

        .dng-btn-danger:hover {
            background: #c05b46;
            color: #fff8e8 !important;
        }

        .dng-back {
            display: inline-block;
            margin-top: 2px;
            color: #6f2b00 !important;
            font-weight: bold;
        }

        .dng-table-label {
            color: #4c310f;
            font-weight: bold;
            white-space: nowrap;
        }

        .dng-table-value {
            color: #35230c;
        }

        @media (max-width: 640px) {
            .dng-hero,
            .dng-actions {
                display: block;
            }

            .dng-actions .dng-btn,
            .dng-actions form,
            .dng-actions form .dng-btn {
                display: block;
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
<div class="dng-page">
    <div class="dng-shell">
        <div class="dng-hero">
            <div>
                <h1 class="dng-title">{{ $page->dungeon->name }}</h1>
                <div class="dng-subtitle">
                    <span class="dng-badge">Тир {{ $page->dungeon->tier }}</span>
                    <span class="dng-badge dng-badge-soft">{{ $page->dungeon->maxPlayers === 1 ? 'Соло' : 'До ' . $page->dungeon->maxPlayers }}</span>
                    <span class="dng-badge dng-badge-soft">Мин. ур. {{ $page->dungeon->minLevel }}</span>
                </div>
            </div>
            <a href="{{ route('dungeon.index') }}" class="dng-back">← К списку</a>
        </div>

        <div class="dng-content">
            @if ($errors->any())
                <div class="dng-error">{{ $errors->first('dungeon') }}</div>
            @endif

            <div class="dng-panel">
                <div class="dng-panel-title">Сведения о данже</div>
                <div class="dng-panel-body">
                    <table class="coll w100 p10h p4v brd2" border="0" width="100%">
                        <tbody>
                        <tr>
                            <td class="b" align="center" width="34%">Параметр</td>
                            <td class="b" align="center">Значение</td>
                        </tr>
                        <tr class="bg_l">
                            <td class="dng-table-label">Игроков</td>
                            <td class="dng-table-value">{{ $page->dungeon->maxPlayers === 1 ? 'Соло' : 'до ' . $page->dungeon->maxPlayers }}</td>
                        </tr>
                        <tr>
                            <td class="dng-table-label">Минимальный уровень</td>
                            <td class="dng-table-value">{{ $page->dungeon->minLevel }}</td>
                        </tr>
                        <tr class="bg_l">
                            <td class="dng-table-label">Кулдаун</td>
                            <td class="dng-table-value">
                                @if ($page->dungeon->cooldownSeconds > 0)
                                    {{ round($page->dungeon->cooldownSeconds / 3600, 1) }} ч. ({{ $page->dungeon->cooldownType }})
                                @else
                                    <span class="dng-muted">Нет</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="dng-table-label">Таймер прохождения</td>
                            <td class="dng-table-value">
                                @if ($page->dungeon->timeLimitSeconds)
                                    {{ round($page->dungeon->timeLimitSeconds / 60) }} мин.
                                @else
                                    <span class="dng-muted">Без ограничения</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg_l">
                            <td class="dng-table-label">Ключ</td>
                            <td class="dng-table-value">
                                @if ($page->dungeon->requiresKey)
                                    {{ $page->dungeon->entryKeyName ?? '-' }}
                                @else
                                    <span class="dng-muted">Не требуется</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="dng-table-label">Точка входа</td>
                            <td class="dng-table-value">
                                @if ($page->dungeon->entryLocationId)
                                    локация #{{ $page->dungeon->entryLocationId }}
                                @else
                                    <span class="dng-muted">Любая допустимая</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg_l">
                            <td class="dng-table-label">Монстры</td>
                            <td class="dng-table-value">{{ $page->dungeon->monsterRespawn ? 'респавнятся' : 'не респавнятся' }}</td>
                        </tr>
                        <tr>
                            <td class="dng-table-label">Смерть в данже</td>
                            <td class="dng-table-value">{{ $page->dungeon->deathBehaviorLabel }}</td>
                        </tr>
                        <tr class="bg_l">
                            <td class="dng-table-label">Локация после смерти</td>
                            <td class="dng-table-value">
                                @if ($page->dungeon->deathReturnLocationId)
                                    локация #{{ $page->dungeon->deathReturnLocationId }}
                                @else
                                    <span class="dng-muted">По умолчанию для выбранного режима</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dng-panel">
                <div class="dng-panel-title">Описание</div>
                <div class="dng-panel-body">
                    @if ($page->dungeon->description)
                        <div class="dng-desc">{{ $page->dungeon->description }}</div>
                    @else
                        <div class="dng-muted">Описание пока не добавлено.</div>
                    @endif
                </div>
            </div>

            <div class="dng-actions">
                <a href="{{ route('dungeon.index') }}" class="dng-btn">Назад</a>

                @if ($page->activeSession && $page->activeSession->dungeonId === $page->dungeon->id)
                    @if ($page->activeSession->canReenter)
                        <form method="POST" action="{{ route('dungeon.enter', $page->dungeon->id) }}" style="display:inline;">
                            @csrf
                            <button class="dng-btn dng-btn-enter">Вернуться в данж</button>
                        </form>
                    @else
                        <a href="{{ route('location') }}" class="dng-btn dng-btn-enter">Вернуться в данж</a>
                    @endif
                    <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline;">
                        @csrf
                        <button class="dng-btn dng-btn-danger">Покинуть данж</button>
                    </form>
                @elseif (!$page->activeSession)
                    <form method="POST" action="{{ route('dungeon.enter', $page->dungeon->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="dng-btn dng-btn-enter">Войти в данж</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
</body>
</html>
