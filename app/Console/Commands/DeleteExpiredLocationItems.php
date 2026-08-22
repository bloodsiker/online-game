<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeleteExpiredLocationItems extends Command
{
    protected $signature = 'items:delete-expired-location';

    protected $description = 'Удалить просроченные размещения предметов на игровых локациях';

    public function handle(): int
    {
        $deletedPlacements = 0;
        $deletedItems = 0;

        ItemOnLocation::expired()
            ->select(['id', 'item_id'])
            ->chunkById(500, function (Collection $placements) use (&$deletedPlacements, &$deletedItems): void {
                DB::transaction(function () use ($placements, &$deletedPlacements, &$deletedItems): void {
                    $placementIds = $placements->pluck('id');
                    $itemIds = $placements->pluck('item_id')->unique()->values();

                    $deletedPlacements += ItemOnLocation::query()
                        ->whereKey($placementIds)
                        ->delete();

                    $deletedItems += $this->deleteItemsWithoutStorage($itemIds);
                });
            });

        $this->info(sprintf(
            'Удалено размещений на локациях: %d; экземпляров предметов: %d.',
            $deletedPlacements,
            $deletedItems,
        ));

        return self::SUCCESS;
    }

    /**
     * Удаляем только экземпляры, пришедшие из просроченных item_on_locations.
     * Если тот же item_id ещё где-либо хранится, экземпляр остаётся нетронутым.
     *
     * @param  Collection<int, int>  $itemIds
     */
    private function deleteItemsWithoutStorage(Collection $itemIds): int
    {
        if ($itemIds->isEmpty()) {
            return 0;
        }

        return Item::query()
            ->whereIn('items.id', $itemIds)
            ->whereNotExists($this->itemReference('backpacks'))
            ->whereNotExists($this->itemReference('item_on_locations'))
            ->whereNotExists($this->itemReference('warehouses'))
            ->whereNotExists($this->itemReference('auctions'))
            ->whereNotExists($this->itemReference('auction_claims'))
            ->whereNotExists($this->itemReference('clan_warehouses'))
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('item_in_chest')
                    ->where(function (Builder $query): void {
                        $query->whereColumn('item_in_chest.item_id', 'items.id')
                            ->orWhereColumn('item_in_chest.chest_id', 'items.id');
                    });
            })
            ->delete();
    }

    private function itemReference(string $table): \Closure
    {
        return function (Builder $query) use ($table): void {
            $query->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.item_id', 'items.id');
        };
    }
}
