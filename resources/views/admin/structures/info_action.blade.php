@extends('admin.layout.base')

@section('title')
    {{ $structure->name }} / Действия
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
                                <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalForm">Добавить действие</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-none">
                                    <thead>
                                    <tr>
                                        <th width="50" class="text-center">ID</th>
                                        <th width="100" class="text-center">Alias</th>
                                        <th>Название</th>
                                        <th width="70"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($structure->actions as $action)
                                        <tr style="vertical-align: baseline">
                                            <td class="text-center">{{ $action->id }}</td>
                                            <td class="text-center">{{ $action->alias }}</td>
                                            <td><a href="{{ route('admin.item.info', ['item' => $action->id]) }}" target="_blank">{{ $action->name }}</a></td>
                                            <td><a href="{{ route('admin.structure.info.action_delete', ['structure' => $structure->id, 'action' => $action->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
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
            <form action="{{ route('admin.structure.info.action', ['structure' => $structure->id]) }}" method="post">
                <header class="card-header">
                    <h2 class="card-title">Добавить действие</h2>
                </header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="form-group">
                                <label class="control-label text-lg-end" for="resourceItem">Действие</label>
                                <select id="resourceItem" name="share_action_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите действие", "allowClear": true }'>
                                    @foreach($allActions as $action)
                                        <option value="{{ $action->id }}">{{ $action->name }}</option>
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


