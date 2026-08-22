@extends('admin.layout.base')

@section('title')
    Добавить скилл
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.magic_skill.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Slug <small class="text-muted">(уникальный код)</small></label>
                                    <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="fireball">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo>
                                        <option value="attack"  @selected(old('type') === 'attack')>attack</option>
                                        <option value="defense" @selected(old('type') === 'defense')>defense</option>
                                        <option value="buff"    @selected(old('type', 'buff') === 'buff')>buff</option>
                                        <option value="debuff"  @selected(old('type') === 'debuff')>debuff</option>
                                        <option value="heal"    @selected(old('type') === 'heal')>heal</option>
                                        <option value="utility" @selected(old('type') === 'utility')>utility</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Цель</label>
                                    <select class="form-control" name="target_type" data-plugin-selectTwo>
                                        <option value="self"  @selected(old('target_type') === 'self')>self</option>
                                        <option value="all"   @selected(old('target_type', 'all') === 'all')>all</option>
                                        <option value="enemy" @selected(old('target_type') === 'enemy')>enemy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Требуемый уровень</label>
                                    <input type="number" min="1" class="form-control" name="level" value="{{ old('level', 1) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Мана</label>
                                    <input type="number" min="0" class="form-control" name="mana_cost" value="{{ old('mana_cost', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Кулдаун (сек)</label>
                                    <input type="number" min="0" class="form-control" name="cooldown" value="{{ old('cooldown', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Мин. урон</label>
                                    <input type="number" min="0" class="form-control" name="min_damage" value="{{ old('min_damage', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Макс. урон</label>
                                    <input type="number" min="0" class="form-control" name="max_damage" value="{{ old('max_damage', 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Коэффициент силы <small class="text-muted">(от интеллекта/атрибута)</small></label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="power_coefficient" value="{{ old('power_coefficient', 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Базовое лечение</label>
                            <input type="number" min="0" class="form-control" name="base_healing" value="{{ old('base_healing', 0) }}">
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="is-passive" name="is_passive" value="1" @checked(old('is_passive'))>
                            <label for="is-passive">Пассивный скилл</label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.magic_skills') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
