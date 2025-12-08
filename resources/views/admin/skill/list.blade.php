@extends('admin.layout.base')

@section('title')
    Навыки
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.skill.create') }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Добавить Навык</a>
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
                                <th>Тип навыка</th>
                                <th>Описание</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $skill)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $skill->id }}</td>
                                    <td>{{ $skill->name }}</td>
                                    <td>{{ $skill->type }}</td>
                                    <td>{!! $skill->description !!}</td>
                                    <td><a href="{{ route('admin.skill.info', ['skill' => $skill->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


