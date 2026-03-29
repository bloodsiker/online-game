@extends('admin.layout.base')

@section('title')
    Локации
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.location.create') }}" class="btn btn-sm btn-primary">Создать локацию</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Карта</th>
                                <th width="100">Монстров</th>
                                <th width="80">Заперта</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listLocations as $location)
                                <tr style="vertical-align: middle">
                                    <td>{{ $location->id }}</td>
                                    <td><a href="{{ route('admin.location.info', $location->id) }}">{{ $location->name }}</a></td>
                                    <td>{{ $location->map?->name ?? '—' }}</td>
                                    <td>{{ $location->count_monster }}</td>
                                    <td>
                                        @if($location->is_locked)
                                            <span class="badge badge-danger">Да</span>
                                        @else
                                            <span class="badge badge-success">Нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.location.info', $location->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Нет локаций</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
