@extends('admin.layout.base')

@section('title')
    Дроп предмета: {{ $item->name }}
@endsection

@section('body')

    @include('admin.item.navigation', ['item' => $item])

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Выпадает с монстров: {{ $item->name }}</h2>
                    <p class="card-subtitle text-muted">
                        {{ $item->monsters->count() }} привязок
                        · {{ $item->monsters->pluck('name')->unique()->count() }} уникальных имён
                    </p>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="70">ID</th>
                                <th>Монстр</th>
                                <th width="100">Уровень</th>
                                <th width="120">Шанс (%)</th>
                                <th width="170">Количество</th>
                                <th width="100"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($item->monsters->groupBy('name') as $monsterName => $monsters)
                                @foreach($monsters as $monster)
                                    <tr style="vertical-align: middle">
                                        <td>{{ $monster->id }}</td>
                                        @if($loop->first)
                                            <td rowspan="{{ $monsters->count() }}" style="vertical-align: middle">
                                                <strong>{{ $monsterName }}</strong>
                                                @if($monsters->count() > 1)
                                                    <span class="badge badge-info ms-1">{{ $monsters->count() }} ур.</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $monster->lvl }}</td>
                                        <td>{{ rtrim(rtrim(number_format((float) $monster->pivot->drop_chance, 3, '.', ''), '0'), '.') }}%</td>
                                        <td>
                                            @if((int) $monster->pivot->min_count === (int) $monster->pivot->max_count)
                                                {{ $monster->pivot->min_count }} шт.
                                            @else
                                                {{ $monster->pivot->min_count }}–{{ $monster->pivot->max_count }} шт.
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.monster.info', $monster->id) }}" class="btn btn-xs btn-primary">Открыть</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Предмет не добавлен в дроп монстров.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
