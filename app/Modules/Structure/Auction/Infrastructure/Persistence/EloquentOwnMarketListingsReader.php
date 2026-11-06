<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Infrastructure\Persistence;

use App\Modules\Structure\Auction\Domain\Contracts\OwnMarketListingsReader;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EloquentOwnMarketListingsReader implements OwnMarketListingsReader
{
    public function auction(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return Auction::query()->where('user_id', $userId)->where('count', '>', 0)
            ->with([
                'item' => static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'share_item_id', 'upgrade_lvl']),
                'item.itemInfo' => static fn (BelongsTo $query): BelongsTo => $query->withOnly([])->select(['id', 'name']),
            ])->orderByDesc('id')->paginate(perPage: $perPage, page: $page)
            ->through(static function (Auction $lot): array {
                $level = (int) ($lot->item?->upgrade_lvl ?? 0);

                return [
                    'id' => (int) $lot->id, 'structure_id' => $lot->structure_id,
                    'item_id' => (int) $lot->item_id, 'share_item_id' => $lot->item?->share_item_id,
                    'name' => ($lot->item?->itemInfo?->name ?? '—').($level > 0 ? ' +'.$level : ''),
                    'quantity' => (int) $lot->count, 'total_price' => (int) $lot->price,
                    'unit_price' => round($lot->price / $lot->count, 2),
                    'created_at' => $lot->created_at?->toIso8601String(),
                ];
            });
    }

    public function exchange(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return AuctionOrder::query()->where('user_id', $userId)->where('count', '>', 0)
            ->with('shareItem', static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'name']))
            ->orderByDesc('id')->paginate(perPage: $perPage, page: $page)
            ->through(static fn (AuctionOrder $order): array => [
                'id' => (int) $order->id, 'structure_id' => $order->structure_id,
                'share_item_id' => (int) $order->share_item_id,
                'name' => $order->shareItem?->name ?? '—', 'quantity' => (int) $order->count,
                'unit_price' => (int) $order->price, 'total_price' => (int) $order->price * (int) $order->count,
                'created_at' => $order->created_at?->toIso8601String(),
            ]);
    }
}
