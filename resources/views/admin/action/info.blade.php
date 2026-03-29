@extends('admin.layout.base')

@section('title')
    Действие: {{ $action->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.action.info', $action->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Alias</label>
                            <input type="text" class="form-control" name="alias" value="{{ $action->alias }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ $action->name }}">
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.action') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-md-7">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Используется в постройках</h2>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Тип</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($action->structures as $structure)
                                <tr style="vertical-align: middle">
                                    <td>{{ $structure->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.structure.info', $structure->id) }}">{{ $structure->name }}</a>
                                    </td>
                                    <td><span class="badge badge-info">{{ $structure->getTypeName() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Нигде не используется</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
