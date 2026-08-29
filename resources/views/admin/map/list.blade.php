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

                    <form method="get" action="{{ route('admin.maps') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Поиск</label>
                                    <input type="text"
                                           name="q"
                                           class="form-control"
                                           value="{{ $filters['q'] }}"
                                           placeholder="Название, slug или папка">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Родительская карта</label>
                                    <select name="parent_id" class="form-control">
                                        <option value="">Все карты</option>
                                        @foreach($allMaps as $allMap)
                                            <option value="{{ $allMap->id }}" @selected($filters['parent_id'] === (string) $allMap->id)>
                                                [{{ $allMap->id }}] {{ $allMap->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">ID локации</label>
                                    <input type="number"
                                           name="location_id"
                                           class="form-control"
                                           value="{{ $filters['location_id'] }}"
                                           placeholder="Локация на карте">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">Фильтровать</button>
                                        <a href="{{ route('admin.maps') }}" class="btn btn-default btn-sm">Сбросить</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

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
                    <div class="mt-3">
                        {{ $listMaps->onEachSide(2)->links('admin.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
