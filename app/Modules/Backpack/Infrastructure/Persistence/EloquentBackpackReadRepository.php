<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Repositories\BackpackReadRepository;
use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

final class EloquentBackpackReadRepository implements BackpackReadRepository
{
    public function page(int $userId, BackpackCategory $category, int $page, int $perPage): array
    {
        $base = $this->baseQuery(userId: $userId);
        $totals = (clone $base)->selectRaw('share_items.type, COUNT(*) AS positions, SUM(backpacks.count) AS quantity')
            ->groupBy('share_items.type')->get()->keyBy('type')
            ->map(static fn (Backpack $row): array => ['positions' => (int) $row->positions, 'quantity' => (int) $row->quantity]);
        $categories = $this->categories(totals: $totals);
        $total = collect($categories)->firstWhere('key', $category->value)['positions'];
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);
        $query = $base->whereIn('share_items.type', ShareItemType::values(ShareItemType::group($category->value)));
        $items = $this->itemsQuery(query: $query)->forPage($page, $perPage)->get();

        return ['category' => $category->value, 'categories' => $categories, 'data' => $this->mapItems(items: $items),
            'meta' => ['current_page' => $page, 'last_page' => $lastPage, 'per_page' => $perPage, 'total' => $total]];
    }

    public function all(int $userId): array
    {
        $query = $this->baseQuery(userId: $userId)->whereIn('share_items.type', array_keys($this->categoryByType()));
        $items = $this->mapItems(items: $this->itemsQuery(query: $query)->get());
        $totals = collect($items)->groupBy('type')->map(static fn (Collection $group): array => [
            'positions' => $group->count(), 'quantity' => (int) $group->sum('quantity'),
        ]);

        return ['categories' => $this->categories(totals: $totals), 'data' => $items, 'meta' => ['total' => count($items)]];
    }

    private function baseQuery(int $userId): Builder
    {
        return Backpack::query()->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)->where('backpacks.equipped', 0)->where('backpacks.count', '>', 0)
            ->where(static fn (Builder $query): Builder => $query->whereNull('items.expires_at')->orWhere('items.expires_at', '>', now()));
    }

    private function itemsQuery(Builder $query): Builder
    {
        return $query->select('backpacks.*')->with([
            'item' => static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'share_item_id', 'upgrade_lvl', 'count_use']),
            'item.itemInfo' => static fn (BelongsTo $query): BelongsTo => $query->withOnly([])->select(['id', 'name', 'type', 'image', 'rarity', 'count_use']),
        ])->orderBy('backpacks.sort_order')->orderBy('backpacks.id');
    }

    /** @param Collection<string, array{positions: int, quantity: int}> $totals
     * @return list<array<string, mixed>>
     */
    private function categories(Collection $totals): array
    {
        $categories = [];
        foreach (BackpackCategory::cases() as $group) {
            $positions = $quantity = 0;
            foreach (ShareItemType::group($group->value) as $type) {
                $positions += $totals->get($type->value)['positions'] ?? 0;
                $quantity += $totals->get($type->value)['quantity'] ?? 0;
            }
            $categories[] = ['key' => $group->value, 'label' => $group->label(), 'positions' => $positions, 'quantity' => $quantity];
        }

        return $categories;
    }

    /** @return array<string, string> */
    private function categoryByType(): array
    {
        $mapping = [];
        foreach (BackpackCategory::cases() as $group) {
            foreach (ShareItemType::group($group->value) as $type) {
                $mapping[$type->value] = $group->value;
            }
        }

        return $mapping;
    }

    /** @param Collection<int, Backpack> $items
     * @return list<array<string, mixed>>
     */
    private function mapItems(Collection $items): array
    {
        $categories = $this->categoryByType();

        // An item may be removed between the inventory query and eager loading during active play.
        return $items->filter(static fn (Backpack $entry): bool => $entry->item?->itemInfo !== null)->values()
            ->map(static function (Backpack $entry) use ($categories): array {
                $item = $entry->item;
                $info = $item->itemInfo;
                $level = (int) $item->upgrade_lvl;

                return ['backpack_id' => (int) $entry->id, 'item_id' => (int) $entry->item_id,
                    'share_item_id' => (int) $item->share_item_id, 'name' => $item->getName().($level > 0 ? ' +'.$level : ''),
                    'quantity' => (int) $entry->count, 'image_url' => asset($info->image),
                    'category' => $categories[$info->type->value],
                    'type' => $info->type->value, 'type_label' => $info->type->isGatheringResource() ? ShareItemType::RESOURCE->label() : $info->type->label(),
                    'rarity' => $info->rarity?->value, 'rarity_color' => $info->rarity?->color() ?? '#666666', 'upgrade_level' => $level];
            })->all();
    }
}
