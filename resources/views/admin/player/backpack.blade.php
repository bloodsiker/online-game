@extends('admin.layout.base')

@section('title')
    Рюкзак игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'backpack'])

    <section class="card">
        <header class="card-header">
            <h2 class="card-title">Рюкзак ({{ $backpack->count() }})</h2>
        </header>
        <div class="card-body">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalBackpackAdd">Добавить предмет</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Название</th>
                                                <th>Тип</th>
                                                <th width="70">Кол-во</th>
                                                <th width="80">Надето</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($backpack as $slot)
                                                @php
                                                    $shareItem = $slot->item?->itemInfo;
                                                    $itemUrl = $shareItem ? route('admin.item.info', ['item' => $shareItem->id]) : null;
                                                @endphp
                                                <tr style="vertical-align: middle" @if($slot->equipped) class="table-success" @endif>
                                                    <td>{{ $slot->id }}</td>
                                                    <td>
                                                        @if($shareItem?->image)
                                                            <a href="{{ $itemUrl }}" target="_blank" rel="noopener" aria-label="Открыть «{{ $shareItem->name }}» в новой вкладке">
                                                                <img src="{{ $shareItem->image }}" style="width:36px" alt="">
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($shareItem)
                                                            <a href="{{ $itemUrl }}" target="_blank" rel="noopener">{{ $shareItem->name }}</a>
                                                        @else
                                                            —
                                                        @endif
                                                        @if($shareItem?->type === \App\Modules\Share\Domain\Enums\ShareItemType::TREASURE_MAP)
                                                            @if($slot->item->treasureHunt?->target_location_id)
                                                                <span class="text-muted">[локация №{{ $slot->item->treasureHunt->target_location_id }}]</span>
                                                            @else
                                                                <span class="text-warning">[место клада не назначено]</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $shareItem?->type?->label() ?? '—' }}</td>
                                                    <td>{{ $slot->count }}</td>
                                                    <td>
                                                        @if($slot->equipped)
                                                            <span class="badge badge-success">Да</span>
                                                        @else
                                                            <span class="badge badge-default">Нет</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.player.backpack.delete', [$player->id, $slot->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Рюкзак пуст</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
        </div>
    </section>

    {{-- Модалка: добавить предмет в рюкзак --}}
    <div id="modalBackpackAdd" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.player.backpack.add', $player->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в рюкзак</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="backpack-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>Количество</label>
                        <input type="number" class="form-control" name="count" value="1" min="1">
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
    function formatBackpackItem(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '<span style="display:inline-block;width:24px;height:24px;margin-right:6px;"></span>';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#backpack-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalBackpackAdd'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatBackpackItem,
        templateSelection: formatBackpackItem,
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
@endpush

@endsection
