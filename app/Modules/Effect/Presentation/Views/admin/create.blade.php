@extends('admin.layout.base')

@section('title')
    Добавить эффект
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.effect.create') }}" method="post" enctype="multipart/form-data">
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
                                    <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="poison">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Картинка</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="form-text text-muted">Максимум 4 МБ.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo>
                                        <option value="buff"    @selected(old('type', 'buff') === 'buff')>buff</option>
                                        <option value="debuff"  @selected(old('type') === 'debuff')>debuff</option>
                                        <option value="neutral" @selected(old('type') === 'neutral')>neutral</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Активная механика</label>
                                    <select class="form-control" name="active_type" data-plugin-selectTwo>
                                        <option value="">Нет</option>
                                        @foreach($activeTypes as $activeType)
                                            <option value="{{ $activeType->value }}" @selected(old('active_type') === $activeType->value)>
                                                {{ $activeType->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Шанс наложения, %</label>
                                    <input type="number" min="0" max="100" class="form-control" name="chance" value="{{ old('chance', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Масштабирование урона</label>
                                    <select class="form-control" name="damage_scaling_type" data-plugin-selectTwo>
                                        <option value="" @selected(old('damage_scaling_type') === null)>Не задано</option>
                                        @foreach($damageScalingTypes as $scalingType)
                                            <option value="{{ $scalingType->value }}" @selected(old('damage_scaling_type') === $scalingType->value)>
                                                {{ $scalingType->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info py-2">
                            Настройка применяется к периодическому урону монстров. «От макс. HP» распределяет указанный у монстра процент на всю длительность эффекта.
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Тик каждые (сек)</label>
                                    <input type="number" min="0" class="form-control" name="tick_interval" value="{{ old('tick_interval', 1) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Значение за тик <small class="text-muted">(урон/лечение)</small></label>
                                    <input type="number" class="form-control" name="value_per_tick" value="{{ old('value_per_tick') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Макс. стаков</label>
                                    <input type="number" min="1" class="form-control" name="max_stacks" value="{{ old('max_stacks', 1) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Модификаторы статов <small class="text-muted">(JSON, напр. [{"type":"attack","value":30,"is_percent":true}])</small></label>
                            <textarea class="form-control" name="stat_modifiers" rows="3" placeholder='[{"type":"attack","value":30,"is_percent":true}]'>{{ old('stat_modifiers') }}</textarea>
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="is-stackable" name="is_stackable" value="1" @checked(old('is_stackable'))>
                            <label for="is-stackable">Стакается</label>
                        </div>
                        <div class="checkbox-custom checkbox-default mt-2">
                            <input type="checkbox" id="is-dispellable" name="is_dispellable" value="1" @checked(old('is_dispellable', true))>
                            <label for="is-dispellable">Можно снять</label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.effects') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
