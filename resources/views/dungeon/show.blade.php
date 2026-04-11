<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $dungeon->name }}</title>
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

<div style="font-weight:bold; font-size:13px; margin-bottom:6px;">{{ $dungeon->name }}</div>

@if ($errors->any())
    <div style="color:red; margin-bottom:6px;">{{ $errors->first('dungeon') }}</div>
@endif

<div class="block">
    <b>Игроков:</b> {{ $dungeon->max_players === 1 ? 'Соло' : 'до ' . $dungeon->max_players }}<br>
    <b>Тир:</b> {{ $dungeon->tier }} &bull; <b>Мин. уровень:</b> {{ $dungeon->min_level }}<br>
    @if ($dungeon->cooldown_seconds > 0)
        <b>Кулдаун:</b> {{ round($dungeon->cooldown_seconds / 3600, 1) }} ч. ({{ $dungeon->cooldown_type->value }})<br>
    @endif
    @if ($dungeon->time_limit_seconds)
        <b>Таймер:</b> {{ round($dungeon->time_limit_seconds / 60) }} мин.<br>
    @endif
    @if ($dungeon->requiresKey())
        <b>Ключ:</b> {{ $dungeon->entryItem?->name ?? '—' }}<br>
    @endif
    @if ($dungeon->entry_location_id)
        <b>Точка входа:</b> локация #{{ $dungeon->entry_location_id }}<br>
    @endif
    @if ($dungeon->monster_respawn)
        <b>Монстры:</b> респавнятся<br>
    @else
        <b>Монстры:</b> не респавнятся<br>
    @endif
    @if ($dungeon->description)
        <div style="margin-top:4px;">{{ $dungeon->description }}</div>
    @endif
</div>

@if ($activeSession && $activeSession->dungeon_id === $dungeon->id)
    <p>
        <a href="{{ route('location') }}" class="btn">Вернуться в данж</a>
        <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline;">
            @csrf
            <button class="btn" style="background:#a33;">Покинуть данж</button>
        </form>
    </p>
@elseif (!$activeSession)
    <form method="POST" action="{{ route('dungeon.enter', $dungeon->id) }}">
        @csrf
        <button type="submit" class="btn" style="background:#3a3;">Войти в данж</button>
    </form>
@endif

<p><a href="{{ route('dungeon.index') }}">← Назад</a></p>

</body>
</html>
