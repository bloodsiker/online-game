<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $page->dungeon->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { font-family: Tahoma; font-size: 11px; margin: 4px; }
        a { color: #000; }
        .block { border: 1px solid #ccc; padding: 6px; margin-bottom: 6px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 3px 8px; background: #555; color: #fff; text-decoration: none; font-size: 11px; cursor: pointer; border: none; }
        .btn:hover { background: #333; color: #fff; }
    </style>
</head>
<body>

<div style="font-weight:bold; font-size:13px; margin-bottom:6px;">{{ $page->dungeon->name }}</div>

@if ($errors->any())
    <div style="color:red; margin-bottom:6px;">{{ $errors->first('dungeon') }}</div>
@endif

<div class="block">
    <b>Игроков:</b> {{ $page->dungeon->maxPlayers === 1 ? 'Соло' : 'до ' . $page->dungeon->maxPlayers }}<br>
    <b>Тир:</b> {{ $page->dungeon->tier }} &bull; <b>Мин. уровень:</b> {{ $page->dungeon->minLevel }}<br>
    @if ($page->dungeon->cooldownSeconds > 0)
        <b>Кулдаун:</b> {{ round($page->dungeon->cooldownSeconds / 3600, 1) }} ч. ({{ $page->dungeon->cooldownType }})<br>
    @endif
    @if ($page->dungeon->timeLimitSeconds)
        <b>Таймер:</b> {{ round($page->dungeon->timeLimitSeconds / 60) }} мин.<br>
    @endif
    @if ($page->dungeon->requiresKey)
        <b>Ключ:</b> {{ $page->dungeon->entryKeyName ?? '—' }}<br>
    @endif
    @if ($page->dungeon->entryLocationId)
        <b>Точка входа:</b> локация #{{ $page->dungeon->entryLocationId }}<br>
    @endif
    @if ($page->dungeon->monsterRespawn)
        <b>Монстры:</b> респавнятся<br>
    @else
        <b>Монстры:</b> не респавнятся<br>
    @endif
    @if ($page->dungeon->description)
        <div style="margin-top:4px;">{{ $page->dungeon->description }}</div>
    @endif
</div>

@if ($page->activeSession && $page->activeSession->dungeonId === $page->dungeon->id)
    <p>
        <a href="{{ route('location') }}" class="btn">Вернуться в данж</a>
        <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline;">
            @csrf
            <button class="btn" style="background:#a33;">Покинуть данж</button>
        </form>
    </p>
@elseif (!$page->activeSession)
    <form method="POST" action="{{ route('dungeon.enter', $page->dungeon->id) }}">
        @csrf
        <button type="submit" class="btn" style="background:#3a3;">Войти в данж</button>
    </form>
@endif

<p><a href="{{ route('dungeon.index') }}">← Назад</a></p>

</body>
</html>
