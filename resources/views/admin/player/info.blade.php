@extends('admin.layout.base')

@section('title')
    Игрок: {{ $player->user?->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-edit" href="#tab-edit" data-bs-toggle="tab">Редактирование</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-backpack" href="#tab-backpack" data-bs-toggle="tab">
                                    Рюкзак <span class="badge badge-primary">{{ $backpack->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-skills" href="#tab-skills" data-bs-toggle="tab">
                                    Навыки <span class="badge badge-primary">{{ $player->skills->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-reputations" href="#tab-reputations" data-bs-toggle="tab">
                                    Репутации <span class="badge badge-primary">{{ $playerReputations->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-mutes" href="#tab-mutes" data-bs-toggle="tab">
                                    Молчание
                                    @if($activeCommunicationMutes->isNotEmpty())
                                        <span class="badge badge-danger">{{ $activeCommunicationMutes->count() }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- РЕДАКТИРОВАНИЕ --}}
                            <div id="tab-edit" class="tab-pane active">
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
                                            <h6 class="text-muted mb-3">HP / MP</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">HP сейчас</label>
                                                <input type="number" class="form-control" name="hp_now" value="{{ $player->hp_now }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">HP макс</label>
                                                <input type="number" class="form-control" name="hp_max" value="{{ $player->hp_max }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">MP сейчас</label>
                                                <input type="number" class="form-control" name="mp_now" value="{{ $player->mp_now }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">MP макс</label>
                                                <input type="number" class="form-control" name="mp_max" value="{{ $player->mp_max }}">
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

                            {{-- РЮКЗАК --}}
                            <div id="tab-backpack" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalBackpackAdd">Добавить предмет</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Название</th>
                                                <th>Тип</th>
                                                <th width="70">Кол-во</th>
                                                <th width="80">Надето</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($backpack as $slot)
                                                <tr style="vertical-align: middle" @if($slot->equipped) class="table-success" @endif>
                                                    <td>{{ $slot->id }}</td>
                                                    <td>
                                                        @if($slot->item?->itemInfo?->image)
                                                            <img src="{{ $slot->item->itemInfo->image }}" style="width:36px" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $slot->item?->itemInfo?->name ?? '—' }}</td>
                                                    <td>{{ $slot->item?->itemInfo?->type?->label() ?? '—' }}</td>
                                                    <td>{{ $slot->count }}</td>
                                                    <td>
                                                        @if($slot->equipped)
                                                            <span class="badge badge-success">Да</span>
                                                        @else
                                                            <span class="badge badge-default">Нет</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.player.backpack.delete', [$player->id, $slot->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Рюкзак пуст</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- НАВЫКИ --}}
                            <div id="tab-skills" class="tab-pane">
                                <div class="table-responsive pt-3">
                                    <table class="table table-hover table-bordered mb-none">
                                        <thead>
                                        <tr>
                                            <th width="50">ID</th>
                                            <th>Навык</th>
                                            <th width="80">Уровень</th>
                                            <th width="120">Опыт</th>
                                            <th width="120">До след. ур.</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($player->skills as $playerSkill)
                                            <tr style="vertical-align: middle">
                                                <td>{{ $playerSkill->id }}</td>
                                                <td>{{ $playerSkill->skill?->name ?? '—' }}</td>
                                                <td>{{ $playerSkill->lvl }}</td>
                                                <td>{{ $playerSkill->exp }} / {{ $playerSkill->exp_up }}</td>
                                                <td>
                                                    @php $pct = $playerSkill->exp_diff > 0 ? round($playerSkill->exp * 100 / $playerSkill->exp_up) : 100; @endphp
                                                    <div class="progress" style="margin-bottom:0;height:16px;">
                                                        <div class="progress-bar" style="width:{{ $pct }}%">{{ $pct }}%</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Нет навыков</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- РЕПУТАЦИИ --}}
                            <div id="tab-reputations" class="tab-pane">
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

                            {{-- МОЛЧАНИЕ В ФОРУМЕ И ЧАТЕ --}}
                            <div id="tab-mutes" class="tab-pane">
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
                                                <td>{{ $mute->imposedBy?->name ?? '—' }}</td>
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

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

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

    {{-- Модалка: добавить предмет в рюкзак --}}
    <div id="modalBackpackAdd" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.player.backpack.add', $player->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в рюкзак</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="backpack-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>Количество</label>
                        <input type="number" class="form-control" name="count" value="1" min="1">
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    function formatBackpackItem(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '<span style="display:inline-block;width:24px;height:24px;margin-right:6px;"></span>';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#backpack-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalBackpackAdd'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatBackpackItem,
        templateSelection: formatBackpackItem,
        ajax: {
            url: '{{ route('admin.api.items') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: { more: data.pagination.more }
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>
@endpush

@endsection
