@extends('admin.layout.base')

@section('title')
    Лог действий с предметами
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form method="get" class="row mb-3">
                        <div class="col-md-3">
                            <select name="action" class="form-control" data-plugin-selectTwo>
                                <option value="">— любое действие —</option>
                                @foreach($actionTypes as $type)
                                    <option value="{{ $type->value }}" @selected($filters['action'] === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="user" placeholder="Игрок" value="{{ $filters['user'] }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="item" placeholder="Предмет" value="{{ $filters['item'] }}">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary">Фильтр</button>
                            <a href="{{ route('admin.item_logs') }}" class="btn btn-default">Сбросить</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="140">Дата</th>
                                <th width="160">Игрок</th>
                                <th>Предмет</th>
                                <th width="110">Действие</th>
                                <th width="80">Кол-во</th>
                                <th width="100">Деньги</th>
                                <th width="160">Получатель</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr style="vertical-align: middle">
                                    <td>{{ $log->id }}</td>
                                    <td class="small text-muted">{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ $log->user?->name ?? '—' }}</td>
                                    <td>
                                        {{ $log->item_name }}
                                        @if($log->upgrade_lvl > 0)
                                            <span class="badge badge-info">+{{ $log->upgrade_lvl }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badge = match($log->action) {
                                                \App\Modules\Item\Domain\Enums\ItemActionType::DROP => 'badge-secondary',
                                                \App\Modules\Item\Domain\Enums\ItemActionType::SELL => 'badge-success',
                                                \App\Modules\Item\Domain\Enums\ItemActionType::GIVE => 'badge-primary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $log->action->label() }}</span>
                                    </td>
                                    <td>{{ $log->count }}</td>
                                    <td>{{ $log->money !== null ? number_format($log->money, 0, '', ' ') : '—' }}</td>
                                    <td>{{ $log->targetUser?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Записей нет</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $logs->links() }}
                </div>
            </section>
        </div>
    </div>

@endsection
