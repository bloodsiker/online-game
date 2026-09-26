@extends('admin.layout.base')

@section('title')
    Логи покупок
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form method="get" class="row mb-3">
                        <div class="col-md-2">
                            <select name="source_type" class="form-control" data-plugin-selectTwo>
                                <option value="">— все магазины —</option>
                                @foreach($sourceTypes as $type)
                                    <option value="{{ $type->value }}" @selected($filters['source_type'] === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="user" placeholder="Игрок или ID" value="{{ $filters['user'] }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="structure" placeholder="Структура или ID" value="{{ $filters['structure'] }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="item" placeholder="Предмет или ID" value="{{ $filters['item'] }}">
                        </div>
                        <div class="col-md-1">
                            <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}" title="Дата от">
                        </div>
                        <div class="col-md-1">
                            <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}" title="Дата до">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary">Фильтр</button>
                            <a href="{{ route('admin.purchase_logs.index') }}" class="btn btn-default">Сбросить</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="55">ID</th>
                                <th width="135">Дата</th>
                                <th width="145">Игрок</th>
                                <th width="150">Магазин</th>
                                <th width="170">Структура</th>
                                <th>Предмет</th>
                                <th width="75">Кол-во</th>
                                <th width="115">Монеты</th>
                                <th width="100">Алмазы</th>
                                <th width="125">Баланс после</th>
                                <th width="90">Операция</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr style="vertical-align: middle">
                                    <td>{{ $log->id }}</td>
                                    <td class="small text-muted">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                                    <td>
                                        @if($log->user?->player_id)
                                            <a href="{{ route('admin.player.info', $log->user->player_id) }}">{{ $log->user_name }}</a>
                                        @else
                                            {{ $log->user_name }}
                                        @endif
                                        <small class="text-muted">#{{ $log->user_id ?? '—' }}</small>
                                    </td>
                                    <td><span class="badge badge-info">{{ $log->source_type->label() }}</span></td>
                                    <td>
                                        @if($log->structure_id)
                                            <a href="{{ route('admin.structure.info', $log->structure_id) }}">{{ $log->structure_name ?? '#'.$log->structure_id }}</a>
                                        @else
                                            {{ $log->metadata['reputation_name'] ?? '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->share_item_id)
                                            <a href="{{ route('admin.item.info', $log->share_item_id) }}">{{ $log->item_name }}</a>
                                        @else
                                            {{ $log->item_name }}
                                        @endif
                                        @if(!empty($log->requirements))
                                            <div class="small text-muted">
                                                Обмен: {{ collect($log->requirements)->map(fn ($requirement) => ($requirement['item_name'] ?? '#'.$requirement['share_item_id']).' ×'.$requirement['quantity'])->join(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->quantity, 0, '', ' ') }}</td>
                                    <td>{{ $log->total_price > 0 ? number_format($log->total_price, 0, '', ' ') : '—' }}</td>
                                    <td>{{ $log->total_diamond > 0 ? number_format($log->total_diamond, 0, '', ' ') : '—' }}</td>
                                    <td class="small">
                                        {{ number_format($log->money_balance_after, 0, '', ' ') }} мон.<br>
                                        {{ number_format($log->diamond_balance_after, 0, '', ' ') }} алм.
                                    </td>
                                    <td><code title="{{ $log->purchase_uuid }}">{{ substr($log->purchase_uuid, 0, 8) }}</code></td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">Покупок пока нет</td></tr>
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
