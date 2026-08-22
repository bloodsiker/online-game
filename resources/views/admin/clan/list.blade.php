@extends('admin.layout.base')

@section('title')
    Кланы
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
                                <th width="45"></th>
                                <th>Название</th>
                                <th>Владелец</th>
                                <th width="60">Ур.</th>
                                <th width="80">Членов</th>
                                <th width="110">Очки</th>
                                <th width="120">Казна</th>
                                <th width="60"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($clans as $clan)
                                <tr style="vertical-align: middle">
                                    <td>{{ $clan->id }}</td>
                                    <td>
                                        @if($clan->icon)
                                            <img src="{{ resolve_storage_image_url($clan->icon) }}" style="width:32px;height:32px;object-fit:contain;" alt="{{ $clan->name }}">
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.clan.info', $clan->id) }}">{{ $clan->name }}</a></td>
                                    <td>{{ $clan->owner?->name ?? '—' }}</td>
                                    <td>{{ $clan->lvl }}</td>
                                    <td>{{ $clan->members_count }}</td>
                                    <td>{{ number_format($clan->points, 0, '', ' ') }}</td>
                                    <td>{{ number_format($clan->treasury, 0, '', ' ') }}</td>
                                    <td>
                                        <a href="{{ route('admin.clan.info', $clan->id) }}" class="btn btn-xs btn-primary">Просмотр</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Нет кланов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
