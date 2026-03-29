@extends('admin.layout.base')

@section('title')
    Квесты
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.quest.create') }}" class="btn btn-primary btn-sm">Создать квест</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="140">Тип</th>
                                <th width="80">Заданий</th>
                                <th width="80">Наград</th>
                                <th width="80">Активен</th>
                                <th width="60"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($quests as $quest)
                                <tr style="vertical-align: middle">
                                    <td>{{ $quest->id }}</td>
                                    <td><a href="{{ route('admin.quest.info', $quest->id) }}">{{ $quest->title }}</a></td>
                                    <td><span class="badge badge-info">{{ $quest->type->label() }}</span></td>
                                    <td>{{ $quest->objectives_count }}</td>
                                    <td>{{ $quest->rewards_count }}</td>
                                    <td>
                                        @if($quest->is_active)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.quest.info', $quest->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Нет квестов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection