@extends('admin.layout.base')

@section('title')
    Создать квест
@endsection

@section('body')

    <div class="row">
        <div class="col-md-8">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.quest.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="row pt-2 pb-3">

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Описание</label>
                                    <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo>
                                        @foreach($questTypes as $type)
                                            <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Активен</label>
                                    <select class="form-control" name="is_active">
                                        <option value="1" @selected(old('is_active', '1') === '1')>Да</option>
                                        <option value="0" @selected(old('is_active') === '0')>Нет</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Создать</button>
                                <a href="{{ route('admin.quests') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection