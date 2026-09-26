@extends('admin.layout.base')

@section('title')Мировые события@endsection

@section('body')
<section class="card"><div class="card-body">
    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if(session($key))<div class="alert alert-{{ $class }}">{{ session($key) }}</div>@endif
    @endforeach
    @if($filterItem)
        <div class="alert alert-info">
            Показаны события, в которых участвует предмет
            <a href="{{ route('admin.item.info', $filterItem->id) }}"><b>[{{ $filterItem->id }}] {{ $filterItem->name }}</b></a>.
        </div>
    @endif
    <form method="get" action="{{ route('admin.event.world-events.index') }}" class="mb-3">
        @if($filters['share_item_id'] !== null)
            <input type="hidden" name="share_item_id" value="{{ $filters['share_item_id'] }}">
        @endif
        <div class="row align-items-end">
            <div class="col-md-4">
                <label for="world-event-map-filter">Карта проведения</label>
                <select id="world-event-map-filter" class="form-control" name="map_id">
                    <option value="">Все карты</option>
                    @foreach($maps as $map)
                        <option value="{{ $map->id }}" @selected($filters['map_id'] === $map->id)>[{{ $map->id }}] {{ $map->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="world-event-influence-filter">Начисляемое влияние</label>
                <select id="world-event-influence-filter" class="form-control" name="influence_map_id">
                    <option value="">Все влияния</option>
                    @foreach($maps as $map)
                        <option value="{{ $map->id }}" @selected($filters['influence_map_id'] === $map->id)>[{{ $map->id }}] {{ $map->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary" type="submit">Фильтровать</button>
                <a class="btn btn-default" href="{{ route('admin.event.world-events.index') }}">Сбросить</a>
                <a class="btn btn-success" href="{{ route('admin.event.world-events.create') }}">Создать событие</a>
            </div>
        </div>
    </form>
    <div class="table-responsive"><table class="table table-bordered table-hover">
        <thead><tr><th>ID</th><th>Событие</th><th>Карта / влияние</th><th>Цель</th><th>Общий / личный лимит</th><th>Следующий запуск</th><th>Состояние</th><th></th></tr></thead>
        <tbody>
        @forelse($events as $event)
            @php($stage = $event->activeRun?->currentStage ?? $event->stages->first())
            <tr class="{{ $event->is_active ? '' : 'text-muted' }}">
                <td>{{ $event->id }}</td>
                <td><a href="{{ route('admin.event.world-events.edit', $event) }}"><b>{{ $event->title }}</b></a><br><small>{{ $event->locations->isEmpty() ? 'Все локации карты' : $event->locations->count().' локаций' }}</small></td>
                <td>{{ $event->map?->name }}<br><small>Влияние: {{ $event->influenceMap?->name ?? $event->map?->name }}</small></td><td>{{ $event->stages->count() }} этап(а)<br><small>{{ $stage?->title }}: {{ $stage?->objective_type?->label() }} — {{ $stage?->targets?->map(fn($target) => $target->item?->name ?? $target->monster?->name)->filter()->join(', ') }}</small></td>
                <td>{{ $stage?->global_limit }} / {{ $stage?->player_limit }}</td>
                <td>{{ $event->next_start_at?->format('d.m.Y H:i') ?? 'вручную' }}</td>
                <td>@if($event->activeRun)<span class="badge badge-success">Этап {{ $stage?->position }}: {{ $event->activeRun->collected_count }}/{{ $stage?->global_limit }}</span>@else — @endif</td>
                <td class="text-nowrap">
                    @if($event->activeRun)
                        <form class="d-inline" method="post" action="{{ route('admin.event.world-events.finish', $event) }}">@csrf<button class="btn btn-xs btn-warning">Завершить</button></form>
                    @else
                        <form class="d-inline" method="post" action="{{ route('admin.event.world-events.start', $event) }}">@csrf<button class="btn btn-xs btn-success">Запустить</button></form>
                    @endif
                    <a class="btn btn-xs btn-primary" href="{{ route('admin.event.world-events.edit', $event) }}">Изменить</a>
                    <form class="d-inline" method="post" action="{{ route('admin.event.world-events.destroy', $event) }}" onsubmit="return confirm('Удалить событие?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="8" class="text-center text-muted">События ещё не созданы.</td></tr>@endforelse
        </tbody>
    </table></div>
</div></section>
@endsection
