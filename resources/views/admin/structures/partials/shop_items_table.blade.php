<div class="table-responsive">
    <table class="table table-hover table-bordered mb-none">
        <thead>
        <tr>
            <th width="45"></th>
            <th>Название</th>
            <th width="130">Цена (монеты)</th>
            <th width="130">Цена (алмазы)</th>
            <th width="120">Сортировка</th>
            <th width="150"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $shopItem)
            <tr style="vertical-align: middle">
                <td>
                    @if($shopItem->item?->image)
                        <img src="{{ $shopItem->item->image }}" width="36" alt="">
                    @endif
                </td>
                <td>
                    @if($shopItem->item)
                        <a href="{{ route('admin.item.info', $shopItem->item->id) }}" target="_blank">
                            {{ $shopItem->item->name }}
                        </a>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <form id="shop-item-form-{{ $shopItem->id }}"
                          action="{{ route('admin.structure.info.shop_update', [$structure->id, $shopItem->id]) }}"
                          method="post">
                        {{ csrf_field() }}
                    </form>
                    <input type="number"
                           min="0"
                           class="form-control form-control-sm"
                           name="price"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->price }}">
                </td>
                <td>
                    <input type="number"
                           min="0"
                           class="form-control form-control-sm"
                           name="diamond"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->diamond }}">
                </td>
                <td>
                    <input type="number"
                           class="form-control form-control-sm"
                           name="sort_order"
                           form="shop-item-form-{{ $shopItem->id }}"
                           value="{{ $shopItem->sort_order }}">
                </td>
                <td>
                    <button class="btn btn-xs btn-primary"
                            form="shop-item-form-{{ $shopItem->id }}">Сохранить</button>
                    <a href="{{ route('admin.structure.info.shop_delete_item', [$structure->id, $shopItem->share_item_id]) }}"
                       class="btn btn-xs btn-danger"
                       onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Нет предметов</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
