@extends('admin.layout.base')

@section('title')
    Репутации игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'reputations'])

    <section class="card">
        <div class="card-body">
                <div class="table-responsive pt-3">
                    <table class="table table-hover table-bordered mb-none">
                        <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="55"></th>
                            <th>Репутация</th>
                            <th width="130">Очки</th>
                            <th width="220">Текущий уровень</th>
                            <th width="165">Последнее задание</th>
                            <th width="165">Последнее подношение</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($playerReputations as $row)
                            @php
                                $playerReputation = $row['record'];
                                $reputation = $playerReputation->reputation;
                                $currentTier = $row['currentTier'];
                                $icon = $reputation->icon;
                            @endphp
                            <tr style="vertical-align: middle">
                                <td>{{ $reputation->id }}</td>
                                <td class="text-center">
                                    @if($icon)
                                        <img src="{{ str_starts_with($icon, 'http://') || str_starts_with($icon, 'https://') || str_starts_with($icon, '/') ? $icon : asset($icon) }}"
                                             style="width:40px;height:40px;object-fit:contain" alt="">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.reputation.info', $reputation->id) }}">
                                        {{ $reputation->name }}
                                    </a>
                                </td>
                                <td><strong>{{ number_format($playerReputation->points, 0, '', ' ') }}</strong></td>
                                <td>
                                    @if($currentTier)
                                        {{ $currentTier->medal_name ?: 'Уровень от '.number_format($currentTier->min_points, 0, '', ' ') }}
                                    @else
                                        <span class="text-muted">Не открыт</span>
                                    @endif
                                </td>
                                <td>{{ $playerReputation->last_completed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td>{{ $playerReputation->last_offering_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">У игрока нет репутаций</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </section>

@endsection
