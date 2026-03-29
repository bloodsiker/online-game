@extends('admin.layout.base')

@section('title')
    {{ $reputation->name }}
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
                                <a class="nav-link" data-bs-target="#tab-tiers" href="#tab-tiers" data-bs-toggle="tab">
                                    Уровни <span class="badge badge-primary">{{ $reputation->tiers->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-shop" href="#tab-shop" data-bs-toggle="tab">
                                    Магазин <span class="badge badge-primary">{{ $reputation->shopItems->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.reputation.info', $reputation->id) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row pt-3 pb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $reputation->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Описание</label>
                                                <textarea class="form-control" name="description" rows="4">{{ $reputation->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">NPC</label>
                                                <select id="sel-npc" name="npc_id" class="form-control">
                                                    @if($reputation->npc)
                                                        <option value="{{ $reputation->npc->id }}" selected>[{{ $reputation->npc->id }}] {{ $reputation->npc->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Иконка (URL)</label>
                                                <input type="text" class="form-control" name="icon" value="{{ $reputation->icon }}">
                                                @if($reputation->icon)
                                                    <div class="mt-1">
                                                        <img src="{{ $reputation->icon }}" style="width:48px;height:48px;object-fit:contain;border:1px solid #ddd;border-radius:4px;" alt="">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.reputations') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- УРОВНИ --}}
                            <div id="tab-tiers" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalTier">Добавить уровень</a>
                                    </div>

                                    @forelse($reputation->tiers as $tier)
                                        <div class="card mb-3">
                                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                                <div>
                                                    @if($tier->medal_icon)
                                                        <img src="{{ $tier->medal_icon }}" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;" alt="">
                                                    @endif
                                                    <strong>{{ $tier->medal_name ?: 'Без названия' }}</strong>
                                                    <span class="text-muted ms-2">{{ number_format($tier->min_points) }} – {{ number_format($tier->max_points) }} очков</span>
                                                </div>
                                                <a href="{{ route('admin.reputation.tier.delete', [$reputation->id, $tier->id]) }}"
                                                   class="btn btn-xs btn-danger"
                                                   onclick="return confirm('Удалить уровень со всеми квестами?')">Удалить</a>
                                            </div>
                                            <div class="card-body py-2">
                                                <p class="mb-2 small text-muted">Квесты этого уровня:</p>
                                                <table class="table table-sm table-bordered mb-2">
                                                    <thead>
                                                    <tr>
                                                        <th width="50">ID</th>
                                                        <th>Квест</th>
                                                        <th width="70"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($tier->quests as $tierQuest)
                                                        <tr>
                                                            <td>{{ $tierQuest->quest?->id }}</td>
                                                            <td>{{ $tierQuest->quest?->title ?? '—' }}</td>
                                                            <td>
                                                                <a href="{{ route('admin.reputation.tier.quest.delete', [$reputation->id, $tier->id, $tierQuest->id]) }}"
                                                                   class="btn btn-xs btn-danger"
                                                                   onclick="return confirm('Удалить?')">Удалить</a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3" class="text-muted text-center">Нет квестов</td></tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                                <form action="{{ route('admin.reputation.tier.quest.add', [$reputation->id, $tier->id]) }}" method="post" class="d-flex gap-2">
                                                    {{ csrf_field() }}
                                                    <select class="form-control tier-quest-select" name="quest_id" style="max-width:400px;"></select>
                                                    <button class="btn btn-sm btn-outline-primary">Добавить квест</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">Уровни не добавлены.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- МАГАЗИН --}}
                            <div id="tab-shop" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalShop">Добавить предмет</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Предмет</th>
                                                <th width="100">Цена (монет)</th>
                                                <th width="100">Цена (алмазы)</th>
                                                <th width="110">Мин. очков</th>
                                                <th width="80">Сортировка</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($reputation->shopItems as $shopItem)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $shopItem->id }}</td>
                                                    <td>
                                                        @if($shopItem->item?->image)
                                                            <img src="{{ $shopItem->item->image }}" width="36" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $shopItem->item?->name ?? '—' }}</td>
                                                    <td>{{ number_format($shopItem->price, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($shopItem->diamond, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($shopItem->min_points, 0, '', ' ') }}</td>
                                                    <td>{{ $shopItem->sort_order }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.reputation.shop.delete', [$reputation->id, $shopItem->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Нет предметов</td></tr>
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

    {{-- Модалка: добавить уровень --}}
    <div id="modalTier" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.reputation.tier.add', $reputation->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить уровень</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Мин. очков</label>
                                <input type="number" class="form-control" name="min_points" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Макс. очков</label>
                                <input type="number" class="form-control" name="max_points" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Название медали</label>
                        <input type="text" class="form-control" name="medal_name">
                    </div>
                    <div class="form-group mb-2">
                        <label>Иконка медали (URL)</label>
                        <input type="text" class="form-control" name="medal_icon">
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
            <form action="{{ route('admin.reputation.shop.add', $reputation->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в магазин</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="shop-item-select" name="share_item_id" class="form-control"></select>
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
                                <label>Мин. очков репутации</label>
                                <input type="number" class="form-control" name="min_points" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Сортировка</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
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

    // NPC select
    $('#sel-npc').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите NPC',
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

    // Shop item select
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

    // Quest selects per tier
    $('.tier-quest-select').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            placeholder: 'Выберите квест',
            allowClear: true,
            ajax: {
                url: '{{ route('admin.api.quests') }}',
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
    });
</script>
@endpush

@endsection