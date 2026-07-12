@extends('admin.layout.base')

@section('title')
    Раса: {{ $race->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.race.info', $race->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ $race->name }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Сила</label>
                                    <input type="number" step="0.01" class="form-control" name="strength" value="{{ $race->strength }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Ловкость</label>
                                    <input type="number" step="0.01" class="form-control" name="agility" value="{{ $race->agility }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интуиция</label>
                                    <input type="number" step="0.01" class="form-control" name="intuition" value="{{ $race->intuition }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Мудрость</label>
                                    <input type="number" step="0.01" class="form-control" name="wisdom" value="{{ $race->wisdom }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Интеллект</label>
                                    <input type="number" step="0.01" class="form-control" name="intelligence" value="{{ $race->intelligence }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Выносливость</label>
                                    <input type="number" step="0.01" class="form-control" name="endurance" value="{{ $race->endurance }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Своб. характеристики</label>
                                    <input type="number" class="form-control" name="free_stats" value="{{ $race->free_stats }}">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.race') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
