@extends('admin.layout.base')

@section('title')
    Карты
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.map.create') }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Добавить карту</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $map)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $map->id }}</td>
                                    <td><a href="{{ route('admin.map.info', ['map' => $map->id]) }}">{{ $map->name }}</a></td>
                                    <td>{{ $map->slug }}</td>
                                    <td><a href="{{ route('admin.map.info', ['map' => $map->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection


