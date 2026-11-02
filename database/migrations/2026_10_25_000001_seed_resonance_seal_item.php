<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Печать Резонанса» — материал для будущей механики точки артефактов
 * (точимый арт + 1 обычная копия того же арта + эта печать + золото = +1
 * уровень, гарантированный успех). Сама механика точки ещё не реализована
 * этой миграцией - только сам предмет и его дроп из сундуков/шкатулок.
 * type=misc по аналогии с «Купеческая Печать» (id=1750) - не сырьё и не
 * свиток апгрейда оружия (у того своя механика upgrade_scroll_type).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', 'Печать Резонанса')->value('id');

        if ($itemId === null) {
            $itemId = DB::table('share_items')->insertGetId([
                'type' => 'misc',
                'name' => json_encode(['ru' => 'Печать Резонанса', 'uk' => 'Печатка Резонансу'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Тонкая печать, что начинает едва слышно звенеть, если поднести её к двум одинаковым артефактам сразу — будто чувствует, что они могут стать единым целым. Используется кузнецом при точке артефактов.',
                    'uk' => 'Тонка печатка, яка починає ледь чутно дзвеніти, якщо піднести її одразу до двох однакових артефактів — наче відчуває, що вони можуть стати єдиним цілим. Використовується ковалем під час заточки артефактів.',
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
                'rarity' => 'rare',
                'upgrade_gold_cost' => 0,
                'drop_direct_to_backpack' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Дроп из верхней половины сундуков/шкатулок (рунические/древние/
        // владыки/бездны тир) - 10%, по 1 шт., в том же диапазоне шанса,
        // что и другие ценные-но-не-гарантированные предметы в этих
        // контейнерах (см. "Рунная отмычка" 15-25% в тех же сундуках).
        $chestIds = [1670, 1671, 1672, 1673, 1674, 1675, 1676, 1677, 1687, 1688, 1689];

        foreach ($chestIds as $chestId) {
            $exists = DB::table('share_item_has_items')
                ->where('parent_item_id', $chestId)
                ->where('share_item_id', $itemId)
                ->exists();

            if (! $exists) {
                DB::table('share_item_has_items')->insert([
                    'parent_item_id' => $chestId,
                    'share_item_id' => $itemId,
                    'min_count' => 1,
                    'max_count' => 1,
                    'drop_chance' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', 'Печать Резонанса')->value('id');

        if ($itemId !== null) {
            DB::table('share_item_has_items')->where('share_item_id', $itemId)->delete();
            DB::table('share_items')->where('id', $itemId)->delete();
        }
    }
};
