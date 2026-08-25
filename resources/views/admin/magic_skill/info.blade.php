@extends('admin.layout.base')

@section('title')
    Скилл: {{ $magicSkill->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.magic_skill.info', $magicSkill->id) }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="name" value="{{ $magicSkill->name }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Slug <small class="text-muted">(уникальный код)</small></label>
                                    <input type="text" class="form-control" name="slug" value="{{ $magicSkill->slug }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ $magicSkill->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Картинка</label>
                            @if($magicSkill->image)
                                <div class="mb-2">
                                    <img src="{{ $magicSkill->image }}" alt="{{ $magicSkill->name }}"
                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;padding:3px;background:#fff;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="form-text text-muted">Максимум 4 МБ.</small>
                            @if($magicSkill->image)
                                <div class="checkbox-custom checkbox-default mt-2">
                                    <input type="checkbox" id="delete-magic-skill-image" name="delete_image" value="1">
                                    <label for="delete-magic-skill-image">Удалить текущую картинку</label>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo>
                                        <option value="attack"  @selected($magicSkill->type === 'attack')>attack</option>
                                        <option value="defense" @selected($magicSkill->type === 'defense')>defense</option>
                                        <option value="buff"    @selected($magicSkill->type === 'buff')>buff</option>
                                        <option value="debuff"  @selected($magicSkill->type === 'debuff')>debuff</option>
                                        <option value="heal"    @selected($magicSkill->type === 'heal')>heal</option>
                                        <option value="utility" @selected($magicSkill->type === 'utility')>utility</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Цель</label>
                                    <select class="form-control" name="target_type" data-plugin-selectTwo>
                                        <option value="self"  @selected($magicSkill->target_type === 'self')>self</option>
                                        <option value="all"   @selected($magicSkill->target_type === 'all')>all</option>
                                        <option value="enemy" @selected($magicSkill->target_type === 'enemy')>enemy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Уровень заклинания</label>
                                    <input type="number" min="1" class="form-control" name="level" value="{{ $magicSkill->level }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Мана</label>
                                    <input type="number" min="0" class="form-control" name="mana_cost" value="{{ $magicSkill->mana_cost }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Кулдаун (сек)</label>
                                    <input type="number" min="0" class="form-control" name="cooldown" value="{{ $magicSkill->cooldown }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Мин. урон</label>
                                    <input type="number" min="0" class="form-control" name="min_damage" value="{{ $magicSkill->min_damage }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Макс. урон</label>
                                    <input type="number" min="0" class="form-control" name="max_damage" value="{{ $magicSkill->max_damage }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Коэффициент силы <small class="text-muted">(от интеллекта/атрибута)</small></label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="power_coefficient" value="{{ $magicSkill->power_coefficient }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Базовое лечение</label>
                            <input type="number" min="0" class="form-control" name="base_healing" value="{{ $magicSkill->base_healing }}">
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="is-passive" name="is_passive" value="1" @checked($magicSkill->is_passive)>
                            <label for="is-passive">Пассивный скилл</label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.magic_skills') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-md-6">
            <section class="card">
                <header class="card-header"><h2 class="card-title">Наложенные эффекты</h2></header>
                <div class="card-body">
                    @foreach($magicSkill->skillEffects as $effect)
                        <form id="magic-effect-update-{{ $effect->id }}"
                              action="{{ route('admin.magic_skill.effect.update', [$magicSkill->id, $effect->id]) }}"
                              method="post">
                            {{ csrf_field() }}
                        </form>
                    @endforeach
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th>Эффект</th>
                                <th width="90">Тип</th>
                                <th width="100">Шанс, %</th>
                                <th width="120">Длительность</th>
                                <th width="145"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($magicSkill->skillEffects as $effect)
                                <tr style="vertical-align: middle">
                                    <td><a href="{{ route('admin.effect.info', $effect->id) }}">{{ $effect->name }}</a></td>
                                    <td><span class="badge badge-info">{{ $effect->type }}</span></td>
                                    <td>
                                        <input form="magic-effect-update-{{ $effect->id }}" type="number" min="0" max="100"
                                               class="form-control form-control-sm" name="chance" value="{{ $effect->pivot->chance }}" required>
                                    </td>
                                    <td>
                                        <input form="magic-effect-update-{{ $effect->id }}" type="number" min="0"
                                               class="form-control form-control-sm" name="duration_seconds"
                                               value="{{ $effect->pivot->duration_seconds }}" required>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <button form="magic-effect-update-{{ $effect->id }}" class="btn btn-xs btn-primary">Сохранить</button>
                                        <a href="{{ route('admin.magic_skill.effect.delete', [$magicSkill->id, $effect->id]) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Эффекты не привязаны</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <form action="{{ route('admin.magic_skill.effect.add', $magicSkill->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Эффект</label>
                                    <select name="effect_id" class="form-control" data-plugin-selectTwo required>
                                        @foreach($effects as $effect)
                                            <option value="{{ $effect->id }}">[{{ $effect->id }}] {{ $effect->name }} ({{ $effect->type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Шанс, %</label>
                                    <input type="number" min="0" max="100" class="form-control" name="chance" value="100">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Длит-ть, сек</label>
                                    <input type="number" min="0" class="form-control" name="duration_seconds" value="5" required>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm mb-2">Добавить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-md-6">
            <section class="card">
                <header class="card-header"><h2 class="card-title">Требования к изучению</h2></header>
                <div class="card-body">
                    <p class="text-muted mb-3">Проверяются только при изучении книги. Уже изученное заклинание остаётся доступным.</p>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th>Тип</th>
                                <th>Требование</th>
                                <th width="100">Минимум</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($magicSkill->requirements as $requirement)
                                <tr style="vertical-align: middle">
                                    <td><span class="badge badge-info">{{ $requirement->type->label() }}</span></td>
                                    <td>{{ $requirement->label() }}</td>
                                    <td>{{ $requirement->min_value }}</td>
                                    <td>
                                        <a href="{{ route('admin.magic_skill.requirement.delete', [$magicSkill->id, $requirement->id]) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Требования не заданы</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <form action="{{ route('admin.magic_skill.requirement.add', $magicSkill->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип</label>
                                    <select name="type" class="form-control" required>
                                        @foreach (\App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType::cases() as $type)
                                            <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Характеристика</label>
                                    <select name="stat_key" class="form-control">
                                        <option value="">—</option>
                                        @foreach (\App\Modules\Player\Domain\Enums\PlayerStatKey::cases() as $stat)
                                            <option value="{{ $stat->value }}" @selected(old('stat_key') === $stat->value)>{{ $stat->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Навык</label>
                                    <select name="skill_id" class="form-control" data-plugin-selectTwo>
                                        <option value="">—</option>
                                        @foreach ($skills as $skill)
                                            <option value="{{ $skill->id }}" @selected((string) old('skill_id') === (string) $skill->id)>{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Минимальное значение</label>
                                    <input type="number" min="1" name="min_value" class="form-control" value="{{ old('min_value', 1) }}" required>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm mb-2">Добавить требование</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
