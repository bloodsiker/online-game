@extends('admin.layout.base')

@section('title')
    Темница игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'detention'])

    <section class="card">
        <div class="card-body">
                <div class="row pt-3">
                    <div class="col-lg-4">
                        <h6 class="text-muted mb-3">Поместить в Темницу</h6>
                        @if($prisons->isEmpty())
                            <div class="alert alert-warning">Сначала создайте структуру «Темница» и назначьте ей локацию.</div>
                        @else
                            <form action="{{ route('admin.player.detain', $player->id) }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>Темница</label>
                                    <select name="prison_structure_id" class="form-control" required>
                                        @foreach($prisons as $prison)
                                            <option value="{{ $prison->id }}" @selected(old('prison_structure_id') == $prison->id)>{{ $prison->name }} — {{ $prison->location?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Срок, минут</label>
                                    <input type="number" name="duration_minutes" id="detain-duration" class="form-control" value="{{ old('duration_minutes', 60) }}" min="1" max="525600" @required(! old('indefinite'))>
                                    <small class="form-text text-muted">60 — час, 1440 — сутки, 10080 — неделя.</small>
                                    <div class="checkbox mt-1"><label><input type="checkbox" name="indefinite" value="1" @checked(old('indefinite')) onchange="var d=document.getElementById('detain-duration');d.disabled=this.checked;d.required=!this.checked;"> Бессрочно (до освобождения администратором, без выкупа)</label></div>
                                </div>
                                <div class="form-group">
                                    <label>Штраф за весь срок, монет</label>
                                    <input type="number" name="fine_money" class="form-control" min="0" value="{{ old('fine_money', 0) }}">
                                </div>
                                <div class="form-group">
                                    <label>Штраф за весь срок, бриллиантов</label>
                                    <input type="number" name="fine_diamond" class="form-control" min="0" step="0.01" value="{{ old('fine_diamond', 0) }}">
                                    <small class="form-text text-muted">Ноль в обеих валютах — выкуп недоступен.</small>
                                </div>
                                <div class="form-group">
                                    <label>Причина наказания</label>
                                    <textarea name="reason" class="form-control" rows="3" maxlength="1000" required>{{ old('reason') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">Поместить в Темницу</button>
                            </form>
                        @endif
                    </div>
                    <div class="col-lg-8">
                        <h6 class="text-muted mb-3">Текущий статус</h6>
                        @if($activeDetention)
                            <div class="alert alert-danger">
                                <strong>Находится в Темнице</strong> — {{ $activeDetention->prison?->name }}<br>
                                Освобождение: {{ $activeDetention->releaseLabel() }}<br>
                                Причина: {{ $activeDetention->reason }}<br>
                                Штраф за весь срок: {{ number_format((int) $activeDetention->fine_money, 0, '', ' ') }} монет, {{ number_format((float) $activeDetention->fine_diamond, 2, '.', ' ') }} бриллиантов.
                            </div>
                            <form action="{{ route('admin.player.release_detention', $player->id) }}" method="post" onsubmit="return confirm('Освободить игрока из Темницы досрочно?')">
                                @csrf
                                <button type="submit" class="btn btn-success">Освободить досрочно</button>
                            </form>
                        @else
                            <div class="alert alert-success">Игрок не отбывает наказание в Темнице.</div>
                        @endif
                    </div>
                </div>
                <h6 class="text-muted mt-4 mb-3">История заключений</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-none">
                        <thead><tr><th>Темница</th><th>Причина</th><th>Начало</th><th>Фактическое освобождение</th><th>Отбыто, мин.</th><th>Назначил</th><th>Статус</th></tr></thead>
                        <tbody>
                        @forelse($detentionHistory as $detention)
                            <tr>
                                <td>{{ $detention->prison?->name ?? $detention->prison_name }}</td>
                                <td>{{ $detention->reason }}</td>
                                <td>{{ $detention->starts_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $detention->actualReleaseAt()?->format('d.m.Y H:i') ?? 'бессрочно' }}</td>
                                <td>{{ $detention->servedMinutes() }}</td>
                                <td>{{ $detention->imposedBy?->name ?? $detention->imposed_by_name ?? '—' }}</td>
                                <td>@if($detention->isActive())<span class="badge badge-danger">Активно</span>@elseif($detention->released_at)<span class="badge badge-success">Завершено</span>@else<span class="badge badge-default">Срок истёк</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">История пуста</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </section>

@endsection
