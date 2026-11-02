<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 6-й тир Печати Резонанса - heroic (ItemRarity: common < uncommon < rare
 * < epic < legendary < heroic). У самоцветов heroic-тира нет, поэтому имя
 * не из их линейки - "Изначальная", ступенью выше "Абсолютной" (legendary).
 * См. 2026_10_25_000001_..., 2026_10_25_000002_...
 */
return new class extends Migration
{
    private const NAME_RU = 'Изначальная Печать Резонанса';

    public function up(): void
    {
        $now = now();

        $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', self::NAME_RU)->value('id');

        if ($itemId === null) {
            $itemId = DB::table('share_items')->insertGetId([
                'type' => 'misc',
                'name' => json_encode(['ru' => self::NAME_RU, 'uk' => 'Первісна Печатка Резонансу'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Печать, выкованная ещё до того, как редкости артефактов получили названия, — резонанс от неё слышен не руками, а где-то глубже. Используется кузнецом при точке артефактов.',
                    'uk' => 'Печатка, викувана ще до того, як рідкості артефактів отримали назви, — резонанс від неї чутно не руками, а десь глибше. Використовується ковалем під час заточки артефактів.',
                ], JSON_UNESCAPED_UNICODE),
                'image' => null,
                'is_two_hand' => 0,
                'count_use' => 0,
                'is_active' => 1,
                'is_sell' => 1,
                'is_auction_sellable' => 1,
                'is_give' => 1,
                'is_clan_warehouse_allowed' => 1,
                'is_droppable' => 1,
                'is_stackable' => 1,
                'is_use' => 0,
                'is_slot_usable' => 0,
                'is_weight' => 0,
                'price' => 0,
                'diamond_price' => 0,
                'break_crystal' => 0,
                'gathering_speed_bonus_percent' => 0,
                'gathering_double_chance_percent' => 0,
                'rarity' => 'heroic',
                'upgrade_gold_cost' => 0,
                'drop_direct_to_backpack' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Самый узкий дроп из всех тиров - только "Шкатулка Бездны" (1689),
        // одна-единственная, не 2 сундука, как у legendary-тира.
        $exists = DB::table('share_item_has_items')
            ->where('parent_item_id', 1689)
            ->where('share_item_id', $itemId)
            ->exists();

        if (! $exists) {
            DB::table('share_item_has_items')->insert([
                'parent_item_id' => 1689,
                'share_item_id' => $itemId,
                'min_count' => 1,
                'max_count' => 1,
                'drop_chance' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', self::NAME_RU)->value('id');

        if ($itemId !== null) {
            DB::table('share_item_has_items')->where('share_item_id', $itemId)->delete();
            DB::table('share_items')->where('id', $itemId)->delete();
        }
    }
};
