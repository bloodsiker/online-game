@extends('admin.layout.base')

@section('title')
    Эффект: {{ $effect->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.effect.info', $effect->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="name" value="{{ $effect->name }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Slug <small class="text-muted">(уникальный код)</small></label>
                                    <input type="text" class="form-control" name="slug" value="{{ $effect->slug }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ $effect->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo>
                                        <option value="buff"    @selected($effect->type === 'buff')>buff</option>
                                        <option value="debuff"  @selected($effect->type === 'debuff')>debuff</option>
                                        <option value="neutral" @selected($effect->type === 'neutral')>neutral</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Шанс наложения, %</label>
                                    <input type="number" min="0" max="100" class="form-control" name="chance" value="{{ $effect->chance }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Длительность (сек) <small class="text-muted">(0 = до снятия)</small></label>
                                    <input type="number" min="0" class="form-control" name="duration" value="{{ $effect->duration }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Тик каждые (сек)</label>
                                    <input type="number" min="1" class="form-control" name="tick_interval" value="{{ $effect->tick_interval }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Значение за тик <small class="text-muted">(урон/лечение)</small></label>
                                    <input type="number" class="form-control" name="value_per_tick" value="{{ $effect->value_per_tick }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Макс. стаков</label>
                                    <input type="number" min="1" class="form-control" name="max_stacks" value="{{ $effect->max_stacks }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Модификаторы статов <small class="text-muted">(JSON, напр. [{"type":"attack","value":30,"is_percent":true}])</small></label>
                            <textarea class="form-control" name="stat_modifiers" rows="3">{{ $effect->stat_modifiers ? json_encode($effect->stat_modifiers, JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="is-stackable" name="is_stackable" value="1" @checked($effect->is_stackable)>
                            <label for="is-stackable">Стакается</label>
                        </div>
                        <div class="checkbox-custom checkbox-default mt-2">
                            <input type="checkbox" id="is-dispellable" name="is_dispellable" value="1" @checked($effect->is_dispellable)>
                            <label for="is-dispellable">Можно снять</label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.effects') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-md-6">
            <section class="card">
                <header class="card-header"><h2 class="card-title">Используется в скиллах</h2></header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th>Скилл</th>
                                <th width="100">Шанс, %</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($effect->magicSkills as $skill)
                                <tr style="vertical-align: middle">
                                    <td><a href="{{ route('admin.magic_skill.info', $skill->id) }}">{{ $skill->name }}</a></td>
                                    <td>{{ $skill->pivot->chance }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Ни один скилл не использует этот эффект</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
