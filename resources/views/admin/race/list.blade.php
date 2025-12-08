@extends('admin.layout.base')

@section('title')
    Построение
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Сила</th>
                                <th>Ловкость</th>
                                <th>Интуиция</th>
                                <th>Мудрость</th>
                                <th>Интелект</th>
                                <th>Свободные хар</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $race)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $race->id }}</td>
                                    <td>{{ $race->name }}</td>
                                    <td>{{ $race->str }}</td>
                                    <td>{{ $race->agil }}</td>
                                    <td>{{ $race->int }}</td>
                                    <td>{{ $race->mud }}</td>
                                    <td>{{ $race->intel }}</td>
                                    <td>{{ $race->free_stats }}</td>
                                    <td><a href="{{ route('admin.race.info', ['race' => $race->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection


