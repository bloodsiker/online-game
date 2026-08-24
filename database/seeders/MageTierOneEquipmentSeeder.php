<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Seeder;

/**
 * Тир 1 мага (1-20): семь предметов одежды без оружия и щита.
 *
 * Набор повторяет броню воинского Тир 1 без щита (21 на 20 уровне), но вместо
 * блока получает немного интеллекта, мудрости и выносливости. MAGIC_ATTACK
 * намеренно отсутствует: текущий маг уже проходит Огра 20 уровня без предметов
 * в 100% PvE-симуляций, поэтому ранняя экипировка не должна резко повышать DPS.
 */
class MageTierOneEquipmentSeeder extends Seeder
{
    private const SHOP_STRUCTURE_ID = 2;

    private const SHOP_ARMOR_CATEGORY_ID = 5;

    /** @var array<int, array{name: string, level: int, slot: ShareItemSlot, gate: PlayerStatKey, stats: array<int, array{0: ShareItemStatType, 1: int}>}> */
    private const ITEMS = [
        [
            'name' => 'Роба ученика',
            'level' => 1,
            'slot' => ShareItemSlot::ARMOR,
            'gate' => PlayerStatKey::INTELLIGENCE,
            'stats' => [[ShareItemStatType::ARMOR, 2], [ShareItemStatType::INTELLIGENCE, 1], [ShareItemStatType::ENDURANCE, 1]],
        ],
        [
            'name' => 'Сапоги странника',
            'level' => 2,
            'slot' => ShareItemSlot::SHOES,
            'gate' => PlayerStatKey::WISDOM,
            'stats' => [[ShareItemStatType::ARMOR, 1], [ShareItemStatType::WISDOM, 1]],
        ],
        [
            'name' => 'Наручи заклинателя',
            'level' => 4,
            'slot' => ShareItemSlot::FOREARM,
            'gate' => PlayerStatKey::INTELLIGENCE,
            'stats' => [[ShareItemStatType::ARMOR, 2], [ShareItemStatType::INTELLIGENCE, 1]],
        ],
        [
            'name' => 'Капюшон провидца',
            'level' => 7,
            'slot' => ShareItemSlot::HELMET,
            'gate' => PlayerStatKey::WISDOM,
            'stats' => [[ShareItemStatType::ARMOR, 3], [ShareItemStatType::WISDOM, 1]],
        ],
        [
            'name' => 'Поножи переписчика',
            'level' => 10,
            'slot' => ShareItemSlot::LEGGING,
            'gate' => PlayerStatKey::INTELLIGENCE,
            'stats' => [[ShareItemStatType::ARMOR, 4], [ShareItemStatType::INTELLIGENCE, 1], [ShareItemStatType::ENDURANCE, 1]],
        ],
        [
            'name' => 'Наплечники астрала',
            'level' => 13,
            'slot' => ShareItemSlot::SHOULDER,
            'gate' => PlayerStatKey::WISDOM,
            'stats' => [[ShareItemStatType::ARMOR, 4], [ShareItemStatType::WISDOM, 1]],
        ],
        [
            'name' => 'Мантия посвящённого',
            'level' => 20,
            'slot' => ShareItemSlot::CHAIN_ARMOR,
            'gate' => PlayerStatKey::INTELLIGENCE,
            'stats' => [[ShareItemStatType::ARMOR, 5], [ShareItemStatType::INTELLIGENCE, 1], [ShareItemStatType::WISDOM, 1], [ShareItemStatType::ENDURANCE, 1], [ShareItemStatType::MAGIC_RESISTANCE, 2]],
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::ITEMS as $definition) {
            $item = ShareItem::firstOrCreate(
                ['name' => $definition['name']],
                [
                    'type' => ShareItemType::ARMOR,
                    'slot' => $definition['slot'],
                    'rarity' => ItemRarity::COMMON,
                    'price' => $this->price($definition['level']),
                    'is_two_hand' => false,
                ],
            );

            if (! $item->wasRecentlyCreated) {
                $this->addToShop($item, $definition['level']);

                continue;
            }

            foreach ($definition['stats'] as [$statType, $value]) {
                ShareItemStat::create([
                    'share_item_id' => $item->id,
                    'stat_type' => $statType,
                    'value' => $value,
                    'value_type' => ItemEffectValueType::FLAT,
                ]);
            }

            ShareItemRequirement::create([
                'share_item_id' => $item->id,
                'type' => ShareItemRequirementType::LEVEL,
                'min_value' => $definition['level'],
            ]);
            ShareItemRequirement::create([
                'share_item_id' => $item->id,
                'type' => ShareItemRequirementType::STAT,
                'stat_key' => $definition['gate']->value,
                'min_value' => max(1, (int) round($definition['level'] * 0.6)),
            ]);

            $this->addToShop($item, $definition['level']);

            $created++;
        }

        $this->command?->info("MageTierOneEquipmentSeeder: создано предметов — {$created}");
    }

    private function price(int $level): int
    {
        return (int) round(50 * $level ** 1.5);
    }

    private function addToShop(ShareItem $item, int $level): void
    {
        ShopItem::firstOrCreate(
            [
                'structure_id' => self::SHOP_STRUCTURE_ID,
                'share_item_id' => $item->id,
            ],
            [
                'share_structure_category_id' => self::SHOP_ARMOR_CATEGORY_ID,
                'price' => $this->price($level),
                'sort_order' => $level,
            ],
        );
    }
}
