@extends('admin.layout.base')

@section('title')
    {{ $monster->name }} / Локации
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.monster.info', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="{{ route('admin.monster.info.location', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Локации</a>
                    <a href="{{ route('admin.monster.info.drop', ['monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Дроп</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="right mb-3">
                                <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalForm">Добавить локацию</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-none">
                                    <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th>Название</th>
                                        <th width="70"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($monster->locations as $location)
                                        <tr style="vertical-align: baseline">
                                            <td class="text-center">{{ $location->id }}</td>
                                            <td>
                                                <a href="">[{{ $location->map->name }}]</a>
                                                &nbsp;
                                                <a href="" target="_blank">{{ $location->name }} [{{ $location->id }}]</a>
                                            </td>
                                            <td><a href="{{ route('admin.monster.info.delete_location', ['monster' => $monster->id, 'location' => $location->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
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
            <form action="{{ route('admin.monster.info.location', ['monster' => $monster->id]) }}" method="post">
                <header class="card-header">
                    <h2 class="card-title">Добавить локацию</h2>
                </header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="form-group">
                                <label class="control-label text-lg-end" for="location_id"> Локация</label>
                                <select id="location_id" name="location_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите локацию", "allowClear": true }'>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
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


