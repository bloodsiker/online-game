@extends('admin.layout.base')

@section('title')
    Предметы
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
                                <th width="45"></th>
                                <th>Название</th>
                                <th>Тип</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listItems as $item)
                                <tr style="vertical-align: baseline">
                                    <td>{{ $item->id }}</td>
                                    <td><img src="{{ $item->image }}" style="width: 40px" alt=""></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->getTypeName() }}</td>
                                    <td><a href="{{ route('admin.item.info', ['item' => $item->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-primary">Детали</a></td>
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


