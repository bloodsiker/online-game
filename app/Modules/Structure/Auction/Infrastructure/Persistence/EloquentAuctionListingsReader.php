<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Infrastructure\Persistence;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Auction\Application\Services\AuctionItemSelectionFilter;
use App\Modules\Structure\Auction\Domain\Contracts\AuctionListingsReader;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EloquentAuctionListingsReader implements AuctionListingsReader
{
    public function __construct(private readonly AuctionItemSelectionFilter $filter) {}

    public function page(int $page, int $perPage, ?int $ownerId = null, string $name = '', ?ShareItemType $type = null, ?ItemRarity $rarity = null): LengthAwarePaginator
    {
        return Auction::query()->where('count', '>', 0)
            ->when($ownerId !== null, static fn (Builder $query): Builder => $query->where('user_id', $ownerId))
            ->whereHas('item.itemInfo', function (Builder $query) use ($name, $type, $rarity): void {
                $this->filter->apply(query: $query, filter: ['name' => $name, 'type' => $type?->value ?? '']);
                if ($rarity !== null) {
                    $query->where('rarity', $rarity->value);
                }
            })
            ->with([
                'item' => static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'share_item_id', 'upgrade_lvl']),
                'item.itemInfo' => static fn (BelongsTo $query): BelongsTo => $query->withOnly([])->select(['id', 'name', 'description', 'image', 'rarity', 'type']),
            ])->orderByDesc('id')->paginate(perPage: $perPage, page: $page)
            ->through(static function (Auction $lot): array {
                $level = (int) ($lot->item?->upgrade_lvl ?? 0);
                $info = $lot->item?->itemInfo;

                return [
                    'id' => (int) $lot->id, 'structure_id' => $lot->structure_id,
                    'item_id' => (int) $lot->item_id, 'share_item_id' => $lot->item?->share_item_id,
                    'name' => ($info?->name ?? '—').($level > 0 ? ' +'.$level : ''),
                    'description' => $info?->description ?? '',
                    'image_url' => asset($info?->image ?: 'img/bg/empty_slot.gif'),
                    'rarity' => $info?->rarity?->value, 'type' => $info?->type?->value, 'type_label' => $info?->type?->label(),
                    'upgrade_level' => $level, 'quantity' => (int) $lot->count, 'total_price' => (int) $lot->price,
                    'unit_price' => round($lot->price / $lot->count, 2), 'created_at' => $lot->created_at?->toIso8601String(),
                ];
            });
    }
}
