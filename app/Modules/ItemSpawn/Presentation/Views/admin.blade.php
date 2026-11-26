@extends('admin.layout.base')

@section('title') Появление предметов @endsection

@section('body')
@foreach($rules as $rule)
<div class="row mb-3"><div class="col-md-12"><section class="card"><div class="card-body">
    <h4>{{ $rule->title }} <small class="text-muted">— карта «{{ $maps[$rule->map_id] ?? $rule->map_id }}»</small></h4>
    <p class="text-muted">
        Раз в интервал на локациях карты появляется до «за тик» предметов, но не больше лимита карты (считаются предметы правила, лежащие на карте).
        Во время дождя (гроза и радуга) за тик — в множитель раз больше. Подбираются через «Искать здесь», без профессии.
        Предмет лежит указанное время и затем удаляется. Последний запуск: {{ $rule->last_run_at ?? '—' }}.
    </p>
    <form method="post" action="{{ route('admin.item_spawns.rule', $rule->id) }}" class="row align-items-end">
        @csrf
        <div class="col-md-3 mb-2"><label>Название</label><input class="form-control" name="title" value="{{ $rule->title }}" required></div>
        <div class="col-md-1 mb-2"><label>За тик</label><input class="form-control" type="number" name="per_tick" min="0" value="{{ $rule->per_tick }}" required></div>
        <div class="col-md-2 mb-2"><label>Интервал, мин</label><input class="form-control" type="number" name="interval_minutes" min="1" value="{{ $rule->interval_minutes }}" required></div>
        <div class="col-md-2 mb-2"><label>Лимит на карте</label><input class="form-control" type="number" name="map_max" min="0" value="{{ $rule->map_max }}" required></div>
        <div class="col-md-2 mb-2"><label>Живёт, мин</label><input class="form-control" type="number" name="ttl_minutes" min="1" value="{{ $rule->ttl_minutes }}" required></div>
        <div class="col-md-1 mb-2"><label>×Дождь</label><input class="form-control" type="number" step="0.1" name="rain_multiplier" min="1" value="{{ $rule->rain_multiplier }}" required></div>
        <div class="col-md-1 mb-2"><label><input type="checkbox" name="is_active" value="1" @checked($rule->is_active)> Вкл.</label></div>
        <div class="col-md-12 mb-3"><button class="btn btn-primary">Сохранить правило</button></div>
    </form>

    <h5>Предметы правила</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-3">
            <thead><tr><th>Предмет</th><th width="120">Вес</th><th width="120">Кол-во от</th><th width="120">до</th><th width="200"></th></tr></thead>
            <tbody>
            @foreach($rule->items as $item)
                <tr>
                    <form method="post" action="{{ route('admin.item_spawns.item', [$rule->id, $item->id]) }}">
                        @csrf
                        <td><a href="{{ route('admin.item.info', ['item' => $item->share_item_id]) }}" target="_blank">{{ $item->title }}</a> <span class="text-muted">#{{ $item->share_item_id }}</span></td>
                        <td><input class="form-control form-control-sm" type="number" name="weight" min="0" value="{{ $item->weight }}"></td>
                        <td><input class="form-control form-control-sm" type="number" name="min_count" min="1" value="{{ $item->min_count }}"></td>
                        <td><input class="form-control form-control-sm" type="number" name="max_count" min="1" value="{{ $item->max_count }}"></td>
                        <td><button class="btn btn-sm btn-success">Сохранить</button>
                    </form>
                    <form method="post" action="{{ route('admin.item_spawns.item.delete', [$rule->id, $item->id]) }}" style="display:inline" onsubmit="return confirm('Убрать предмет из правила?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Убрать</button>
                    </form></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <form method="post" action="{{ route('admin.item_spawns.item.add', $rule->id) }}" class="row align-items-end">
        @csrf
        <div class="col-md-3 mb-2"><label>ID предмета</label><input class="form-control" type="number" name="share_item_id" min="1" required></div>
        <div class="col-md-2 mb-2"><label>Вес</label><input class="form-control" type="number" name="weight" min="1" value="1" required></div>
        <div class="col-md-2 mb-2"><button class="btn btn-default">Добавить предмет</button></div>
    </form>
</div></section></div></div>
@endforeach
@if($rules->isEmpty())
    <div class="alert alert-info">Правил пока нет.</div>
@endif
@endsection
