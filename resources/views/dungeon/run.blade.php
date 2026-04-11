<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Данж — {{ $run->dungeon->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { font-family: Tahoma; font-size: 11px; margin: 4px; }
        a { color: #000; }
        .block { border: 1px solid #ccc; padding: 6px; margin-bottom: 6px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 3px 8px; background: #555; color: #fff; text-decoration: none; font-size: 11px; cursor: pointer; border: none; }
        .btn:hover { background: #333; color: #fff; }
        .btn-danger { background: #933; }
        .btn-danger:hover { background: #611; }
        .participant { padding: 2px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<div style="font-weight:bold; font-size:13px; margin-bottom:6px;">{{ $run->dungeon->name }}</div>

@if ($errors->any())
    <div style="color:red; margin-bottom:6px;">{{ $errors->first('dungeon') }}</div>
@endif

<div class="block">
    <b>Статус:</b> {{ $run->status->value }}<br>
    <b>Начат:</b> {{ $run->started_at->format('d.m H:i') }}
    @if ($run->expires_at)
        &bull; <b>Время:</b>
        @php $secsLeft = max(0, now()->diffInSeconds($run->expires_at, false)); @endphp
        <span id="timer" style="color:{{ $secsLeft < 60 ? 'red' : '#333' }}">
            {{ gmdate('i:s', $secsLeft) }}
        </span>
    @endif
</div>

@if ($currentFloor)
    <div class="block">
        <b>Этаж {{ $currentFloor->floor->floor_number }}</b>
        @if ($currentFloor->floor_expires_at)
            &bull; Осталось:
            @php $floorSecs = max(0, now()->diffInSeconds($currentFloor->floor_expires_at, false)); @endphp
            <span style="color:{{ $floorSecs < 30 ? 'red' : '#333' }}">{{ gmdate('i:s', $floorSecs) }}</span>
        @endif
        <br>

        @if ($currentFloor->battle_id)
            {{-- Active battle --}}
            <p>
                <a href="{{ route('fight', $currentFloor->battle_id) }}" class="btn">Сражаться</a>
            </p>

            {{-- Next floor choices (shown only if battle is won) --}}
            @if ($currentFloor->status->value === 'completed')
                @php $nextFloors = $currentFloor->floor->nextBranches; @endphp
                @if ($nextFloors->isNotEmpty())
                    <form method="POST" action="{{ route('dungeon.advance', $run->id) }}">
                        @csrf
                        <p><b>Выберите следующий этаж:</b></p>
                        @foreach ($nextFloors as $branch)
                            <button type="submit" name="next_floor_id" value="{{ $branch->to_floor_id }}" class="btn">
                                Этаж {{ $branch->toFloor->floor_number }}
                                @if ($branch->label)
                                    — {{ $branch->label }}
                                @endif
                            </button>
                        @endforeach
                    </form>
                @else
                    {{-- No next floor = last floor, complete run --}}
                    <form method="POST" action="{{ route('dungeon.advance', $run->id) }}">
                        @csrf
                        <input type="hidden" name="next_floor_id" value="0">
                        <button type="submit" class="btn">Завершить данж</button>
                    </form>
                @endif
            @endif
        @endif
    </div>
@endif

<div class="block">
    <b>Участники:</b>
    @foreach ($run->participants as $p)
        <div class="participant">
            {{ $p->user->name }}
            ({{ $p->user->player->level }} лвл)
            — {{ $p->status->value }}
        </div>
    @endforeach
</div>

<form method="POST" action="{{ route('dungeon.abandon', $run->id) }}" style="margin-top:6px;">
    @csrf
    <button type="submit" class="btn btn-danger"
        onclick="return confirm('Покинуть данж без награды?')">Покинуть данж</button>
</form>

@if ($run->expires_at)
<script>
(function () {
    var endTime = {{ $run->expires_at->timestamp }};
    var el = document.getElementById('timer');
    if (!el) return;
    setInterval(function () {
        var left = Math.max(0, endTime - Math.floor(Date.now() / 1000));
        var m = Math.floor(left / 60);
        var s = left % 60;
        el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        el.style.color = left < 60 ? 'red' : '#333';
        if (left <= 0) location.reload();
    }, 1000);
})();
</script>
@endif

</body>
</html>