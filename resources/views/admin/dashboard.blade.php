@extends('admin.layout.base')

@section('title')
    Дашборд
@endsection

@section('body')

    <div class="row">

        {{-- Последние онлайн игроки --}}
        <div class="col-lg-6">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <div class="card-actions">
                        <i class="bx bx-user" style="font-size:20px;color:#0088cc"></i>
                    </div>
                    <h2 class="card-title">Последние онлайн игроки</h2>
                    <p class="card-subtitle">10 игроков по времени последнего входа</p>
                </header>
                <div class="card-body" style="padding:0">
                    <table class="table table-hover mb-none">
                        <thead>
                        <tr>
                            <th>Игрок</th>
                            <th>Раса</th>
                            <th width="50">Ур.</th>
                            <th>Последний онлайн</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentPlayers as $user)
                            @php $isOnline = $user->last_online_at && $user->last_online_at->diffInMinutes(now()) <= 5; @endphp
                            <tr>
                                <td>
                                    @if($isOnline)
                                        <span style="color:#46a546;font-size:10px;margin-right:4px">●</span>
                                    @else
                                        <span style="color:#aaa;font-size:10px;margin-right:4px">●</span>
                                    @endif
                                    @if($user->player)
                                        <a href="{{ route('admin.player.info', $user->player->id) }}">{{ $user->name }}</a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                </td>
                                <td>{{ $user->player?->race?->name ?? '—' }}</td>
                                <td>{{ $user->player?->lvl ?? '—' }}</td>
                                <td>
                                    <span class="text-muted small" title="{{ $user->last_online_at }}">
                                        {{ $user->last_online_at?->diffForHumans() ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Нет данных</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- Последние завершённые бои --}}
        <div class="col-lg-6">
            <section class="card card-featured card-featured-danger">
                <header class="card-header">
                    <div class="card-actions">
                        <i class="bx bx-shield-quarter" style="font-size:20px;color:#c00"></i>
                    </div>
                    <h2 class="card-title">Последние завершённые бои</h2>
                    <p class="card-subtitle">10 последних боёв со статусом «завершён»</p>
                </header>
                <div class="card-body" style="padding:0">
                    <table class="table table-hover mb-none">
                        <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Локация</th>
                            <th>Участники</th>
                            <th width="60">Рнд.</th>
                            <th>Завершён</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentBattles as $battle)
                            <tr>
                                <td>{{ $battle->id }}</td>
                                <td>{{ $battle->location?->name ?? '—' }}</td>
                                <td>
                                    @foreach($battle->detailsWithUsers as $detail)
                                        <span class="badge badge-primary">{{ $detail->user?->name ?? '?' }}</span>
                                    @endforeach
                                    @foreach($battle->detailsWithMonsters as $detail)
                                        <span class="badge badge-danger">{{ $detail->locationMonster?->monster?->name ?? '?' }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $battle->rounds }}</td>
                                <td>
                                    <span class="text-muted small" title="{{ $battle->updated_at }}">
                                        {{ $battle->updated_at?->diffForHumans() ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Нет завершённых боёв</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

@endsection
