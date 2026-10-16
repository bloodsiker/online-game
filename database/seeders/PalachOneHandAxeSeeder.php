<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Добавляет «Секач палача» — одноручный топор сета «Палач», по прямому
 * указанию пользователя (ранее в комплекте был только одноручный «Меч
 * палача» (519-547) и двуручный «Топор палача» (149/521/530/539/548),
 * одноручного топора не было вообще).
 *
 * Полная цепочка редкости (5 тиров с апгрейдом), стат-профиль и
 * экономика СКОПИРОВАНЫ 1:1 с «Меч палача» (тот же слот/силовой уровень
 * одноручного оружия сета), требования (lvl20, intuition>=12,
 * skill_id=15 "Владение топором">=54) и skill_id=15 — скопированы с
 * двуручного «Топор палача» (тот же навык владения топором, но с
 * skill_exp=1 как у одноручного оружия, а не 2 как у двуручного).
 *
 * Без привязки к дропу — по прямому указанию пользователя (не нужно
 * вешать дроп с «Кровавый Палач» на этот предмет).
 */
class PalachOneHandAxeSeeder extends Seeder
{
    private const AXE_MASTERY_SKILL_ID = 15;

    /** @var list<array{rarity: string, name: string, description: string, price: int, upgrade_gold_cost: int, is_auction_sellable: bool, attack_min: int, attack_max: int, critical: int}> */
    private const TIERS = [
        [
            'rarity' => 'common',
            'name' => 'Секач палача',
            'description' => 'Одноручный секач сета «Палач». Используйте пару секачей для быстрого стиля боя или выберите двуручный топор для более сильного единичного удара.',
            'price' => 3375,
            'upgrade_gold_cost' => 1350,
            'is_auction_sellable' => true,
            'attack_min' => 12,
            'attack_max' => 17,
            'critical' => 3,
        ],
        [
            'rarity' => 'uncommon',
            'name' => 'Усиленный Секач палача',
            'description' => 'Одноручный секач сета «Палач». Используйте пару секачей для быстрого стиля боя или выберите двуручный топор для более сильного единичного удара. Необычная версия сета «Палач».',
            'price' => 3881,
            'upgrade_gold_cost' => 1552,
            'is_auction_sellable' => true,
            'attack_min' => 14,
            'attack_max' => 20,
            'critical' => 4,
        ],
        [
            'rarity' => 'rare',
            'name' => 'Закалённый Секач палача',
            'description' => 'Одноручный секач сета «Палач». Используйте пару секачей для быстрого стиля боя или выберите двуручный топор для более сильного единичного удара. Необычная версия сета «Палач». Редкая версия сета «Палач».',
            'price' => 4463,
            'upgrade_gold_cost' => 1785,
            'is_auction_sellable' => true,
            'attack_min' => 17,
            'attack_max' => 23,
            'critical' => 5,
        ],
        [
            'rarity' => 'epic',
            'name' => 'Эпический Секач палача',
            'description' => 'Одноручный секач сета «Палач». Используйте пару секачей для быстрого стиля боя или выберите двуручный топор для более сильного единичного удара. Необычная версия сета «Палач». Редкая версия сета «Палач». Эпическая версия сета «Палач».',
            'price' => 5133,
            'upgrade_gold_cost' => 2053,
            'is_auction_sellable' => false,
            'attack_min' => 20,
            'attack_max' => 27,
            'critical' => 6,
        ],
        [
            'rarity' => 'legendary',
            'name' => 'Легендарный Секач палача',
            'description' => 'Одноручный секач сета «Палач». Используйте пару секачей для быстрого стиля боя или выберите двуручный топор для более сильного единичного удара. Необычная версия сета «Палач». Редкая версия сета «Палач». Эпическая версия сета «Палач». Легендарная версия сета «Палач».',
            'price' => 5903,
            'upgrade_gold_cost' => 0,
            'is_auction_sellable' => false,
            'attack_min' => 23,
            'attack_max' => 32,
            'critical' => 7,
        ],
    ];

    public function run(): void
    {
        if (LocalizedItemSeederQuery::items()->where('name', self::TIERS[0]['name'])->exists()) {
            $this->command?->info('PalachOneHandAxeSeeder: «Секач палача» уже существует, пропускаю.');

            return;
        }

        $itemIds = [];

        foreach (self::TIERS as $tier) {
            $itemIds[] = LocalizedItemSeederQuery::items()->insertGetId([
                'type' => 'weapon',
                'name' => $tier['name'],
                'description' => $tier['description'],
                'image' => null,
                'transparent_image' => null,
                'is_two_hand' => 0,
                'count_use' => 0,
                'expire' => null,
                'max_drop_level_difference' => null,
                'is_heal' => null,
                'is_active' => 1,
                'is_sell' => 1,
                'is_auction_sellable' => $tier['is_auction_sellable'],
                'is_give' => 1,
                'is_clan_warehouse_allowed' => 1,
                'is_slot_usable' => 1,
                'is_droppable' => 1,
                'is_stackable' => 0,
                'is_use' => 0,
                'is_weight' => 0,
                'price' => $tier['price'],
                'break_crystal' => $tier['price'] - 1,
                'upgrade_scroll_type' => null,
                'gem_stats' => null,
                'rune_rarity' => null,
                'rune_stat_pool' => null,
                'innate_passive_type' => null,
                'innate_passive_value' => null,
                'slot' => 'hand',
                'tool_family' => null,
                'gathering_speed_bonus_percent' => 0,
                'gathering_double_chance_percent' => 0,
                'skill_id' => self::AXE_MASTERY_SKILL_ID,
                'skill_lvl' => null,
                'skill_exp' => 1,
                'gathering_time_seconds' => null,
                'gathering_respawn_seconds' => null,
                'gathering_tool_family' => null,
                'rarity' => $tier['rarity'],
                'upgrade_to_share_item_id' => null,
                'upgrade_gold_cost' => $tier['upgrade_gold_cost'],
                'required_active_effect_id' => null,
                'drop_direct_to_backpack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($itemIds as $index => $itemId) {
            $nextItemId = $itemIds[$index + 1] ?? null;

            LocalizedItemSeederQuery::items()->where('id', $itemId)->update([
                'upgrade_to_share_item_id' => $nextItemId,
                'updated_at' => now(),
            ]);

            $tier = self::TIERS[$index];

            DB::table('share_item_stats')->insert([
                ['share_item_id' => $itemId, 'stat_type' => 'attack_min', 'value' => $tier['attack_min'], 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
                ['share_item_id' => $itemId, 'stat_type' => 'attack_max', 'value' => $tier['attack_max'], 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
                ['share_item_id' => $itemId, 'stat_type' => 'critical', 'value' => $tier['critical'], 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('share_item_requirements')->insert([
                ['share_item_id' => $itemId, 'type' => 'level', 'stat_key' => null, 'skill_id' => null, 'min_value' => 20, 'created_at' => now(), 'updated_at' => now()],
                ['share_item_id' => $itemId, 'type' => 'stat', 'stat_key' => 'intuition', 'skill_id' => null, 'min_value' => 12, 'created_at' => now(), 'updated_at' => now()],
                ['share_item_id' => $itemId, 'type' => 'skill', 'stat_key' => null, 'skill_id' => self::AXE_MASTERY_SKILL_ID, 'min_value' => 54, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->command?->info('PalachOneHandAxeSeeder: создано 5 тиров «Секач палача», id='.implode(',', $itemIds));
    }
}
