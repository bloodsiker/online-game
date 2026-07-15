@extends('admin.layout.base')

@section('title')
    Предметы
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.item.create') }}" class="btn btn-sm btn-primary">Создать предмет</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>Название</th>
                                <th width="150">Тип</th>
                                <th width="110">Редкость</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listItems as $item)
                                <tr style="vertical-align: middle">
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ $item->image }}" style="width:36px;height:36px;object-fit:contain;" alt="">
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.item.info', $item->id) }}">{{ $item->name }}</a></td>
                                    <td><span class="badge badge-info">{{ $item->getTypeName() }}</span></td>
                                    <td><span class="badge" style="background-color:{{ $item->rarity->color() }};color:#fff;">{{ $item->rarity->label() }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.item.info', $item->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Нет предметов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection