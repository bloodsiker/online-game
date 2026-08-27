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

                    <form method="get" action="{{ route('admin.items') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Поиск</label>
                                    <input type="text"
                                           name="q"
                                           class="form-control"
                                           value="{{ $filters['q'] }}"
                                           placeholder="Название или описание">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select name="type" class="form-control">
                                        <option value="">Все типы</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Редкость</label>
                                    <select name="rarity" class="form-control">
                                        <option value="">Все редкости</option>
                                        @foreach($rarities as $rarity)
                                            <option value="{{ $rarity->value }}" @selected($filters['rarity'] === $rarity->value)>
                                                {{ $rarity->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Слот</label>
                                    <select name="slot" class="form-control">
                                        <option value="">Все слоты</option>
                                        @foreach($slots as $slot)
                                            <option value="{{ $slot->value }}" @selected($filters['slot'] === $slot->value)>
                                                {{ $slot->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; margin-top: 12px;">
                            <button class="btn btn-primary btn-sm">Фильтровать</button>
                            <a href="{{ route('admin.items') }}" class="btn btn-default btn-sm">Сбросить</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>Название</th>
                                <th width="150">Тип</th>
                                <th width="110">Редкость</th>
                                <th width="120">Слот</th>
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
                                    <td>{{ $item->slot?->label() ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.item.info', $item->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <form action="{{ route('admin.item.duplicate', $item->id) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-default">Дубль</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Нет предметов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $listItems->onEachSide(2)->links('admin.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
