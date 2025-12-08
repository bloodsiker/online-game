@extends('admin.layout.base')

@section('title')
    NPC
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.npc.create') }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Добавить НПС</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Имя</th>
                                <th>Локация</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $npc)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $npc->id }}</td>
                                    <td><a href="{{ route('admin.npc.info', ['npc' => $npc->id]) }}">{{ $npc->name }}</a></td>
                                    <td>{{ $npc->location?->name }} @if($npc->location) [{{ $npc->location_id }}]@endif</td>
                                    <td><a href="{{ route('admin.npc.info', ['npc' => $npc->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


