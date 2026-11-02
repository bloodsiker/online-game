<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Services;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Validation\ValidationException;

final class TreasureMapPublicationGuard
{
    public function assertCanActivate(ShareItem $item, ShareItemType $type, bool $active): void
    {
        if ($type !== ShareItemType::TREASURE_MAP || ! $active) {
            return;
        }
        if (! $item->exists || ! TreasureMapConfig::where('share_item_id', $item->id)->exists()) {
            throw ValidationException::withMessages([
                'is_active' => 'Нельзя активировать карту сокровищ без настроек поиска. Сначала сохраните предмет как неактивный, создайте конфиг в разделе «Карты сокровищ», затем активируйте предмет.',
            ]);
        }
    }

    public function assertCanReassign(TreasureMapConfig $config, int $newItemId): void
    {
        if ($config->exists && (int) $config->share_item_id !== $newItemId
            && ShareItem::whereKey($config->share_item_id)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'share_item_id' => 'Сначала отключите прежнюю карту сокровищ в справочнике предметов. Нельзя переносить конфиг, оставляя активную карту без настроек поиска.',
            ]);
        }
    }
}
