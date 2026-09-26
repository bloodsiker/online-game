@extends('admin.layout.base')

@section('title', 'Влияние по территориям')

@section('body')
    <section class="card">
        <div class="card-body">
            <p class="text-muted">Пороги влияния задаются отдельно для каждой карты. Влияние не расходуется при покупке товаров.</p>
            <table class="table table-bordered table-striped">
                <thead><tr><th>ID</th><th>Территория</th><th>Локаций</th><th>Уровней</th><th></th></tr></thead>
                <tbody>
                @foreach($maps as $map)
                    <tr>
                        <td>{{ $map->id }}</td>
                        <td>{{ $map->name }}</td>
                        <td>{{ $map->locations_count }}</td>
                        <td>{{ $map->influence_levels_count }}</td>
                        <td><a class="btn btn-sm btn-primary" href="{{ route('admin.influence.show', $map) }}">Настроить</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
