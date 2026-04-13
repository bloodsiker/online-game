@extends('admin.layout.base')

@section('title')
    Создать расу
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.race.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Сила</label>
                                    <input type="number" step="0.01" class="form-control" name="strength" value="{{ old('strength', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Ловкость</label>
                                    <input type="number" step="0.01" class="form-control" name="agility" value="{{ old('agility', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интуиция</label>
                                    <input type="number" step="0.01" class="form-control" name="intuition" value="{{ old('intuition', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Мудрость</label>
                                    <input type="number" step="0.01" class="form-control" name="wisdom" value="{{ old('wisdom', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интеллект</label>
                                    <input type="number" step="0.01" class="form-control" name="intelligence" value="{{ old('intelligence', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Своб. характеристики</label>
                                    <input type="number" class="form-control" name="free_stats" value="{{ old('free_stats', 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.race') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
