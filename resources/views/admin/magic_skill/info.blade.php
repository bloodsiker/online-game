@extends('admin.layout.base')

@section('title')
    Скилл: {{ $magicSkill->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.magic_skill.info', $magicSkill->id) }}" method="post">
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
                                    <label class="col-form-label">Требуемый уровень</label>
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
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th>Эффект</th>
                                <th width="90">Тип</th>
                                <th width="100">Шанс, %</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($magicSkill->skillEffects as $effect)
                                <tr style="vertical-align: middle">
                                    <td><a href="{{ route('admin.effect.info', $effect->id) }}">{{ $effect->name }}</a></td>
                                    <td><span class="badge badge-info">{{ $effect->type }}</span></td>
                                    <td>{{ $effect->pivot->chance }}</td>
                                    <td>
                                        <a href="{{ route('admin.magic_skill.effect.delete', [$magicSkill->id, $effect->id]) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Эффекты не привязаны</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <form action="{{ route('admin.magic_skill.effect.add', $magicSkill->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-7">
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm mb-2">Добавить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
