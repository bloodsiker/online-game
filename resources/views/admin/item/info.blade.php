@extends('admin.layout.base')

@section('title')
    {{ $item->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <a href="{{ route('admin.items') }}" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Основная информация</a>
                    <a href="" class="mb-1 mt-1 me-1 btn btn-primary btn-sm btn-block">Магазин</a>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#popular" href="#popular" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#skill" href="#skill" data-bs-toggle="tab">Навык</a>
                            </li>
                            @if($item->type === \App\Models\ShareItem::TYPE_RECIPE)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-target="#recent" href="#recent" data-bs-toggle="tab">Рецепт</a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content">
                            <div id="popular" class="tab-pane active">
                                <form action="{{ route('admin.item.info', ['item' => $item->id]) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row form-group pb-3">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="col-form-label" for="name">Название</label>
                                                <input type="text" class="form-control" name="name" id="name" value="{{ $item->name }}" placeholder="">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="type">Тип</label>
                                                <select class="form-control" name="type" id="type">
                                                    @foreach(App\Models\ShareItem::TYPES as $key => $type)
                                                        <option value="{{ $key }}" @if($item->type === $key) selected @endif>{{ $type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="slot">Слот</label>
                                                <select class="form-control" name="slot" id="slot">
                                                    <option value=""></option>
                                                    @foreach(App\Models\ShareItem::TYPES as $key => $type)
                                                        <option value="{{ $key }}" @if($item->type === $key) selected @endif>{{ $type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="is_two_hand">Одна/Две руки</label>
                                                <select class="form-control" name="is_two_hand" id="is_two_hand">
                                                    <option value="1" @if($item->is_two_hand === 1) selected @endif>Две руки</option>
                                                    <option value="0" @if($item->is_two_hand === 0) selected @endif>Одна рука</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="description">Описание</label>
                                                <textarea class="form-control" name="description" rows="7"  id="description">{{ $item->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label" for="price">Цена</label>
                                                <input type="text" class="form-control" id="price" name="price" value="{{ $item->price }}" placeholder="">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="break_crystal">Количество кристалов</label>
                                                <input type="text" class="form-control" id="break_crystal" name="break_crystal" value="{{ $item->break_crystal }}" placeholder="">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="count_use">Количество использований</label>
                                                <input type="text" class="form-control" id="count_use" name="count_use" value="{{ $item->count_use }}" placeholder="">
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label" for="min_attack">Мин атака</label>
                                                <input type="text" class="form-control" id="min_attack" name="min_attack" value="{{ $item->min_attack }}" placeholder="">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="max_attack">Макс атака</label>
                                                <input type="text" class="form-control" id="max_attack" name="max_attack" value="{{ $item->max_attack }}" placeholder="">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="armor">Защита</label>
                                                <input type="text" class="form-control" id="armor" name="armor" value="{{ $item->armor }}" placeholder="">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_active">Активний</label>
                                                <select class="form-control" name="is_active" id="is_active">
                                                    <option value="1" @if($item->is_active === 1) selected @endif>Да</option>
                                                    <option value="0" @if($item->is_active === 0) selected @endif>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_heal">Востанавливающее</label>
                                                <select class="form-control" name="is_heal" id="is_heal">
                                                    <option value="1" @if($item->is_heal === 1) selected @endif>Да</option>
                                                    <option value="0" @if($item->is_heal === 0) selected @endif>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_sell">Можно продать</label>
                                                <select class="form-control" name="is_sell" id="is_sell">
                                                    <option value="1" @if($item->is_sell === 1) selected @endif>Да</option>
                                                    <option value="0" @if($item->is_sell === 0) selected @endif>Нет</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row justify-content-start">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div id="skill" class="tab-pane">
                                <div class="row pb-3">
                                    <div class="col-lg-4">
                                        <form action="" method="post">
                                            {{ csrf_field() }}
                                            <div class="row pb-3">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label class="control-label text-lg-end pt-2" for="skill_id">Навык</label>
                                                        <select id="skill_id" name="skill_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите рецепт", "allowClear": true }'>
                                                            @foreach($skills as $skill)
                                                                <option value="{{ $skill->id }}" @if($item->skill_id === $skill->id)selected @endif>{{ $skill->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-form-label" for="skill_lvl">Необходим навык</label>
                                                        <input type="text" class="form-control" id="skill_lvl" name="skill_lvl" value="{{ $item->skill_lvl }}" placeholder="">
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-form-label" for="skill_exp">Опыта навыка за удар</label>
                                                        <input type="text" class="form-control" id="skill_exp" name="skill_exp" value="{{ $item->skill_exp }}" placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row justify-content-start">
                                                <div class="col-sm-12">
                                                    <button class="btn btn-primary">Сохранить</button>
                                                    <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if($item->type === \App\Models\ShareItem::TYPE_RECIPE)
                                <div id="recent" class="tab-pane">
                                    <div class="row pb-3">
                                        <div class="col-lg-4">
                                            <form action="" method="post">
                                                {{ csrf_field() }}
                                                <div class="row pb-3">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label class="control-label text-lg-end pt-2">Крафт предмета</label>
                                                            <select data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите рецепт", "allowClear": true }'>
                                                                @foreach($allItems as $itemKraft)
                                                                    <option value="{{ $itemKraft->id }}" @if($item->recipe->kraft_item_id === $itemKraft->id)selected @endif>{{ $itemKraft->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="col-form-label" for="percent">Процент успеха</label>
                                                            <input type="text" class="form-control" id="percent" name="percent" value="{{ $item->recipe->percent }}" placeholder="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row justify-content-start">
                                                    <div class="col-sm-12">
                                                        <button class="btn btn-primary">Сохранить</button>
                                                        <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="right mb-3">
                                                <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalForm">Добавить предмет</a>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered mb-none">
                                                    <thead>
                                                    <tr>
                                                        <th width="50">ID</th>
                                                        <th width="40">Картинка</th>
                                                        <th>Название</th>
                                                        <th>Кол-во шт</th>
                                                        <th width="70"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($item->recipe->items as $needItem)
                                                        <tr style="vertical-align: baseline">
                                                            <td>{{ $needItem->id }}</td>
                                                            <td><img src="{{ $needItem->image }}" width="40" alt=""></td>
                                                            <td><a href="{{ route('admin.item.info', ['item' => $needItem->id]) }}">{{ $needItem->name }}</a></td>
                                                            <td>{{ $needItem->pivot->count }}</td>
                                                            <td><a href="{{ route('admin.item.recipe.delete_item', ['recipe' => $item->recipe->id, 'item' => $needItem->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @if($item->type === \App\Models\ShareItem::TYPE_RECIPE)
        <div id="modalForm" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
            <section class="card">
                <form action="{{ route('admin.item.recipe.add_item', ['recipe' => $item->recipe->id]) }}" method="post">
                    <header class="card-header">
                        <h2 class="card-title">Добавить ресурс</h2>
                    </header>
                    <div class="card-body">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-group">
                                    <label class="control-label text-lg-end" for="resourceItem">Ресурс</label>
                                    <select id="resourceItem" name="share_item_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите рецепт", "allowClear": true }'>
                                        @foreach($allItems as $itemKraft)
                                            <option value="{{ $itemKraft->id }}">{{ $itemKraft->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="form-group ">
                                    <label for="count">Кол-во</label>
                                    <input type="text" class="form-control" id="count" name="count">
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
    @endif
@endsection


