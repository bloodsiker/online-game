@extends('admin.layout.base')

@section('title')
    Игрок: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'edit'])

    <section class="card">
        <div class="card-body">
                <div class="alert alert-info mb-0 mt-3">
                    <b>Текущее местоположение:</b>
                    Карта: {{ $player->user?->currentLocation?->map?->name ?? 'неизвестна' }};
                    локация: {{ $player->user?->location_id ?? '—' }}
                    @if($player->user?->currentLocation?->name)
                        — {{ $player->user->currentLocation->name }}
                    @endif
                </div>
                <form action="{{ route('admin.player.info', $player->id) }}" method="post">
                    {{ csrf_field() }}
                    <div class="row pt-3 pb-3">

                        <div class="col-lg-3">
                            <h6 class="text-muted mb-3">Основное</h6>
                            <div class="form-group">
                                <label class="col-form-label">Уровень</label>
                                <input type="number" class="form-control" name="lvl" value="{{ $player->lvl }}">
                            </div>
                            <div class="mb-3">
                                @if($player->lvl < $maxPlayerLevel)
                                    <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalPlayerLevelUp">
                                        Повысить до уровня
                                    </a>
                                @else
                                    <span class="badge badge-success">Достигнут максимальный уровень</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Опыт</label>
                                <input type="number" class="form-control" name="exp" value="{{ $player->exp }}">
                                @php $expPercent = (float) $player->getPercentExp(); @endphp
                                <div class="progress mt-2" style="height: 14px;" title="До следующего уровня: {{ number_format(max(0, $player->exp_up - $player->exp), 0, ',', ' ') }}">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $expPercent }}%; background-color: #e0a526;" aria-valuenow="{{ $expPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="form-text text-muted">{{ $expPercent }}% уровня · до следующего: {{ number_format(max(0, $player->exp_up - $player->exp), 0, ',', ' ') }} из {{ number_format($player->exp_up, 0, ',', ' ') }}</small>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Коэффициент опыта</label>
                                <input
                                    type="number"
                                    class="form-control @error('experience_multiplier') is-invalid @enderror"
                                    name="experience_multiplier"
                                    value="{{ old('experience_multiplier', $player->experience_multiplier) }}"
                                    min="0"
                                    max="9999.9999"
                                    step="0.0001"
                                    required
                                >
                                <small class="form-text text-muted">1.0 — обычный опыт, 1.5 — бонус 50%, 0.5 — штраф 50%.</small>
                                @error('experience_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Свободные очки</label>
                                <input type="number" class="form-control" name="free_stats" value="{{ $player->free_stats }}">
                            </div>
                            <h6 class="text-muted mb-3 mt-3">Валюта</h6>
                            <div class="form-group">
                                <label class="col-form-label">Монеты</label>
                                <input type="number" class="form-control" name="money" value="{{ $player->user->money }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Алмазы</label>
                                <input type="number" class="form-control" name="diamond" value="{{ $player->user->diamond }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <h6 class="text-muted mb-3">HP / MP <small>(только просмотр)</small></h6>
                            <div class="form-group">
                                <label class="col-form-label">HP сейчас</label>
                                <input type="number" class="form-control" name="hp_now" value="{{ $player->hp_now }}" readonly disabled>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">HP макс</label>
                                <input type="number" class="form-control" name="hp_max" value="{{ $player->hp_max }}" readonly disabled>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">MP сейчас</label>
                                <input type="number" class="form-control" name="mp_now" value="{{ $player->mp_now }}" readonly disabled>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">MP макс</label>
                                <input type="number" class="form-control" name="mp_max" value="{{ $player->mp_max }}" readonly disabled>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Мин урон</label>
                                <input type="number" step="0.01" class="form-control" name="min_dmg" value="{{ $player->min_dmg }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Макс урон</label>
                                <input type="number" step="0.01" class="form-control" name="max_dmg" value="{{ $player->max_dmg }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <h6 class="text-muted mb-3">Характеристики</h6>
                            <div class="form-group">
                                <label class="col-form-label">Сила</label>
                                <input type="number" step="0.01" class="form-control" name="strength" value="{{ $player->strength }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Ловкость</label>
                                <input type="number" step="0.01" class="form-control" name="agility" value="{{ $player->agility }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Интуиция</label>
                                <input type="number" step="0.01" class="form-control" name="intuition" value="{{ $player->intuition }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Мудрость</label>
                                <input type="number" step="0.01" class="form-control" name="wisdom" value="{{ $player->wisdom }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Интеллект</label>
                                <input type="number" step="0.01" class="form-control" name="intelligence" value="{{ $player->intelligence }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Уворот</label>
                                <input type="number" class="form-control" name="dodge" value="{{ $player->dodge }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Крит. удар</label>
                                <input type="number" class="form-control" name="critical" value="{{ $player->critical }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <h6 class="text-muted mb-3">Информация</h6>
                            <p><strong>Аккаунт:</strong> {{ $player->user?->name }}</p>
                            <p><strong>Email:</strong> {{ $player->user?->email }}</p>
                            <p><strong>Раса:</strong> {{ $player->race?->name }}</p>
                            <p><strong>Победы:</strong> {{ $player->victory }}</p>
                            <p><strong>Смерти:</strong> {{ $player->death }}</p>
                            <p><strong>Тайные знания:</strong> {{ $secretKnowledgePoints }}</p>
                            <p class="mb-1"><strong>Изученные пособия боссов:</strong></p>
                            @forelse($studiedManuals as $manual)
                                <div class="small">• {{ $manual->boss_name }} <span class="text-muted">({{ $manual->required_knowledge }} зн.{{ $manual->studied_at ? ', '.\Illuminate\Support\Carbon::parse($manual->studied_at)->format('d.m.Y') : '' }})</span></div>
                            @empty
                                <div class="small text-muted">нет</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-12">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.players') }}" class="btn btn-success">Назад</a>
                        </div>
                    </div>
                </form>
        </div>
    </section>

    {{-- Модалка: естественное последовательное повышение уровня --}}
    @if($player->lvl < $maxPlayerLevel)
        <div id="modalPlayerLevelUp" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
            <section class="card">
                <form action="{{ route('admin.player.level_up', $player->id) }}" method="post">
                    <header class="card-header"><h2 class="card-title">Повысить уровень персонажа</h2></header>
                    <div class="card-body">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label>Целевой уровень</label>
                            <input type="number"
                                   class="form-control @error('target_level') is-invalid @enderror"
                                   name="target_level"
                                   value="{{ old('target_level', $player->lvl + 1) }}"
                                   min="{{ $player->lvl + 1 }}"
                                   max="{{ $maxPlayerLevel }}"
                                   required>
                            @error('target_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Доступно: от {{ $player->lvl + 1 }} до {{ $maxPlayerLevel }}. За каждый уровень будут начислены расовые характеристики и свободные очки.
                            </small>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary">Повысить уровень</button>
                            <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                        </div>
                    </footer>
                </form>
            </section>
        </div>
    @endif

@endsection
