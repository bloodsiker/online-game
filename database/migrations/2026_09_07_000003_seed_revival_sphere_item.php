<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Сфера возрождения» — многоразовый предмет премиум-магазина. Использовать
 * можно только пока активно «Возрождение» (30 минут после смерти) — тогда
 * она возвращает опыт, потерянный при последней гибели. См.
 * RestoreLostExpStrategy и PlayerRevivalService.
 *
 * Категория «Артефакты» (id 2) у премиум-магазина (structure_id 10) уже
 * подключена, поэтому отдельной привязки категории к структуре не требуется.
 */
return new class extends Migration
{
    private const PREMIUM_SHOP_STRUCTURE_ID = 10;

    private const ARTIFACTS_CATEGORY_ID = 2;

    public function up(): void
    {
        $now = now();

        $shareItemId = DB::table('share_items')->insertGetId([
            'type' => 'artifact',
            'name' => 'Сфера возрождения',
            'description' => 'Пока действует «Возрождение» (30 минут после гибели), возвращает опыт, '.
                'потерянный при последней смерти. Сфера многоразовая — 5 использований.',
            'image' => null,
            'is_two_hand' => 0,
            'count_use' => 5,
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
            'rarity' => 'epic',
            'upgrade_gold_cost' => 0,
            'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('share_item_effects')->insert([
            'share_item_id' => $shareItemId,
            // value/value_type — техническая заглушка: реальная сумма
            // берётся из current_value активного эффекта «Возрождение»,
            // а не отсюда, см. RestoreLostExpStrategy.
            'effect_type' => 'restore_lost_exp',
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
            'diamond' => 150,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $shareItemId = DB::table('share_items')
            ->where('type', 'artifact')
            ->where('name', 'Сфера возрождения')
            ->value('id');

        if ($shareItemId === null) {
            return;
        }

        DB::table('shop_items')->where('share_item_id', $shareItemId)->delete();
        DB::table('share_item_effects')->where('share_item_id', $shareItemId)->delete();
        DB::table('share_items')->where('id', $shareItemId)->delete();
    }
};
