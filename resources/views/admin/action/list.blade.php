@extends('admin.layout.base')

@section('title')
    Действия на локации
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
                                <th>Alias</th>
                                <th>название</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listActions as $action)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $action->id }}</td>
                                    <td>{{ $action->alias }}</td>
                                    <td>{{ $action->name }}</td>
                                    <td><a href="{{ route('admin.action.info', ['action' => $action->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


