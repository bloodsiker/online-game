@extends('admin.layout.base')

@section('title')
    Навык: {{ $skill->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.skill.info', $skill->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ $skill->name }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Тип навыка</label>
                            <select class="form-control" name="type" data-plugin-selectTwo>
                                <option value="combat"   @selected($skill->type === 'combat')>Боевой</option>
                                <option value="magic"    @selected($skill->type === 'magic')>Магический</option>
                                <option value="peaceful" @selected($skill->type === 'peaceful')>Мирный</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ $skill->description }}</textarea>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.skills') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    @if($skill->type === 'peaceful')
        <section class="card mt-3">
            <div class="card-body">
                <h4 class="mb-1">Шкала мирной профессии</h4>
                <p class="text-muted mb-3">
                    Укажите суммарный опыт для перехода с каждого уровня на следующий. Разница опыта рассчитывается автоматически.
                </p>

                <form action="{{ route('admin.skill.peaceful-requirements.update', $skill) }}" method="post">
                    @csrf
                    <div class="table-responsive" style="max-height: 640px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead>
                            <tr>
                                <th style="width: 120px;">Уровень</th>
                                <th>Суммарный опыт для перехода</th>
                            </tr>
                            </thead>
                            <tbody>
                            @for($level = 1; $level <= 300; $level++)
                                <tr>
                                    <td>{{ $level }}</td>
                                    <td>
                                        <input
                                            class="form-control @error('requirements.'.$level) is-invalid @enderror"
                                            type="number"
                                            min="1"
                                            max="4294967295"
                                            name="requirements[{{ $level }}]"
                                            value="{{ old('requirements.'.$level, $requirements->get($level)?->exp_required) }}"
                                            required
                                        >
                                        @error('requirements.'.$level)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>
                    @error('requirements')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                    <div class="mt-3">
                        <button class="btn btn-primary">Сохранить шкалу</button>
                    </div>
                </form>
            </div>
        </section>
    @endif

@endsection
