@extends('admin.layout.base')

@section('title') Погода @endsection

@section('body')
<div class="row"><div class="col-md-12"><section class="card"><div class="card-body">
    <h4>Погода по картам</h4>
    <p class="text-muted mb-3">
        Смена погоды — задача планировщика «Смена погоды» (раз в минуту): явление 20–40 мин, затем ясно 30–60 мин; после грозы в половине случаев — радуга.
        Погода выпадает только на картах с весами больше 0. Радугу вручную включить можно, но в весах её нет — она приходит после грозы.
        Ручное включение заменяет текущую погоду и действует указанное число минут, дальше смена идёт как обычно.
    </p>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0" style="vertical-align: middle;">
            <thead>
            <tr>
                <th>Карта</th>
                <th>Сейчас</th>
                <th>Включить вручную</th>
                <th>Веса (0 — не выпадает)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($maps as $map)
                @php
                    $row = $current->get($map->id);
                    $active = $row !== null && $row->isActive();
                    $mapWeights = $weights->get($map->id, collect());
                @endphp
                <tr>
                    <td><b>{{ $map->title }}</b> <span class="text-muted">#{{ $map->id }}</span></td>
                    <td>
                        @if($active)
                            <span class="badge {{ $row->weather->isGood() ? 'badge-success' : 'badge-danger' }}">{{ $row->weather->label() }}</span>
                            <div class="small text-muted">до {{ $row->ends_at->format('d.m H:i') }}</div>
                        @elseif($row !== null)
                            <span class="badge badge-default">Ясно</span>
                            <div class="small text-muted">до {{ $row->ends_at->format('d.m H:i') }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <form method="post" action="{{ route('admin.weather.force', $map->id) }}" class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            @csrf
                            <select name="weather" class="form-control form-control-sm" style="width: auto;">
                                <option value="">Ясно</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="minutes" value="30" min="1" max="1440" class="form-control form-control-sm" style="width: 80px;" title="Минут">
                            <button class="btn btn-sm btn-primary">Включить</button>
                        </form>
                    </td>
                    <td>
                        <form method="post" action="{{ route('admin.weather.weights', $map->id) }}" class="d-flex flex-wrap align-items-end" style="gap: 6px;">
                            @csrf
                            @foreach($types as $type)
                                @continue($type === \App\Modules\Weather\Domain\Enums\WeatherType::RAINBOW)
                                <label class="small mb-0" style="width: 78px;">
                                    {{ $type->label() }}
                                    <input type="number" name="weights[{{ $type->value }}]" value="{{ (int) ($mapWeights[$type->value] ?? 0) }}" min="0" max="100" class="form-control form-control-sm">
                                </label>
                            @endforeach
                            <button class="btn btn-sm btn-success">Сохранить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div></section></div></div>
@endsection
