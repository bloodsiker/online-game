@extends('admin.layout.base')

@section('title')
    Создать карту
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.map.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" value="{{ old('slug') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Папка для карт</label>
                            <input type="text" class="form-control" name="folder" value="{{ old('folder') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Родительская карта</label>
                            <select name="parent_id" class="form-control" data-plugin-selectTwo
                                    data-plugin-options='{ "placeholder": "Выберите карту", "allowClear": true }'>
                                <option value=""></option>
                                @foreach($allMaps as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} [{{ $m->id }}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.maps') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@endsection
