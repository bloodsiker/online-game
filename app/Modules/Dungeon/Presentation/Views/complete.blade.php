<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Данж завершён') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { font-family: Tahoma; font-size: 11px; margin: 4px; }
        a { color: #000; }
        .block { border: 1px solid #ccc; padding: 6px; margin-bottom: 6px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 3px 8px; background: #555; color: #fff; text-decoration: none; font-size: 11px; cursor: pointer; border: none; }
        .log-row { padding: 2px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<div style="font-weight:bold; font-size:13px; color:#1a1; margin-bottom:6px;">
    ✔ {{ __('Данж завершён:') }} {{ $run->dungeon->name }}
</div>

<div class="block">
    <b>{{ __('Статус:') }}</b> {{ $run->status->value }}<br>
    <b>{{ __('Начат:') }}</b> {{ $run->started_at->format('d.m.Y H:i') }}<br>
    <b>{{ __('Завершён:') }}</b> {{ $run->updated_at->format('d.m.Y H:i') }}
</div>

@php
    $rewardLogs = $run->logs->where('event', 'reward_given');
@endphp

@if ($rewardLogs->isNotEmpty())
    <div class="block">
        <b>{{ __('Полученные награды:') }}</b>
        @foreach ($rewardLogs as $log)
            <div class="log-row">
                @php $p = $log->payload ?? []; @endphp
                {{ $p['type'] ?? '?' }}:
                {{ $p['amount'] ?? '' }}
                @if (!empty($p['pity_triggered']))
                    <span style="color:#a50;">[pity]</span>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="block" style="color:#888;">{{ __('Наград не получено.') }}</div>
@endif

<p><a href="{{ route('dungeon.index') }}" class="btn">{{ __('К списку данжей') }}</a></p>

</body>
</html>
