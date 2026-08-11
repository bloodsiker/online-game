@extends('admin.layout.base')

@section('title')
    {{ $monster->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-drop" href="#tab-drop" data-bs-toggle="tab">
                                    Дроп <span class="badge badge-primary">{{ $monster->items->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-locations" href="#tab-locations" data-bs-toggle="tab">
                                    Локации <span class="badge badge-primary">{{ $monster->locations->count() }}</span>
                                </a>
                            </li>
                            @if($monster->is_boss)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-phases" href="#tab-phases" data-bs-toggle="tab">
                                    Фазы боя <span class="badge badge-warning">{{ $monster->phases?->count() ?? 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-mechanics" href="#tab-mechanics" data-bs-toggle="tab">
                                    Механики <span class="badge badge-danger">{{ $monster->mechanics?->count() ?? 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-summon-pool" href="#tab-summon-pool" data-bs-toggle="tab">
                                    Пул призыва <span class="badge badge-info">{{ $monster->summonPool?->count() ?? 0 }}</span>
                                </a>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.monster.info', $monster->id) }}" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="row pb-3 pt-3">

                                        <div class="col-lg-4">
                                            <h6 class="text-muted mb-3">Основное</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $monster->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Уровень</label>
                                                <input type="number" class="form-control" name="lvl" value="{{ $monster->lvl }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Описание</label>
                                                <textarea class="form-control" name="description" rows="4">{{ $monster->description }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Изображение</label>
                                                @if($monster->image)
                                                    <div class="mb-1">
                                                        <img id="monster-preview" src="{{ $monster->image }}" alt=""
                                                             style="width:300px;height:300px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">
                                                        <small class="text-muted d-block">{{ $monster->image }}</small>
                                                    </div>
                                                @else
                                                    <img id="monster-preview" src="" alt=""
                                                         style="width:300px;height:300px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                                @endif
                                                <input type="file" class="form-control mt-1" name="image" id="monster-image" accept="image/*">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Босс</label>
                                                <select class="form-control" name="is_boss">
                                                    <option value="0" @selected(!$monster->is_boss)>Нет</option>
                                                    <option value="1" @selected($monster->is_boss)>Да</option>
                                                </select>
                                            </div>
                                            @if($monster->is_boss)
                                                <div class="form-group">
                                                    <label class="col-form-label">Респаун, мин. (от / до)</label>
                                                    <div class="d-flex gap-1">
                                                        <input type="number" class="form-control" name="respawn_min_minutes" min="0" value="{{ $monster->respawn_min_minutes }}" placeholder="от">
                                                        <input type="number" class="form-control" name="respawn_max_minutes" min="0" value="{{ $monster->respawn_max_minutes }}" placeholder="до (пусто = фикс.)">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Следующий респаун (можно поменять вручную)</label>
                                                    <input type="datetime-local" class="form-control" name="respawn_at" value="{{ $monster->respawn_at?->format('Y-m-d\TH:i') }}">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="col-lg-4">
                                            <h6 class="text-muted mb-3">Боевые характеристики</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">HP</label>
                                                <input type="number" class="form-control" name="hp" value="{{ $monster->hp }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Защита</label>
                                                <input type="number" class="form-control" name="armor" value="{{ $monster->armor }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Уворот (%)</label>
                                                <input type="number" class="form-control" name="dodge" value="{{ $monster->dodge }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Крит (%)</label>
                                                <input type="number" class="form-control" name="critical" value="{{ $monster->critical }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Мин. атака</label>
                                                <input type="number" step="0.01" class="form-control" name="min_dmg" value="{{ $monster->min_dmg }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Макс. атака</label>
                                                <input type="number" step="0.01" class="form-control" name="max_dmg" value="{{ $monster->max_dmg }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <h6 class="text-muted mb-3">Награда</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">Агрессивность (%)</label>
                                                <input type="number" class="form-control" name="aggression" value="{{ $monster->aggression }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Опыт</label>
                                                <input type="number" class="form-control" name="exp" value="{{ $monster->exp }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Мин. монет</label>
                                                <input type="number" class="form-control" name="min_money" value="{{ $monster->min_money }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Макс. монет</label>
                                                <input type="number" class="form-control" name="max_money" value="{{ $monster->max_money }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.monsters') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- ДРОП --}}
                            <div id="tab-drop" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalDrop">Добавить предмет</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Название</th>
                                                <th width="100">Шанс (%)</th>
                                                <th width="110">Кол-во</th>
                                                <th width="100">Цена</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($monster->items as $item)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $item->id }}</td>
                                                    <td><img src="{{ $item->image }}" width="36" alt=""></td>
                                                    <td><a href="{{ route('admin.item.info', $item->id) }}" target="_blank">{{ $item->name }}</a></td>
                                                    <td>{{ $item->pivot->drop_chance }}</td>
                                                    <td>{{ $item->pivot->min_count }} – {{ $item->pivot->max_count }}</td>
                                                    <td>{{ number_format($item->price, 0, '', ' ') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.monster.info.drop.delete_item', [$monster->id, $item->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Нет предметов</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ЛОКАЦИИ --}}
                            <div id="tab-locations" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalLocation">Добавить локацию</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th>Локация</th>
                                                <th>Карта</th>
                                                <th width="170">Агрессия (%)</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($monster->locations as $location)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $location->id }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.location.info', $location->id) }}">[{{ $location->id }}] {{ $location->name }}</a>
                                                    </td>
                                                    <td>
                                                        @if($location->map)
                                                            <a href="{{ route('admin.map.info', $location->map->id) }}">{{ $location->map->name }}</a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1 align-items-center">
                                                            <input type="number" min="0" max="100"
                                                                   value="{{ $location->pivot->aggression ?? '' }}"
                                                                   placeholder="из монстра"
                                                                   class="form-control form-control-sm aggression-input" style="width:90px"
                                                                   data-url="{{ route('admin.monster.info.location.aggression', [$monster->id, $location->id]) }}">
                                                            <button class="btn btn-xs btn-secondary aggression-save">✓</button>
                                                            <span class="aggression-status ms-1" style="font-size:12px"></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.monster.info.delete_location', [$monster->id, $location->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted">Нет локаций</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($monster->is_boss)

                            {{-- ФАЗЫ БОЯ --}}
                            <div id="tab-phases" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAddPhase">Добавить фазу</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="70">Фаза №</th>
                                                <th width="120">HP порог (%)</th>
                                                <th>Описание</th>
                                                <th width="120">Мод. статов</th>
                                                <th width="100">Новые скилы</th>
                                                <th width="100">Убраны</th>
                                                <th width="120"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($monster->phases ?? [] as $phase)
                                                <tr style="vertical-align: middle"
                                                    data-phase-id="{{ $phase->id }}"
                                                    data-phase-number="{{ $phase->phase_number }}"
                                                    data-hp-threshold="{{ $phase->hp_threshold }}"
                                                    data-description="{{ $phase->description }}"
                                                    data-stats-modifiers="{{ json_encode($phase->stats_modifiers) }}"
                                                    data-new-skills="{{ json_encode($phase->new_skills) }}"
                                                    data-removed-skills="{{ json_encode($phase->removed_skills) }}">
                                                    <td><strong>{{ $phase->phase_number }}</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $phase->hp_threshold <= 25 ? 'danger' : ($phase->hp_threshold <= 50 ? 'warning' : 'info') }}">
                                                            ≤ {{ $phase->hp_threshold }}%
                                                        </span>
                                                    </td>
                                                    <td>{{ $phase->description }}</td>
                                                    <td>
                                                        @if($phase->stats_modifiers)
                                                            <code class="text-muted small">{{ count($phase->stats_modifiers) }} статов</code>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($phase->new_skills)
                                                            <span class="badge badge-success">{{ count($phase->new_skills) }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($phase->removed_skills)
                                                            <span class="badge badge-secondary">{{ count($phase->removed_skills) }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-xs btn-info btn-edit-phase">Изменить</button>
                                                        <a href="{{ route('admin.monster.boss.phase.delete', [$monster->id, $phase->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить фазу?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Нет фаз. Добавьте хотя бы одну.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- МЕХАНИКИ --}}
                            <div id="tab-mechanics" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAddMechanic">Добавить механику</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th>Тип</th>
                                                <th width="130">Триггер HP (%)</th>
                                                <th width="110">Триггер ход</th>
                                                <th width="80">Приор.</th>
                                                <th width="90">Статус</th>
                                                <th width="150"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($monster->mechanics ?? [] as $mechanic)
                                                <tr style="vertical-align: middle"
                                                    data-mechanic-id="{{ $mechanic->id }}"
                                                    data-mechanic-type="{{ $mechanic->mechanic_type?->value }}"
                                                    data-trigger-hp="{{ $mechanic->trigger_hp_percent }}"
                                                    data-trigger-turn="{{ $mechanic->trigger_turn }}"
                                                    data-priority="{{ $mechanic->priority }}"
                                                    data-is-active="{{ $mechanic->is_active ? '1' : '0' }}"
                                                    data-config="{{ json_encode($mechanic->config) }}">
                                                    <td>
                                                        {{ $mechanic->getIcon() }}
                                                        <strong>{{ $mechanic->getLabel() }}</strong>
                                                        <br><small class="text-muted">{{ $mechanic->mechanic_type?->value }}</small>
                                                    </td>
                                                    <td>
                                                        @if($mechanic->trigger_hp_percent !== null)
                                                            <span class="badge badge-warning">≤ {{ $mechanic->trigger_hp_percent }}%</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mechanic->trigger_turn !== null)
                                                            <span class="badge badge-info">Ход {{ $mechanic->trigger_turn }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $mechanic->priority }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.monster.boss.mechanic.toggle', [$monster->id, $mechanic->id]) }}"
                                                           class="badge badge-{{ $mechanic->is_active ? 'success' : 'secondary' }}"
                                                           style="cursor:pointer;text-decoration:none">
                                                            {{ $mechanic->is_active ? 'Активна' : 'Выкл' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-xs btn-info btn-edit-mechanic">Изменить</button>
                                                        <a href="{{ route('admin.monster.boss.mechanic.delete', [$monster->id, $mechanic->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить механику?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-muted">Нет механик. Босс сражается как обычный монстр.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div id="tab-summon-pool" class="tab-pane">
                                <div class="pt-3">
                                    <p class="text-muted">Кого может призвать этот босс механикой «Вызов миньонов» (SUMMON_MINIONS). Сколько именно
                                        призывать за раз — задаётся в config самой механики (count или min_count/max_count).</p>
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalSummonPool">Добавить моба в пул</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th>Моб</th>
                                                <th width="120">Вес (шанс)</th>
                                                <th width="100"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($monster->summonPool ?? [] as $entry)
                                                <tr style="vertical-align: middle">
                                                    <td>
                                                        @if($entry->minionMonster)
                                                            <a href="{{ route('admin.monster.info', $entry->minionMonster->id) }}">
                                                                {{ $entry->minionMonster->name }} (ур. {{ $entry->minionMonster->lvl }})
                                                            </a>
                                                        @else
                                                            <span class="text-muted">— моб удалён —</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $entry->weight }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.monster.boss.summon-pool.delete', [$monster->id, $entry->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Убрать моба из пула призыва?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted">Пул пуст — механика «Вызов миньонов» не сработает.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @endif

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Модалка: добавить моба в пул призыва --}}
    <div id="modalSummonPool" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.monster.boss.summon-pool.add', $monster->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить моба в пул призыва</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Моб</label>
                        <select name="minion_monster_id" class="form-control" required>
                            @foreach($allMonsters ?? [] as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} (ур. {{ $m->lvl }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Вес (относительный шанс выбора при призыве)</label>
                        <input type="number" min="1" class="form-control" name="weight" value="1">
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

    {{-- Модалка: добавить дроп --}}
    <div id="modalDrop" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.monster.info.drop', $monster->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в дроп</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="item-ajax-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Шанс (%)</label>
                        <input type="number" step="0.01" class="form-control" name="drop_chance" value="10">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Мин. кол-во</label>
                                <input type="number" class="form-control" name="min_count" value="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Макс. кол-во</label>
                                <input type="number" class="form-control" name="max_count" value="1">
                            </div>
                        </div>
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

    {{-- Модалка: добавить локацию --}}
    <div id="modalLocation" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.monster.info.location.save', $monster->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить локацию</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Локация</label>
                        <select id="location-ajax-select" name="location_id" class="form-control"></select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Агрессия (0–100, пусто = базовая из монстра)</label>
                        <input type="number" name="aggression" min="0" max="100" class="form-control" placeholder="По умолчанию из монстра">
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

    {{-- Модалка: добавить фазу --}}
    <div id="modalAddPhase" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form id="form-add-phase" action="{{ route('admin.monster.boss.phase.add', $monster->id) }}" method="post">
                <header class="card-header"><h2 class="card-title" id="phase-modal-title">Добавить фазу</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Фаза №</label>
                                <input type="number" class="form-control" name="phase_number" id="phase_number" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">HP порог (%)</label>
                                <input type="number" class="form-control" name="hp_threshold" id="hp_threshold" value="50" min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Описание</label>
                                <input type="text" class="form-control" name="description" id="phase_description">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Модификаторы статов (JSON)</label>
                                <small class="text-muted d-block">Напр.: <code>[{"stat":"hp","value":50}]</code></small>
                                <textarea class="form-control font-monospace" name="stats_modifiers" id="stats_modifiers" rows="4" placeholder="[]"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Новые скилы (JSON)</label>
                                <small class="text-muted d-block">Напр.: <code>["fireball","shield"]</code></small>
                                <textarea class="form-control font-monospace" name="new_skills" id="new_skills" rows="4" placeholder="[]"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Убираемые скилы (JSON)</label>
                                <textarea class="form-control font-monospace" name="removed_skills" id="removed_skills" rows="4" placeholder="[]"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" id="phase-submit-btn">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    {{-- Модалка: добавить / редактировать механику --}}
    <div id="modalAddMechanic" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form id="form-mechanic" action="{{ route('admin.monster.boss.mechanic.add', $monster->id) }}" method="post">
                <header class="card-header"><h2 class="card-title" id="mechanic-modal-title">Добавить механику</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Тип механики</label>
                                <select class="form-control" name="mechanic_type" id="mechanic_type">
                                    @foreach($mechanicTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->getIcon() }} {{ $type->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Триггер HP (%)</label>
                                <small class="text-muted d-block">Пусто = не триггерить по HP</small>
                                <input type="number" class="form-control" name="trigger_hp_percent" id="trigger_hp_percent" min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Триггер ход №</label>
                                <small class="text-muted d-block">Пусто = не триггерить по ходу</small>
                                <input type="number" class="form-control" name="trigger_turn" id="trigger_turn" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Приоритет</label>
                                <input type="number" class="form-control" name="priority" id="mechanic_priority" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Активна</label>
                                <select class="form-control" name="is_active" id="mechanic_is_active">
                                    <option value="1">Да</option>
                                    <option value="0">Нет</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Конфигурация (JSON)</label>
                                <small class="text-muted d-block">Зависит от типа механики. Напр.: <code>{"multiplier":2}</code></small>
                                <textarea class="form-control font-monospace" name="config" id="mechanic_config" rows="3" placeholder="{}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" id="mechanic-submit-btn">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    // Image preview
    document.getElementById('monster-image').addEventListener('change', function () {
        const preview = document.getElementById('monster-preview');
        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.style.display = 'block';
        }
    });

    @if($monster->is_boss)
    // ── Фазы: редактирование ──────────────────────────────────────────────────
    const addPhaseUrl    = '{{ route('admin.monster.boss.phase.add', $monster->id) }}';
    const updatePhaseBase = '{{ url('admin/monster/' . $monster->id . '/phase') }}';

    document.querySelectorAll('.btn-edit-phase').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const phaseId = row.dataset.phaseId;

            document.getElementById('phase-modal-title').textContent = 'Редактировать фазу #' + row.dataset.phaseNumber;
            document.getElementById('form-add-phase').action = updatePhaseBase + '/' + phaseId;
            document.getElementById('phase-submit-btn').textContent = 'Сохранить';
            document.getElementById('phase_number').value    = row.dataset.phaseNumber;
            document.getElementById('hp_threshold').value    = row.dataset.hpThreshold;
            document.getElementById('phase_description').value = row.dataset.description || '';

            const mods = row.dataset.statsModifiers;
            document.getElementById('stats_modifiers').value = mods && mods !== 'null' ? JSON.stringify(JSON.parse(mods), null, 2) : '';

            const ns = row.dataset.newSkills;
            document.getElementById('new_skills').value = ns && ns !== 'null' ? JSON.stringify(JSON.parse(ns), null, 2) : '';

            const rs = row.dataset.removedSkills;
            document.getElementById('removed_skills').value = rs && rs !== 'null' ? JSON.stringify(JSON.parse(rs), null, 2) : '';

            $.magnificPopup.open({ items: { src: '#modalAddPhase', type: 'inline' }, removalDelay: 300, mainClass: 'mfp-zoom-in' });
        });
    });

    document.querySelector('[href="#modalAddPhase"]').addEventListener('click', function () {
        document.getElementById('phase-modal-title').textContent = 'Добавить фазу';
        document.getElementById('form-add-phase').action = addPhaseUrl;
        document.getElementById('phase-submit-btn').textContent = 'Добавить';
        document.getElementById('phase_number').value = '';
        document.getElementById('hp_threshold').value = '50';
        document.getElementById('phase_description').value = '';
        document.getElementById('stats_modifiers').value = '';
        document.getElementById('new_skills').value = '';
        document.getElementById('removed_skills').value = '';
    });

    // ── Механики: редактирование ──────────────────────────────────────────────
    const addMechanicUrl    = '{{ route('admin.monster.boss.mechanic.add', $monster->id) }}';
    const updateMechanicBase = '{{ url('admin/monster/' . $monster->id . '/mechanic') }}';

    document.querySelectorAll('.btn-edit-mechanic').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const mechId = row.dataset.mechanicId;

            document.getElementById('mechanic-modal-title').textContent = 'Редактировать механику';
            document.getElementById('form-mechanic').action = updateMechanicBase + '/' + mechId;
            document.getElementById('mechanic-submit-btn').textContent = 'Сохранить';
            document.getElementById('mechanic_type').value = row.dataset.mechanicType || '';
            document.getElementById('trigger_hp_percent').value = row.dataset.triggerHp || '';
            document.getElementById('trigger_turn').value = row.dataset.triggerTurn || '';
            document.getElementById('mechanic_priority').value = row.dataset.priority || 0;
            document.getElementById('mechanic_is_active').value = row.dataset.isActive || '1';

            const cfg = row.dataset.config;
            document.getElementById('mechanic_config').value = cfg && cfg !== 'null' ? JSON.stringify(JSON.parse(cfg), null, 2) : '';

            $.magnificPopup.open({ items: { src: '#modalAddMechanic', type: 'inline' }, removalDelay: 300, mainClass: 'mfp-zoom-in' });
        });
    });

    document.querySelector('[href="#modalAddMechanic"]').addEventListener('click', function () {
        document.getElementById('mechanic-modal-title').textContent = 'Добавить механику';
        document.getElementById('form-mechanic').action = addMechanicUrl;
        document.getElementById('mechanic-submit-btn').textContent = 'Добавить';
        document.getElementById('mechanic_type').value = '';
        document.getElementById('trigger_hp_percent').value = '';
        document.getElementById('trigger_turn').value = '';
        document.getElementById('mechanic_priority').value = '0';
        document.getElementById('mechanic_is_active').value = '1';
        document.getElementById('mechanic_config').value = '';
    });
    @endif

    function formatItem(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '<span style="display:inline-block;width:24px;height:24px;margin-right:6px;"></span>';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#item-ajax-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalDrop'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatItem,
        templateSelection: formatItem,
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

    // AJAX сохранение агрессии
    $(document).on('click', '.aggression-save', function () {
        var $btn    = $(this);
        var $wrap   = $btn.closest('.d-flex');
        var $input  = $wrap.find('.aggression-input');
        var $status = $wrap.find('.aggression-status');
        var url     = $input.data('url');
        var value   = $input.val();

        $btn.prop('disabled', true);
        $status.text('').css('color', '');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                aggression: value !== '' ? value : null,
            },
            success: function () {
                $status.text('✓').css('color', 'green');
                setTimeout(function () { $status.text(''); }, 2000);
            },
            error: function () {
                $status.text('✗').css('color', 'red');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#location-ajax-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalLocation'),
        placeholder: 'Выберите локацию',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.locations') }}',
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
