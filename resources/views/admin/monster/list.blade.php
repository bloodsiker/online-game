@extends('admin.layout.base')

@section('title')
    Монстры
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.monster.create') }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Добавить Монстра</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Монеты</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listMonsters as $monster)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $monster->id }}</td>
                                    <td><a href="{{ route('admin.monster.info', ['monster' => $monster->id]) }}">{{ $monster->name }} {{ $monster->lvl }}</a></td>
                                    <td>{{ $monster->min_money }} - {{ $monster->max_money }}</td>
                                    <td><a href="{{ route('admin.monster.info', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


