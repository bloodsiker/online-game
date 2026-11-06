<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuestInventoryService
{
    /** The enclosing quest transaction holds the user lock. Requirements for identical items are summed. */
    public function consume(User $user, Collection $objectives): void
    {
        $requirements = $objectives->whereIn('type', ['deliver', 'collect'])
            ->filter(fn ($objective) => $objective->requiredShareItemId() !== null)
            ->groupBy(fn ($objective): int => $objective->requiredShareItemId())
            ->map(fn ($group): int => (int) $group->sum('required_amount'));
        if ($requirements->isEmpty()) {
            return;
        }
        $rows = Backpack::query()->select('backpacks.*')->addSelect('items.share_item_id as required_share_item_id')
            ->join('items', 'items.id', '=', 'backpacks.item_id')->where('backpacks.user_id', $user->id)
            ->where('equipped', 0)->whereIn('items.share_item_id', $requirements->keys())
            ->orderBy('backpacks.id')->lockForUpdate()->get();
        foreach ($requirements as $shareItemId => $amount) {
            $matching = $rows->where('required_share_item_id', $shareItemId);
            if ($amount < 0 || (int) $matching->sum('count') < $amount) {
                throw ValidationException::withMessages(['quest' => 'В рюкзаке нет всех необходимых предметов.']);
            }
        }
        foreach ($requirements as $shareItemId => $amount) {
            foreach ($rows->where('required_share_item_id', $shareItemId) as $row) {
                if ($amount === 0) {
                    break;
                }
                $used = min($amount, (int) $row->count);
                if ($used <= 0) {
                    continue;
                }
                $row->decrement('count', $used);
                $amount -= $used;
                if ((int) $row->count === 0) {
                    $row->delete();
                    if (! Backpack::where('item_id', $row->item_id)->exists() && ! ItemOnLocation::where('item_id', $row->item_id)->exists()) {
                        Item::whereKey($row->item_id)->delete();
                    }
                }
            }
        }
    }
}
