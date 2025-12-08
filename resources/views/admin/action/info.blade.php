@extends('admin.layout.base')

@section('title')
    {{ $action->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.action.info', ['action' => $action->id]) }}" method="post">
                        {{ csrf_field() }}
                        <div class="row form-group pb-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="col-form-label" for="alias">Alias</label>
                                    <input type="text" class="form-control" name="alias" id="alias" value="{{ $action->alias }}" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="name">Название</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $action->name }}" placeholder="">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-none">
                                        <thead>
                                        <tr>
                                            <th width="50">ID</th>
                                            <th>Название</th>
                                            <th>Кол-во шт</th>
                                            <th width="70"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($action->structures as $structure)
                                            <tr style="vertical-align: baseline">
                                                <td>{{ $structure->id }}</td>
                                                <td><a href="{{ route('admin.structure.info', ['item' => $structure->id]) }}">{{ $structure->name }}</a></td>
                                                <td>{{ $needItem->pivot->count }}</td>
                                                <td><a href="{{ route('admin.item.recipe.delete_item', ['recipe' => $item->recipe->id, 'item' => $needItem->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div class="row justify-content-start">
                            <div class="col-sm-12">
                                <button class="btn btn-primary">Сохранить</button>
                                <a href="{{ route('admin.action') }}" class="btn btn-success">Назад</a>
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


