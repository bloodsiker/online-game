@extends('admin.layout.base')

@section('title')
    Молчание игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'mutes'])

    <section class="card">
        <div class="card-body">
                <div class="row pt-3">
                    <div class="col-lg-4">
                        <h6 class="text-muted mb-3">Наложить молчание</h6>
                        <form action="{{ route('admin.player.mute', $player->id) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label class="col-form-label">Где запретить общение</label>
                                <select name="scope" class="form-control" required>
                                    <option value="forum" @selected(old('scope') === 'forum')>Форум</option>
                                    <option value="chat" @selected(old('scope') === 'chat')>Игровой чат</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Продолжительность, минут</label>
                                <input type="number"
                                       name="duration_minutes"
                                       class="form-control @error('duration_minutes') is-invalid @enderror"
                                       value="{{ old('duration_minutes', 60) }}"
                                       min="1"
                                       max="525600"
                                       required>
                                @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="form-text text-muted">60 — час, 1440 — сутки, 10080 — неделя.</small>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Причина</label>
                                <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" maxlength="500">{{ old('reason') }}</textarea>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger">Наложить молчание</button>
                        </form>
                    </div>

                    <div class="col-lg-8">
                        <h6 class="text-muted mb-3">Активные ограничения</h6>
                        @forelse($activeCommunicationMutes as $activeMute)
                            <div class="alert alert-danger d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $activeMute->scope->label() }}</strong><br>
                                    До {{ $activeMute->expires_at->format('d.m.Y H:i') }}
                                    @if($activeMute->reason)<br><small>{{ $activeMute->reason }}</small>@endif
                                </div>
                                <form action="{{ route('admin.player.mute.revoke', [$player->id, $activeMute->id]) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Снять молчание досрочно?')">Снять</button>
                                </form>
                            </div>
                        @empty
                            <div class="alert alert-success">У игрока нет активных ограничений общения.</div>
                        @endforelse
                    </div>
                </div>

                <h6 class="text-muted mt-4 mb-3">История ограничений</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-none">
                        <thead>
                        <tr>
                            <th width="120">Тип</th>
                            <th width="145">Начало</th>
                            <th width="145">Окончание</th>
                            <th>Причина</th>
                            <th width="140">Назначил</th>
                            <th width="130">Статус</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($communicationMutes as $mute)
                            <tr>
                                <td>{{ $mute->scope->label() }}</td>
                                <td>{{ $mute->starts_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $mute->expires_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $mute->reason ?: '—' }}</td>
                                <td>{{ $mute->imposedBy?->name ?? ($mute->imposed_by_user_id === null ? 'Автомодерация' : '—') }}</td>
                                <td>
                                    @if($mute->isActive())
                                        <span class="badge badge-danger">Активно</span>
                                    @elseif($mute->revoked_at)
                                        <span class="badge badge-warning" title="Снял: {{ $mute->revokedBy?->name ?? '—' }}">Снято</span>
                                    @else
                                        <span class="badge badge-default">Истекло</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">История пуста</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </section>

@endsection
