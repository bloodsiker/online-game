<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Комплект «Титан» уже содержал кастет, но только 3 тира редкости
 * (rare/epic/legendary, id 1302/1303/1304) — без базового uncommon-тира,
 * которым начинается КАЖДАЯ другая линия предмета этого сета (Булава,
 * Молот, Щит, вся броня — везде ровно 4 тира uncommon→legendary). По
 * прямому указанию пользователя достраивает недостающий uncommon-тир.
 *
 * Стат-профиль uncommon-тира (attack 45-67, endurance 13) скопирован с
 * «Булава «Титан»» (1385) — существующие rare/epic/legendary кастета
 * буквально повторяют стат-профиль булавы на тех же тирах (1302=1386,
 * 1303=1387, 1304=1388 — те же числа), значит и недостающий uncommon
 * должен совпадать с uncommon булавы.
 *
 * upgrade_gold_cost/break_crystal — не взяты у булавы (там реальная
 * цена, тут price=1 — предмет не продаётся, только дроп/крафт), а
 * экстраполированы НАЗАД по собственному отношению кастета между уже
 * существующими тирами: upgrade_gold_cost(epic)/upgrade_gold_cost(rare)
 * = 5670137/4158100 ≈ 1.3637, и break_crystal(tier) = round(
 * upgrade_gold_cost(tier) * 1.25) — это отношение точно (до округления)
 * выполняется на всех 3 существующих тирах кастета.
 *
 * Заодно чинит независимо найденный баг: skill_id=3 («Владение мечом»)
 * на всех 3 существующих тирах — должен быть 25 («Владение кастетом»,
 * как у ВСЕХ остальных кастетов в игре, включая «Кастет «Мамонт»» и
 * «Кастет новичка») — по прямому подтверждению пользователя.
 */
class TitanKnuckleUncommonTierSeeder extends Seeder
{
    private const KNUCKLE_SKILL_ID = 25;

    private const EXISTING_TIER_IDS = [1302, 1303, 1304];

    private const RARE_TIER_ID = 1302;

    private const NEW_NAME = 'Кастет «Титан»';

    private const PLACEHOLDER_IMAGE = 'http://localhost:85/storage/items/FDnJLLYTK80WKQFbIuPCqr2vAzKbCSim0QpndqtX.gif';

    public function run(): void
    {
        $fixed = DB::table('share_items')
            ->whereIn('id', self::EXISTING_TIER_IDS)
            ->where('skill_id', 3)
            ->update(['skill_id' => self::KNUCKLE_SKILL_ID, 'updated_at' => now()]);
        $this->command?->info("skill_id исправлен на {$fixed} существующих тирах кастета «Титан» (3 → 25)");

        if (DB::table('share_items')->where('name', self::NEW_NAME)->exists()) {
            $this->command?->info('TitanKnuckleUncommonTierSeeder: «Кастет «Титан»» уже существует, пропускаю.');

            return;
        }

        $upgradeGoldCost = 3049128;
        $breakCrystal = (int) round($upgradeGoldCost * 1.25);

        $newItemId = DB::table('share_items')->insertGetId([
            'type' => 'weapon',
            'name' => self::NEW_NAME,
            'description' => '',
            'image' => self::PLACEHOLDER_IMAGE,
            'transparent_image' => null,
            'is_two_hand' => 0,
            'count_use' => 0,
            'expire' => null,
            'max_drop_level_difference' => null,
            'is_heal' => null,
            'is_active' => 1,
            'is_sell' => 1,
            'is_auction_sellable' => 0,
            'is_give' => 1,
            'is_clan_warehouse_allowed' => 1,
            'is_slot_usable' => 1,
            'is_droppable' => 1,
            'is_stackable' => 0,
            'is_use' => 0,
            'is_weight' => 0,
            'price' => 1,
            'break_crystal' => $breakCrystal,
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
            'skill_id' => self::KNUCKLE_SKILL_ID,
            'skill_lvl' => null,
            'skill_exp' => 2,
            'gathering_time_seconds' => null,
            'gathering_respawn_seconds' => null,
            'gathering_tool_family' => null,
            'rarity' => 'uncommon',
            'upgrade_to_share_item_id' => self::RARE_TIER_ID,
            'upgrade_gold_cost' => $upgradeGoldCost,
            'required_active_effect_id' => null,
            'drop_direct_to_backpack' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('share_item_stats')->insert([
            ['share_item_id' => $newItemId, 'stat_type' => 'attack_min', 'value' => 45, 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['share_item_id' => $newItemId, 'stat_type' => 'attack_max', 'value' => 67, 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['share_item_id' => $newItemId, 'stat_type' => 'endurance', 'value' => 13, 'value_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('share_item_requirements')->insert([
            ['share_item_id' => $newItemId, 'type' => 'level', 'stat_key' => null, 'skill_id' => null, 'min_value' => 55, 'created_at' => now(), 'updated_at' => now()],
            ['share_item_id' => $newItemId, 'type' => 'stat', 'stat_key' => 'strength', 'skill_id' => null, 'min_value' => 33, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command?->info(sprintf(
            'TitanKnuckleUncommonTierSeeder: создан uncommon-тир id=%d (-> %d)',
            $newItemId,
            self::RARE_TIER_ID,
        ));
    }
}
