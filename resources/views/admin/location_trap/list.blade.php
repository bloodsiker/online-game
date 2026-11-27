@extends('admin.layout.base')

@section('title')
    Ловушки локаций
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.location-trap.create') }}" class="btn btn-sm btn-primary">Создать ловушку</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Локация</th>
                                <th width="110">Направление</th>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Параметры</th>
                                <th width="80">Активна</th>
                                <th width="130"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($traps as $trap)
                                <tr style="vertical-align: middle">
                                    <td>{{ $trap->id }}</td>
                                    <td>[{{ $trap->location_id }}] {{ $trap->location?->name }}</td>
                                    <td>{{ ['north' => 'Север', 'south' => 'Юг', 'east' => 'Восток', 'west' => 'Запад'][$trap->direction] ?? 'Любое' }}</td>
                                    <td>{{ $trap->name }}</td>
                                    <td>{{ isset($types[$trap->type]) ? $types[$trap->type]->label() : $trap->type }}</td>
                                    <td><small class="text-muted">{{ collect($trap->config ?? [])->map(fn ($v, $k) => $k.': '.$v)->implode(', ') }}</small></td>
                                    <td>{{ $trap->is_active ? 'Да' : 'Нет' }}</td>
                                    <td>
                                        <a href="{{ route('admin.location-trap.info', $trap->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <a href="{{ route('admin.location-trap.delete', $trap->id) }}"
                                           onclick="return confirm('Удалить ловушку #{{ $trap->id }}?')"
                                           class="btn btn-xs btn-danger ml-1">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Нет ловушек</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
