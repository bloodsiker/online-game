@extends('admin.layout.base')

@section('title')
    Карты
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.map.create') }}" class="btn btn-sm btn-primary">Создать карту</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Папка</th>
                                <th>Родительская</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listMaps as $map)
                                <tr style="vertical-align: middle">
                                    <td>{{ $map->id }}</td>
                                    <td><a href="{{ route('admin.map.info', $map->id) }}">{{ $map->name }}</a></td>
                                    <td><code>{{ $map->slug }}</code></td>
                                    <td>{{ $map->folder }}</td>
                                    <td>{{ $map->parent ? '[' . $map->parent->id . '] ' . $map->parent->name : '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.map.info', $map->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Нет карт</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
