@extends('admin.layout.base')

@section('title')
    {{ $monster->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.monster.info', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="{{ route('admin.monster.info.location', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Локации</a>
                    <a href="{{ route('admin.monster.info.drop', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Дроп</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form action="{{ route('admin.monster.info', ['monster' => $monster->id]) }}" method="post">
                                {{ csrf_field() }}
                                <div class="row form-group pb-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="col-form-label" for="name">Название</label>
                                            <input type="text" class="form-control" name="name" id="name" value="{{ $monster->name }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="lvl">LVL</label>
                                            <input type="text" class="form-control" name="lvl" id="lvl" value="{{ $monster->lvl }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="description">Описание</label>
                                            <textarea class="form-control" name="description" rows="7" id="description"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="col-form-label" for="hp">HP</label>
                                            <input type="text" class="form-control" name="hp" id="hp" value="{{ $monster->hp }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="armor">Защита</label>
                                            <input type="text" class="form-control" name="armor" id="armor" value="{{ $monster->armor }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="dodge">Уворот</label>
                                            <input type="text" class="form-control" name="dodge" id="dodge" value="{{ $monster->dodge }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="critical">Крит</label>
                                            <input type="text" class="form-control" name="critical" id="critical" value="{{ $monster->critical }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="min_dmg">Мин атака</label>
                                            <input type="text" class="form-control" name="min_dmg" id="min_dmg" value="{{ $monster->min_dmg }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="max_dmg">Макс атака</label>
                                            <input type="text" class="form-control" name="max_dmg" id="max_dmg" value="{{ $monster->max_dmg }}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="col-form-label" for="aggression">Агресивность %</label>
                                            <input type="text" class="form-control" id="aggression" name="aggression" value="{{ $monster->aggression }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="exp">Опыт</label>
                                            <input type="text" class="form-control" id="exp" name="exp" value="{{ $monster->exp }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="min_money">Мин монет</label>
                                            <input type="text" class="form-control" id="min_money" name="min_money" value="{{ $monster->min_money }}" placeholder="">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label" for="max_money">Макс монет</label>
                                            <input type="text" class="form-control" id="max_money" name="max_money" value="{{ $monster->max_money }}" placeholder="">
                                        </div>
                                    </div>
                                </div>


                                <div class="row justify-content-start">
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary">Сохранить</button>
                                        <a href="{{ route('admin.monsters') }}" class="btn btn-success">Назад</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection


