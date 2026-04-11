<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Данжи</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { font-family: Tahoma; font-size: 11px; margin: 4px; }
        a { color: #000; }
        .dungeon-item { border: 1px solid #ccc; padding: 6px; margin-bottom: 6px; background: #f9f9f9; }
        .dungeon-name { font-weight: bold; font-size: 12px; }
        .dungeon-meta { color: #666; margin-top: 2px; }
        .tier-badge { display: inline-block; padding: 1px 5px; background: #3a3; color: #fff; border-radius: 2px; font-size: 10px; }
        .btn { display: inline-block; padding: 3px 8px; background: #555; color: #fff; text-decoration: none; font-size: 11px; cursor: pointer; border: none; }
        .btn:hover { background: #333; color: #fff; }
        .btn-danger { background: #a33; }
        .btn-danger:hover { background: #722; }
        .active-run-banner { background: #ffe; border: 1px solid #cc0; padding: 6px; margin-bottom: 8px; }
        .errors { background: #fdd; border: 1px solid #c33; padding: 5px; margin-bottom: 6px; }
    </style>
</head>
<body>

@if (session('info'))
    <div style="background:#dfd; border:1px solid #393; padding:5px; margin-bottom:6px;">{{ session('info') }}</div>
@endif

@if ($errors->has('dungeon'))
    <div class="errors">{{ $errors->first('dungeon') }}</div>
@endif

@if ($activeSession)
    <div class="active-run-banner">
        <b>Вы в данже:</b> {{ $activeSession->dungeon->name }}
        @if ($activeSession->expires_at)
            — осталось <b id="dungeon-timer"></b>
            <script>
                (function() {
                    const exp = {{ $activeSession->expires_at->timestamp }};
                    function tick() {
                        const left = exp - Math.floor(Date.now()/1000);
                        if (left <= 0) { document.getElementById('dungeon-timer').textContent = '00:00'; return; }
                        const m = String(Math.floor(left/60)).padStart(2,'0');
                        const s = String(left%60).padStart(2,'0');
                        document.getElementById('dungeon-timer').textContent = m+':'+s;
                    }
                    tick(); setInterval(tick, 1000);
                })();
            </script>
        @endif
        &nbsp;
        <a href="{{ route('location') }}" class="btn">В данж</a>
        <form method="POST" action="{{ route('dungeon.exit') }}" style="display:inline;">
            @csrf
            <button class="btn btn-danger">Покинуть</button>
        </form>
    </div>
@endif

<div style="font-weight:bold; font-size:13px; margin-bottom:6px;">Данжи</div>

@forelse ($dungeons as $dungeon)
    <div class="dungeon-item">
        <div class="dungeon-name">
            <span class="tier-badge">Тир {{ $dungeon->tier }}</span>
            {{ $dungeon->name }}
        </div>
        <div class="dungeon-meta">
            Игроков: {{ $dungeon->max_players === 1 ? 'Соло' : 'до ' . $dungeon->max_players }} &bull;
            Мин. уровень: {{ $dungeon->min_level }}
            @if ($dungeon->cooldown_seconds > 0)
                &bull; КД: {{ round($dungeon->cooldown_seconds / 3600, 1) }} ч.
            @endif
            @if ($dungeon->time_limit_seconds)
                &bull; Таймер: {{ round($dungeon->time_limit_seconds / 60) }} мин.
            @endif
            @if ($dungeon->requiresKey())
                &bull; Ключ: {{ $dungeon->entryItem?->name ?? '?' }}
            @endif
            @if ($dungeon->entry_location_id)
                &bull; Вход: лок. #{{ $dungeon->entry_location_id }}
            @endif
        </div>
        @if ($dungeon->description)
            <div style="margin-top:3px; color:#444;">{{ $dungeon->description }}</div>
        @endif
        <div style="margin-top:5px;">
            <a href="{{ route('dungeon.show', $dungeon->id) }}" class="btn">Подробнее</a>
            @if (!$activeSession)
                <form method="POST" action="{{ route('dungeon.enter', $dungeon->id) }}" style="display:inline;">
                    @csrf
                    <button class="btn" style="background:#3a3;">Войти</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <p>Нет доступных данжей.</p>
@endforelse

</body>
</html>
