<div class="table-responsive">
    <table class="table table-hover table-bordered mb-none">
        <thead><tr>
            <th>ID</th><th></th><th>Предмет</th><th>Категория</th>
            <th>Монеты</th><th>Бриллианты</th><th>Репутация от</th><th>Лимит покупки</th><th>Ингредиенты</th><th>Сортировка</th>
        </tr></thead>
        <tbody>
        @forelse($structureShop->shopItems as $shopItem)
            <tr>
                <td>{{ $shopItem->id }}</td>
                <td>@if($shopItem->item?->image)<img src="{{ $shopItem->item->image }}" alt="" width="36" height="36" style="object-fit:contain;">@endif</td>
                <td><a href="{{ route('admin.item.info', $shopItem->share_item_id) }}">{{ $shopItem->item?->name ?? '—' }}</a></td>
                <td>{{ $shopItem->category?->name ?? '—' }}</td>
                <td>{{ format_money($shopItem->price) }}</td>
                <td>{{ format_money($shopItem->diamond) }}</td>
                <td>{{ $shopItem->min_reputation_points }}</td>
                <td>
                    @if($shopItem->purchase_limit_quantity > 0 && $shopItem->purchase_limit_period_seconds > 0)
                        {{ $shopItem->purchase_limit_quantity }} шт. / {{ \Carbon\CarbonInterval::seconds($shopItem->purchase_limit_period_seconds)->cascade()->locale(app()->getLocale())->forHumans() }}
                    @else
                        —
                    @endif
                </td>
                <td>@forelse($shopItem->requirements as $requirement){{ $requirement->item?->name ?? '—' }} ×{{ $requirement->quantity }}@if(!$loop->last)<br>@endif @empty — @endforelse</td>
                <td>{{ $shopItem->sort_order }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center text-muted">Нет предметов</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
