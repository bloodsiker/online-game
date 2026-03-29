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

@endsection