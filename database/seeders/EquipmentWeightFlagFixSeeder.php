<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Проставляет share_items.is_weight=1 для ВСЕЙ экипировки и оружия
 * (weapon/armor/shield/belt) — по прямому указанию пользователя.
 *
 * is_weight — это флаг «занимает слот вместимости рюкзака»
 * (BackpackService::getCountableItemsCount() считает только предметы с
 * is_weight=true, отображается как «Вместимость: X/Y» в bag.blade.php).
 * Обнаружен реальный пробел: 184 из 195 weapon, 336 из 363 armor и
 * 1 из 14 shield-предметов имели is_weight=0 — то есть почти вся
 * экипировка в игре физически не занимала место в рюкзаке.
 *
 * tool/bag/artifact сознательно НЕ трогаются — пользователь просил
 * именно «экипировку и оружие», а не весь ShareItemType::isEquipment().
 */
class EquipmentWeightFlagFixSeeder extends Seeder
{
    private const TYPES = ['weapon', 'armor', 'shield', 'belt'];

    public function run(): void
    {
        $updated = LocalizedItemSeederQuery::items()
            ->whereIn('type', self::TYPES)
            ->where('is_weight', false)
            ->update(['is_weight' => true, 'updated_at' => now()]);

        $this->command?->info("EquipmentWeightFlagFixSeeder: is_weight=1 проставлен {$updated} предметам (weapon/armor/shield/belt)");
    }
}
