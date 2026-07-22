@extends('admin.layout.base')

@section('title')
    {{ $structure->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-shop" href="#tab-shop" data-bs-toggle="tab">
                                    Магазин <span class="badge badge-primary">{{ $structure->shopItems->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-actions" href="#tab-actions" data-bs-toggle="tab">
                                    Действия <span class="badge badge-primary">{{ $structure->actions->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.structure.info', $structure->id) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row pt-3 pb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $structure->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Тип</label>
                                                <select class="form-control" name="type" data-plugin-selectTwo>
                                                    @foreach(\App\Modules\Structure\Infrastructure\Persistence\Models\Structure::TYPES as $key => $label)
                                                        <option value="{{ $key }}" @selected($structure->type === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-form-label">Локация</label>
                                                <select id="sel-location" name="location_id" class="form-control">
                                                    @if($structure->location)
                                                        <option value="{{ $structure->location->id }}" selected>[{{ $structure->location->id }}] {{ $structure->location->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">НПС</label>
                                                <select id="sel-npc" name="npc_id" class="form-control">
                                                    @if($structure->npc)
                                                        <option value="{{ $structure->npc->id }}" selected>[{{ $structure->npc->id }}] {{ $structure->npc->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.structures') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

	                            {{-- МАГАЗИН --}}
	                            <div id="tab-shop" class="tab-pane">
	                                <div class="pt-3">
	                                    <div class="mb-3">
	                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalShop">Добавить предмет</a>
	                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-success" href="#modalCategory">Добавить категорию</a>
	                                    </div>
	                                    @php
	                                        $attachedCategoryIds = $structure->categories->pluck('id')->all();
	                                        $orphanCategories = $structure->shopItems
	                                            ->filter(fn ($shopItem) => $shopItem->category && ! in_array($shopItem->category->id, $attachedCategoryIds, true))
	                                            ->pluck('category')
	                                            ->unique('id')
	                                            ->sortBy('name')
	                                            ->values();
	                                        $categoryBlocks = $structure->categories->sortBy('name')->values()->concat($orphanCategories);
	                                        $uncategorizedItems = $structure->shopItems->filter(fn ($shopItem) => $shopItem->share_structure_category_id === null);
	                                    @endphp

	                                    @if($structure->shopItems->isEmpty() && $categoryBlocks->isEmpty())
	                                        <div class="alert alert-default text-center text-muted mb-0">Нет предметов</div>
	                                    @else
	                                        @foreach($categoryBlocks as $category)
	                                            @php
	                                                $categoryItems = $structure->shopItems
	                                                    ->filter(fn ($shopItem) => (int) $shopItem->share_structure_category_id === (int) $category->id)
	                                                    ->sortByDesc('sort_order');
	                                            @endphp
	                                            <section class="card mb-3">
	                                                <header class="card-header py-2">
	                                                    <h2 class="card-title" style="font-size: 14px;">
	                                                        {{ $category->name }}
	                                                        <span class="badge badge-primary">{{ $categoryItems->count() }}</span>
	                                                    </h2>
	                                                </header>
	                                                <div class="card-body p-0">
	                                                    @include('admin.structures.partials.shop_items_table', ['items' => $categoryItems, 'structure' => $structure])
	                                                </div>
	                                            </section>
	                                        @endforeach

	                                        @if($uncategorizedItems->isNotEmpty())
	                                            <section class="card mb-3">
	                                                <header class="card-header py-2">
	                                                    <h2 class="card-title" style="font-size: 14px;">
	                                                        Без категории
	                                                        <span class="badge badge-primary">{{ $uncategorizedItems->count() }}</span>
	                                                    </h2>
	                                                </header>
	                                                <div class="card-body p-0">
	                                                    @include('admin.structures.partials.shop_items_table', ['items' => $uncategorizedItems->sortByDesc('sort_order'), 'structure' => $structure])
	                                                </div>
	                                            </section>
	                                        @endif
	                                    @endif
	                                </div>
	                            </div>

                            {{-- ДЕЙСТВИЯ --}}
                            <div id="tab-actions" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAction">Добавить действие</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="160">Alias</th>
                                                <th>Название</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($structure->actions as $action)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $action->id }}</td>
                                                    <td><code>{{ $action->alias }}</code></td>
                                                    <td>{{ $action->name }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.structure.info.action_delete', [$structure->id, $action->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">Нет действий</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
	    </div>

	    {{-- Модалка: добавить категорию магазина --}}
	    <div id="modalCategory" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
	        <section class="card">
	            <form action="{{ route('admin.structure.info.category', $structure->id) }}" method="post">
	                <header class="card-header"><h2 class="card-title">Добавить категорию магазина</h2></header>
	                <div class="card-body">
	                    {{ csrf_field() }}
	                    <div class="form-group mb-2">
	                        <label>Категория</label>
	                        <select name="share_structure_category_id" class="form-control" data-plugin-selectTwo
	                                data-plugin-options='{ "placeholder": "Выберите категорию", "allowClear": true }'>
	                            <option value=""></option>
	                            @foreach($allCategories as $category)
	                                <option value="{{ $category->id }}">{{ $category->name }}</option>
	                            @endforeach
	                        </select>
	                    </div>
	                </div>
	                <footer class="card-footer">
	                    <div class="col-md-12 text-end">
	                        <button class="btn btn-primary">Добавить</button>
	                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
	                    </div>
	                </footer>
	            </form>
	        </section>
	    </div>

	    {{-- Модалка: добавить предмет в магазин --}}
	    <div id="modalShop" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.structure.info.shop', $structure->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в магазин</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="shop-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Категория</label>
                        <select name="share_structure_category_id" class="form-control" data-plugin-selectTwo
                                data-plugin-options='{ "placeholder": "Без категории", "allowClear": true }'>
                            <option value=""></option>
                            @foreach($allCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Цена (монеты)</label>
                                <input type="number" class="form-control" name="price" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Цена (алмазы)</label>
                                <input type="number" class="form-control" name="diamond" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Сортировка</label>
                                <input type="number" class="form-control" name="sort_order" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    {{-- Модалка: добавить действие --}}
    <div id="modalAction" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.structure.info.action', $structure->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить действие</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Действие</label>
                        <select name="share_action_id" class="form-control" data-plugin-selectTwo
                                data-plugin-options='{ "placeholder": "Выберите действие", "allowClear": true }'>
                            <option value=""></option>
                            @foreach($allActions as $action)
                                <option value="{{ $action->id }}">[{{ $action->alias }}] {{ $action->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    function formatItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#sel-location').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите локацию',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.locations') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $('#sel-npc').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите НПС',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.npcs') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $('#shop-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalShop'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
        ajax: {
            url: '{{ route('admin.api.items') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>
@endpush

@endsection
