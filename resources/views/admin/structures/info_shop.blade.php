@extends('admin.layout.base')

@section('title')
    {{ $structure->name }} / Предметы
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.structure.info', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="{{ route('admin.structure.info.shop', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Магазин</a>
                    <a href="{{ route('admin.structure.info.action', ['structure' => $structure->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Действия</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="right mb-3">
                                <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalForm">Добавить предмет</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-none">
                                    <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th width="40" class="text-center">Картинка</th>
                                        <th>Название</th>
                                        <th width="100">Цена</th>
                                        <th width="70"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($structure->shopItems as $item)
                                        <tr style="vertical-align: baseline">
                                            <td class="text-center">{{ $item->id }}</td>
                                            <td class="text-center"><img src="{{ $item->image }}" width="40" alt=""></td>
                                            <td><a href="{{ route('admin.item.info', ['item' => $item->id]) }}" target="_blank">{{ $item->name }}</a></td>
                                            <td>{{ number_format($item->price, 0, '', ' ') }}</td>
                                            <td><a href="{{ route('admin.structure.info.shop_delete_item', ['structure' => $structure->id, 'item' => $item->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="modalForm" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.structure.info.shop', ['structure' => $structure->id]) }}" method="post">
                <header class="card-header">
                    <h2 class="card-title">Добавить предмет</h2>
                </header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="form-group">
                                <label class="control-label text-lg-end" for="resourceItem">Ресурс</label>
                                <select id="resourceItem" name="share_item_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите предмет", "allowClear": true }'>
                                    @foreach($allItems as $itemShop)
                                        <option value="{{ $itemShop->id }}">{{ $itemShop->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary">Сохранить</button>
                            <button class="btn btn-default modal-dismiss">Отмена</button>
                        </div>
                    </div>
                </footer>
            </form>
        </section>
    </div>
@endsection


