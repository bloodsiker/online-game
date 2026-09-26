<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Свиток перерождения» — одноразовый расходник премиум-магазина. Сбрасывает
 * все очки характеристик, вложенные игроком через AllocateStats (см.
 * RespecStatsStrategy), возвращая их в free_stats. Расовый прирост
 * (RecalculatePlayerStats на level-up) не затрагивается.
 *
 * Категория «Артефакты» (id 2) у премиум-магазина (structure_id 10) уже
 * подключена — зеркалит паттерн 2026_09_07_000003_seed_revival_sphere_item.php.
 */
return new class extends Migration
{
    private const PREMIUM_SHOP_STRUCTURE_ID = 10;

    private const ARTIFACTS_CATEGORY_ID = 2;

    public function up(): void
    {
        $now = now();

        $shareItemId = DB::table('share_items')->insertGetId([
            'type' => 'scroll',
            'name' => 'Свиток перерождения',
            'description' => 'Сбрасывает все очки характеристик, вложенные вами вручную, и возвращает их '.
                'в свободный запас для нового распределения. Прирост от расы не затрагивается. Расходуется при использовании.',
            'image' => null,
            'is_two_hand' => 0,
            'count_use' => 1,
            'is_active' => 1,
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'is_give' => 0,
            'is_droppable' => 0,
            'is_stackable' => 1,
            'is_slot_usable' => 0,
            'is_weight' => 1,
            'price' => 0,
            'break_crystal' => 0,
            'rarity' => 'rare',
            'upgrade_gold_cost' => 0,
            'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('share_item_effects')->insert([
            'share_item_id' => $shareItemId,
            // value/value_type — техническая заглушка: сумма возврата считается
            // динамически из фактических данных игрока, см. RespecStatsStrategy.
            'effect_type' => 'respec_stats',
            'value' => 0,
            'value_type' => 'flat',
            'duration_seconds' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('shop_items')->insert([
            'structure_id' => self::PREMIUM_SHOP_STRUCTURE_ID,
            'share_item_id' => $shareItemId,
            'share_structure_category_id' => self::ARTIFACTS_CATEGORY_ID,
            'price' => 0,
            'diamond' => 200,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $shareItemId = DB::table('share_items')
            ->where('type', 'scroll')
            ->where('name', 'Свиток перерождения')
            ->value('id');

        if ($shareItemId === null) {
            return;
        }

        DB::table('shop_items')->where('share_item_id', $shareItemId)->delete();
        DB::table('share_item_effects')->where('share_item_id', $shareItemId)->delete();
        DB::table('share_items')->where('id', $shareItemId)->delete();
    }
};
