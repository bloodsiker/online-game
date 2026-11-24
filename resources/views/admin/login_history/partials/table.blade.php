@php
    $deviceLabels = ['desktop' => 'Компьютер', 'mobile' => 'Телефон', 'tablet' => 'Планшет', 'bot' => 'Бот', 'unknown' => '—'];
@endphp
<div class="table-responsive">
    <table class="table table-hover table-bordered mb-none">
        <thead>
        <tr>
            <th width="55">ID</th>
            <th width="135">Дата</th>
            @if($showUser)
                <th width="160">Игрок</th>
            @endif
            <th width="125">IP</th>
            <th width="170">Страна, город</th>
            <th>Браузер</th>
            <th>ОС</th>
            <th width="95">Устройство</th>
            <th width="90">Язык</th>
            <th width="130">Часовой пояс</th>
            <th width="90">Экран</th>
            <th>Сеть</th>
        </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr style="vertical-align: middle">
                <td>{{ $log->id }}</td>
                <td class="small text-muted">
                    {{ $log->created_at?->format('d.m.Y H:i:s') }}
                    @if($log->remembered)
                        <span class="badge badge-default" title="Вход по «запомнить меня»">авто</span>
                    @endif
                </td>
                @if($showUser)
                    <td>
                        @if($log->user?->player_id)
                            <a href="{{ route('admin.player.info', $log->user->player_id) }}">{{ $log->user->name }}</a>
                        @else
                            {{ $log->user?->name ?? '—' }}
                        @endif
                        <small class="text-muted">#{{ $log->user_id }}</small>
                    </td>
                @endif
                <td>
                    @if($log->ip_address)
                        <a href="{{ route('admin.login_history.index', ['ip' => $log->ip_address]) }}" title="Все входы с этого IP">{{ $log->ip_address }}</a>
                    @else
                        —
                    @endif
                </td>
                <td class="small">
                    @if($log->country_code)
                        <a href="{{ route('admin.login_history.index', ['country' => $log->country_code]) }}" title="Все входы из этой страны">{{ $log->placeLabel() }}</a>
                    @else
                        —
                    @endif
                </td>
                <td title="{{ $log->user_agent }}">{{ $log->browser ? $log->browser.' '.$log->browser_version : '—' }}</td>
                <td>{{ $log->os ? $log->os.' '.$log->os_version : '—' }}</td>
                <td>
                    <span class="badge {{ $log->device_type === 'bot' ? 'badge-warning' : 'badge-info' }}">{{ $deviceLabels[$log->device_type] ?? '—' }}</span>
                </td>
                <td class="small">{{ $log->language ?? ($log->accept_language ? \Illuminate\Support\Str::before($log->accept_language, ',') : '—') }}</td>
                <td class="small">{{ $log->timezone ?? '—' }}</td>
                <td class="small">{{ $log->screenLabel() }}</td>
                <td class="small">{{ $log->connectionLabel() }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $showUser ? 12 : 11 }}" class="text-center text-muted">Входов пока нет</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
