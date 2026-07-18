<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentBlacksmithReadRepository implements BlacksmithReadRepository
{
    public function findStructureOrFail(int $id): Structure
    {
        return Structure::findOrFail($id);
    }

    public function findCrystalOrFail(): ShareItem
    {
        return ShareItem::findOrFail(23);
    }

    public function getCraftRecipes(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('share_items.type', ShareItemType::RECIPE->value)
            ->get();
    }

    public function getResourceCounts(User $user): array
    {
        return DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RESOURCE)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'count' => $item->count,
            ])
            ->toArray();
    }

    public function getBreakableItems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [ShareItemType::WEAPON->value, ShareItemType::SHIELD->value, ShareItemType::ARMOR->value])
            ->get();
    }

    public function getUpgradeableItems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [
                ShareItemType::WEAPON->value,
                ShareItemType::SHIELD->value,
                ShareItemType::ARMOR->value,
                ShareItemType::BELT->value,
            ])
            ->get();
    }

    public function getBaseScrolls(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->where('share_items.upgrade_scroll_type', UpgradeScrollType::BASE->value)
            ->get();
    }

    public function getBonusScrolls(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->whereIn('share_items.upgrade_scroll_type', [
                UpgradeScrollType::PROTECTION->value,
                UpgradeScrollType::STABILIZER->value,
                UpgradeScrollType::LUCKY->value,
            ])
            ->get();
    }

    public function getSocketableItems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.gems'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [
                ShareItemType::WEAPON->value,
                ShareItemType::SHIELD->value,
                ShareItemType::ARMOR->value,
                ShareItemType::BELT->value,
            ])
            ->get();
    }

    public function getGems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::GEM->value)
            ->get();
    }

    public function getMounts(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::MOUNT->value)
            ->get();
    }

    public function getImbueableItems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.runes'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [ShareItemType::WEAPON->value, ShareItemType::SHIELD->value])
            ->get();
    }

    public function getRunes(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RUNE->value)
            ->get();
    }

    public function getRuneKeys(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RUNE_KEY->value)
            ->get();
    }
}
