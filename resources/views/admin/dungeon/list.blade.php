@extends('admin.layout.base')

@section('title')
    Данжи
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="130">Активен</th>
                                <th>Старт</th>
                                <th>Выход</th>
                                <th>Смерть</th>
                                <th>Точка смерти</th>
                                <th width="90"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($dungeons as $dungeon)
                                <tr style="vertical-align: middle">
                                    <td>{{ $dungeon->id }}</td>
                                    <td><a href="{{ route('admin.dungeon.info', $dungeon->id) }}">{{ $dungeon->name }}</a></td>
                                    <td>
                                        @if($dungeon->is_active)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-danger">Нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dungeon->firstLocation)
                                            [{{ $dungeon->firstLocation->id }}] {{ $dungeon->firstLocation->name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($dungeon->exitLocation)
                                            [{{ $dungeon->exitLocation->id }}] {{ $dungeon->exitLocation->name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $dungeon->death_behavior->label() }}</td>
                                    <td>
                                        @if($dungeon->deathReturnLocation)
                                            [{{ $dungeon->deathReturnLocation->id }}] {{ $dungeon->deathReturnLocation->name }}
                                        @else
                                            По умолчанию
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.dungeon.info', $dungeon->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Нет данжей</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
