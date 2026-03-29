@extends('admin.layout.base')

@section('title')
    Монстры
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.monster.create') }}" class="btn btn-success btn-sm">Создать монстра</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>Название</th>
                                <th width="50">Ур.</th>
                                <th width="80">HP</th>
                                <th width="80">Атака</th>
                                <th width="60">Опыт</th>
                                <th width="100">Монеты</th>
                                <th width="60">Босс</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listMonsters as $monster)
                                <tr style="vertical-align: middle">
                                    <td>{{ $monster->id }}</td>
                                    <td>
                                        @if($monster->image)
                                            <img src="{{ $monster->image }}" style="width:36px" alt="">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.monster.info', $monster->id) }}">{{ $monster->name }}</a>
                                    </td>
                                    <td>{{ $monster->lvl }}</td>
                                    <td>{{ $monster->hp }}</td>
                                    <td>{{ $monster->min_dmg }} – {{ $monster->max_dmg }}</td>
                                    <td>{{ $monster->exp }}</td>
                                    <td>{{ $monster->min_money }} – {{ $monster->max_money }}</td>
                                    <td>
                                        @if($monster->is_boss)
                                            <span class="badge badge-danger">Босс</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.monster.info', $monster->id) }}" class="btn btn-xs btn-primary">Детали</a></td>
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