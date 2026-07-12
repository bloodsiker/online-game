@extends('admin.layout.base')

@section('title')
    Расы
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.race.create') }}" class="btn btn-sm btn-primary">Создать расу</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="80">Сила</th>
                                <th width="80">Ловкость</th>
                                <th width="80">Интуиция</th>
                                <th width="80">Мудрость</th>
                                <th width="80">Интеллект</th>
                                <th width="90">Выносл.</th>
                                <th width="100">Своб. хар.</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listRaces as $race)
                                <tr style="vertical-align: middle">
                                    <td>{{ $race->id }}</td>
                                    <td><a href="{{ route('admin.race.info', $race->id) }}">{{ $race->name }}</a></td>
                                    <td>{{ $race->strength }}</td>
                                    <td>{{ $race->agility }}</td>
                                    <td>{{ $race->intuition }}</td>
                                    <td>{{ $race->wisdom }}</td>
                                    <td>{{ $race->intelligence }}</td>
                                    <td>{{ $race->endurance }}</td>
                                    <td>{{ $race->free_stats }}</td>
                                    <td>
                                        <a href="{{ route('admin.race.info', $race->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">Нет рас</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
