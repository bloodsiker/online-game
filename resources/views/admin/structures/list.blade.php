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
                                <th width="100">Тип</th>
                                <th>Название</th>
                                <th>Локация</th>
                                <th>НПС</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listStructures as $structure)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $structure->id }}</td>
                                    <td>{{ $structure->getTypeName() }}</td>
                                    <td>{{ $structure->name }}</td>
                                    <td>{{ $structure->location?->name }} @if($structure->location)[{{ $structure->location_id }}]@endif</td>
                                    <td>{{ $structure->npc?->name }} @if($structure->npc)[{{ $structure->npc->location_id }}]@endif</td>
                                    <td><a href="{{ route('admin.structure.info', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


