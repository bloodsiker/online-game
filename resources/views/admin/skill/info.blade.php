@extends('admin.layout.base')

@section('title')
    {{ $skill->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.skill.info', ['skill' => $skill->id]) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row form-group pb-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="name">Название</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $skill->name }}" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="type">Тип навыка</label>
                                    <select class="form-control" name="type" id="type">
                                        <option value="combat" @if($skill->type === 'combat') selected @endif>Боевой навык</option>
                                        <option value="magic" @if($skill->type === 'magic') selected @endif>Магический навык</option>
                                        <option value="peaceful" @if($skill->type === 'peaceful') selected @endif>Мирный навык</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="description">Описание</label>
                                    <textarea class="form-control" name="description" rows="7"  id="description">{{ $skill->description }}</textarea>
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
                                <a href="{{ route('admin.race') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection


