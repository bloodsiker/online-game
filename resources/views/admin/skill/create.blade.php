@extends('admin.layout.base')

@section('title')
    Добавить навык
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.skill.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="row form-group pb-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="name">Название</label>
                                    <input type="text" class="form-control" name="name" id="name" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="type">Тип навыка</label>
                                    <select class="form-control" name="type" id="type">
                                        <option value="combat">Боевой навык</option>
                                        <option value="magic">Магический навык</option>
                                        <option value="peaceful">Мирный навык</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="description">Описание</label>
                                    <textarea class="form-control" name="description" rows="7" id="description"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-3">

                            </div>

                            <div class="col-lg-3">

                            </div>
                        </div>


                        <div class="row justify-content-start">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Сохранить</button>
                                <a href="{{ route('admin.skills') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection


