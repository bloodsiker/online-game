@extends('admin.layout.base')

@section('title')
    {{ $stock ? 'Акция: '.$stock->name : 'Новая акция' }}
@endsection

@section('body')
<div class="row">
    <div class="col-md-12">
        <section class="card">
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($stock)
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-tiers" href="#tab-tiers" data-bs-toggle="tab">
                                    Уровни <span class="badge badge-primary">{{ $stock->tiers->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.bank.stock.info', $stock->id) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="_action" value="update">
                                    <div class="row pb-3 pt-3">
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 control-label text-right pt-2">Название</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="name" class="form-control" value="{{ $stock->name }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 control-label text-right pt-2">Начало</label>
                                                <div class="col-lg-9">
                                                    <input type="datetime-local" name="starts_at" class="form-control"
                                                           value="{{ $stock->starts_at->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 control-label text-right pt-2">Конец</label>
                                                <div class="col-lg-9">
                                                    <input type="datetime-local" name="ends_at" class="form-control"
                                                           value="{{ $stock->ends_at->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 control-label text-right pt-2">Активна</label>
                                                <div class="col-lg-9 pt-2">
                                                    <input type="checkbox" name="is_active" value="1" {{ $stock->is_active ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                                    <a href="{{ route('admin.bank.stock.duplicate', $stock->id) }}" class="btn btn-sm btn-default ml-1">Копировать</a>
                                    <a href="{{ route('admin.bank.stock.delete', $stock->id) }}"
                                       onclick="return confirm('Удалить акцию «{{ $stock->name }}» и все её уровни?')"
                                       class="btn btn-sm btn-danger ml-1">Удалить</a>
                                    <a href="{{ route('admin.bank.stocks') }}" class="btn btn-sm btn-default ml-1">Назад</a>
                                </form>
                            </div>

                            {{-- УРОВНИ --}}
                            <div id="tab-tiers" class="tab-pane">
                                <style>
                                    .tier-card { border-radius: 8px; overflow: hidden; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
                                    .tier-header { background: linear-gradient(135deg, #2c3e50 0%, #3d5a80 100%); color: #fff; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; }
                                    .tier-header .tier-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.2); font-weight: 700; font-size: 13px; margin-right: 10px; }
                                    .tier-header .tier-title { font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
                                    .tier-header .tier-diamond { background: rgba(255,255,255,0.15); border-radius: 12px; padding: 2px 10px; font-size: 13px; font-weight: 700; color: #a8d8f0; }
                                    .tier-body { background: #fafafa; padding: 14px 16px; min-height: 60px; }
                                    .tier-items { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; }
                                    .tier-item { position: relative; width: 64px; }
                                    .tier-item-img { width: 59px; height: 59px; border-radius: 4px; overflow: hidden; border: 2px solid #d0d0d0; background: #e8e8e8 center / cover no-repeat; position: relative; transition: border-color .15s; }
                                    .tier-item-img:hover { border-color: #e74c3c; }
                                    .tier-item-count { position: absolute; bottom: 2px; right: 2px; background: rgba(80,40,10,0.85); color: #f6d9a6; font-size: 10px; font-weight: 700; padding: 1px 4px; border-radius: 2px; line-height: 14px; }
                                    .tier-item-del { position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #e74c3c; color: #fff; border-radius: 50%; font-size: 11px; line-height: 18px; text-align: center; text-decoration: none; display: none; font-weight: 700; z-index: 2; }
                                    .tier-item:hover .tier-item-del { display: block; }
                                    .tier-item-name { font-size: 10px; color: #555; text-align: center; margin-top: 4px; line-height: 1.2; word-break: break-word; }
                                    .tier-empty { color: #aaa; font-size: 12px; font-style: italic; padding: 4px 0; }
                                    .tier-add-btn { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; font-size: 12px; border-radius: 4px; }
                                    .tier-del-btn { opacity: 0.7; font-size: 11px; padding: 2px 8px; }
                                    .tier-del-btn:hover { opacity: 1; }
                                </style>

                                <div class="pt-3">
                                    <a class="modal-with-zoom-anim btn btn-sm btn-success mb-3" href="#modalAddTier">
                                        <i class="bx bx-plus"></i> Добавить порог
                                    </a>

                                    @forelse($stock->tiers as $tier)
                                        <div class="tier-card">
                                            <div class="tier-header">
                                                <div class="tier-title">
                                                    <span class="tier-badge">{{ $loop->iteration }}</span>
                                                    <span>Уровень {{ $loop->iteration }}</span>
                                                    <span class="tier-diamond">
                                                        <img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" style="vertical-align:middle;margin-right:3px;">{{ number_format($tier->diamond_threshold, 2) }}
                                                    </span>
                                                    <small style="opacity:.6;font-weight:400;">— {{ $tier->items->count() }} {{ trans_choice('предмет|предмета|предметов', $tier->items->count()) }}</small>
                                                </div>
                                                <div style="display:flex;gap:6px;">
                                                    <a class="modal-with-zoom-anim tier-add-btn btn btn-xs btn-primary"
                                                       href="#modalAddItem"
                                                       data-tier-id="{{ $tier->id }}"
                                                       data-action="{{ route('admin.bank.stock.tier.item.add', [$stock->id, $tier->id]) }}">
                                                        <i class="bx bx-gift"></i> Награда
                                                    </a>
                                                    <a href="{{ route('admin.bank.stock.tier.delete', [$stock->id, $tier->id]) }}"
                                                       onclick="return confirm('Удалить уровень и все его награды?')"
                                                       class="tier-del-btn btn btn-xs btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="tier-body">
                                                @if($tier->items->isNotEmpty())
                                                    <div class="tier-items">
                                                        @foreach($tier->items as $item)
                                                            <div class="tier-item">
                                                                <div class="tier-item-img"
                                                                     @if($item->shareItem?->image) style="background-image:url('{{ $item->shareItem->image }}');" @endif>
                                                                    @if($item->count > 0)
                                                                        <span class="tier-item-count">×{{ $item->count }}</span>
                                                                    @endif
                                                                </div>
                                                                <a href="{{ route('admin.bank.stock.tier.item.delete', [$stock->id, $tier->id, $item->id]) }}"
                                                                   onclick="return confirm('Удалить «{{ addslashes($item->shareItem?->name) }}»?')"
                                                                   class="tier-item-del" title="Удалить">×</a>
                                                                <div class="tier-item-name">{{ Str::limit($item->shareItem?->name, 20) }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="tier-empty">Нет наград — нажмите «<i class="bx bx-gift"></i> Награда»</div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div style="padding:30px;text-align:center;color:#aaa;border:2px dashed #ddd;border-radius:8px;">
                                            <i class="bx bx-layer" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                            Нет уровней — нажмите «+ Добавить порог»
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>{{-- /tabs --}}

                @else
                    {{-- Форма создания --}}
                    <form action="{{ route('admin.bank.stock.store') }}" method="post">
                        @csrf
                        <div class="row pb-3 pt-3">
                            <div class="col-lg-6">
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label text-right pt-2">Название</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="name" class="form-control" placeholder="Акция июля" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label text-right pt-2">Начало</label>
                                    <div class="col-lg-9">
                                        <input type="datetime-local" name="starts_at" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label text-right pt-2">Конец</label>
                                    <div class="col-lg-9">
                                        <input type="datetime-local" name="ends_at" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label text-right pt-2">Активна</label>
                                    <div class="col-lg-9 pt-2">
                                        <input type="checkbox" name="is_active" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Создать</button>
                        <a href="{{ route('admin.bank.stocks') }}" class="btn btn-sm btn-default ml-1">Назад</a>
                    </form>
                @endif

            </div>
        </section>
    </div>
</div>

@if($stock)
{{-- Модалка: добавить порог --}}
<div id="modalAddTier" class="modal-block zoom-anim-dialog modal-block-success mfp-hide">
    <section class="card">
        <form action="{{ route('admin.bank.stock.tier.add', $stock->id) }}" method="post">
            @csrf
            <header class="card-header"><h2 class="card-title">Добавить порог</h2></header>
            <div class="card-body">
                <div class="form-group">
                    <label>Сумма пополнения (бриллианты)</label>
                    <input type="number" step="0.01" min="0.01" name="diamond_threshold"
                           class="form-control" placeholder="100.00" required autofocus>
                </div>
            </div>
            <footer class="card-footer">
                <div class="col-md-12 text-end">
                    <button class="btn btn-success">Добавить</button>
                    <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                </div>
            </footer>
        </form>
    </section>
</div>

{{-- Модалка: добавить награду (общая, action меняется через JS) --}}
<div id="modalAddItem" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
    <section class="card">
        <form id="formAddItem" action="" method="post">
            @csrf
            <header class="card-header"><h2 class="card-title">Добавить награду</h2></header>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label>Предмет</label>
                    <select id="item-stock-select" name="share_item_id" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Количество <span class="text-muted">(0 — не показывать)</span></label>
                    <input type="number" name="count" class="form-control" value="0" min="0">
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
@endif

@endsection

@push('footer_scripts')
@if($stock)
<script>
    // При клике на «+ Добавить награду» — прокидываем action в форму модалки
    $(document).on('click', '[data-action]', function () {
        $('#formAddItem').attr('action', $(this).data('action'));
        $('#item-stock-select').val(null).trigger('change');
    });

    function formatStockItem(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '<span style="display:inline-block;width:24px;height:24px;margin-right:6px;"></span>';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#item-stock-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalAddItem'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatStockItem,
        templateSelection: formatStockItem,
        ajax: {
            url: '{{ route('admin.api.items') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: { more: data.pagination.more }
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>
@endif
@endpush
