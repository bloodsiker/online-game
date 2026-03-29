@extends('admin.layout.base')

@section('title')
    Добавить навык
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.skill.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Тип навыка</label>
                            <select class="form-control" name="type" data-plugin-selectTwo>
                                <option value="combat"  @selected(old('type') === 'combat')>Боевой</option>
                                <option value="magic"   @selected(old('type') === 'magic')>Магический</option>
                                <option value="peaceful" @selected(old('type') === 'peaceful')>Мирный</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ old('description') }}</textarea>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.skills') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection