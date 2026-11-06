<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderShopItems
{
    /** @param Collection<int, int> $ids */
    public function execute(Structure $structure, ?int $categoryId, Collection $ids): void
    {
        DB::transaction(function () use ($structure, $categoryId, $ids): void {
            Structure::whereKey($structure->id)->lockForUpdate()->firstOrFail();
            $items = ShopItem::where('structure_id', $structure->id)->where('share_structure_category_id', $categoryId)
                ->orderBy('id')->lockForUpdate()->get();
            if ($ids->sort()->values()->all() !== $items->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all()) {
                throw ValidationException::withMessages(['item_ids' => 'Состав категории изменился. Обновите страницу и повторите сортировку.']);
            }
            // The game displays higher sort_order values first.
            foreach ($ids->values() as $index => $id) {
                ShopItem::where('structure_id', $structure->id)->whereKey($id)->update(['sort_order' => $ids->count() - $index]);
            }
        });
    }
}
