@extends('admin.layout.base')

@section('title')
    {{ $structure->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.structure.info', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="{{ route('admin.structure.info.shop', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Магазин</a>
                    <a href="{{ route('admin.structure.info.action', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Действия</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.structure.info', ['structure' => $structure->id]) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row form-group pb-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="name">Название</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $structure->name }}" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="type">Тип</label>
                                    <select class="form-control" name="type" id="type" required>
                                        @foreach(App\Models\Structure::TYPES as $key => $type)
                                            <option value="{{ $key }}" @if($structure->type === $key) selected @endif>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="location_id">Локация</label>
                                    <select class="form-control" name="location_id" id="location_id">
                                        <option value=""></option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" @if($structure->location_id === $location->id) selected @endif>{{ $location->name }} [{{$location->id}}]</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="location_id">Локация</label>
                                    <select class="form-control" name="location_id" id="location_id">
                                        <option value=""></option>
                                        @foreach($npcs as $npc)
                                            <option value="{{ $npc->id }}" @if($structure->npc_id === $npc->id) selected @endif>{{ $npc->name }} @if($npc->location_id)[{{$npc->location_id}}] @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4">

                            </div>
                        </div>


                        <div class="row justify-content-start">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Сохранить</button>
                                <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection


