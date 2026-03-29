@extends('admin.layout.base')

@section('title')
    Действия на локации
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.action.create') }}" class="btn btn-sm btn-primary">Создать действие</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="200">Alias</th>
                                <th>Название</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listActions as $action)
                                <tr style="vertical-align: middle">
                                    <td>{{ $action->id }}</td>
                                    <td><code>{{ $action->alias }}</code></td>
                                    <td>{{ $action->name }}</td>
                                    <td>
                                        <a href="{{ route('admin.action.info', $action->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Нет действий</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
