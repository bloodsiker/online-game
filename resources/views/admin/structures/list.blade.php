@extends('admin.layout.base')

@section('title')
    Построение
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.structure.create') }}" class="btn btn-primary">Добавить построение</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="160">Тип</th>
                                <th>Название</th>
                                <th>Локация</th>
                                <th>НПС</th>
                                <th width="80">Товаров</th>
                                <th width="80">Действий</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listStructures as $structure)
                                <tr style="vertical-align: middle">
                                    <td>{{ $structure->id }}</td>
                                    <td><span class="badge badge-info">{{ $structure->getTypeName() }}</span></td>
                                    <td><a href="{{ route('admin.structure.info', $structure->id) }}">{{ $structure->name }}</a></td>
                                    <td>{{ $structure->location ? '[' . $structure->location_id . '] ' . $structure->location->name : '—' }}</td>
                                    <td>{{ $structure->npc?->name ?? '—' }}</td>
                                    <td>{{ $structure->shop_items_count }}</td>
                                    <td>{{ $structure->actions_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.structure.info', $structure->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Нет построений</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
