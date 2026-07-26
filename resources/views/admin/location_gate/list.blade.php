@extends('admin.layout.base')

@section('title')
    Врата локаций
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.location-gate.create') }}" class="btn btn-sm btn-primary">Создать врата</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Откуда</th>
                                <th>Куда</th>
                                <th>Предмет</th>
                                <th width="130">Режим</th>
                                <th width="90">Расходуется</th>
                                <th>Подпись кнопки</th>
                                <th width="130"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($gates as $gate)
                                <tr style="vertical-align: middle">
                                    <td>{{ $gate->id }}</td>
                                    <td>[{{ $gate->from_location_id }}] {{ $gate->fromLocation?->name }}</td>
                                    <td>[{{ $gate->to_location_id }}] {{ $gate->toLocation?->name }}</td>
                                    <td>[{{ $gate->share_item_id }}] {{ $gate->shareItem?->name }}</td>
                                    <td>{{ $gate->mode === 'teleport_use' ? 'Использовать ключ' : 'Ключ в сумке' }}</td>
                                    <td>{{ $gate->consume_item ? 'Да' : 'Нет' }}</td>
                                    <td>{{ $gate->button_label }}</td>
                                    <td>
                                        <a href="{{ route('admin.location-gate.info', $gate->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <a href="{{ route('admin.location-gate.delete', $gate->id) }}"
                                           onclick="return confirm('Удалить врата #{{ $gate->id }}?')"
                                           class="btn btn-xs btn-danger ml-1">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Нет врат</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection