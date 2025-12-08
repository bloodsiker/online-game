@extends('admin.layout.base')

@section('title')
    {{ $map->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.map.info', ['map' => $map->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="{{ route('admin.map.location', ['map' => $map->id]) }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Локации</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.map.info', ['map' => $map->id]) }}" method="post" enctype='multipart/form-data'>
                        {{ csrf_field() }}
                        <div class="row form-group pb-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="name">Название</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $map->name }}" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="control-label text-lg-end pt-2" for="parent_id">Родительская карта</label>
                                    <select id="parent_id" name="parent_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите карту", "allowClear": true }'>
                                        <option value="" selected></option>
                                        @foreach($maps as $mapList)
                                            <option value="{{ $mapList->id }}" @if($map->parent_id === $mapList->id) selected @endif>{{ $mapList->name }} [{{ $mapList->id }}]</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="slug">Slug</label>
                                    <input type="text" class="form-control" name="slug" id="slug" value="{{ $map->slug }}" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="folder">Папка для карт</label>
                                    <input type="text" class="form-control" name="folder" id="folder" value="{{ $map->folder }}" placeholder="">
                                </div>

                            </div>
                            <div class="col-lg-3">

                            </div>

                            <div class="col-lg-3">

                            </div>

                            <div class="col-lg-2">
                                <a class=" modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalForm">Open Form</a>
                            </div>
                        </div>


                        <div class="row justify-content-start">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Сохранить</button>
                                <a href="{{ route('admin.npc') }}" class="btn btn-success">Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="modalForm" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <header class="card-header">
                <h2 class="card-title">Registration Form</h2>
            </header>
            <div class="card-body">
                <form action="">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputEmail4">Email</label>
                            <input type="email" class="form-control" id="inputEmail4" placeholder="Email">
                        </div>
                        <div class="form-group col-md-6 mb-3 mb-lg-0">
                            <label for="inputPassword4">Password</label>
                            <input type="password" class="form-control" id="inputPassword4" placeholder="Password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputAddress">Address</label>
                        <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
                    </div>
                    <div class="form-group">
                        <label for="inputAddress2">Address 2</label>
                        <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputCity">City</label>
                            <input type="text" class="form-control" id="inputCity">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputState">State</label>
                            <select id="inputState" class="form-control">
                                <option selected>Choose...</option>
                                <option>...</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="inputZip">Zip</label>
                            <input type="text" class="form-control" id="inputZip">
                        </div>
                    </div>
                </form>
            </div>
            <footer class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary modal-confirm">Submit</button>
                        <button class="btn btn-default modal-dismiss">Cancel</button>
                    </div>
                </div>
            </footer>
        </section>
    </div>
@endsection


