<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Добавляет 6-й (героический) тир «Кладоискателя» поверх существующих 5
 * (common/uncommon/rare/epic/legendary, см. TreasureHuntSeeder): карта,
 * шкатулка, 4 фрагмента и сборка. Прогрессия продолжает уже установленные
 * ряды (required_reputation/sort_order 0→500→1500→3000→6000→12000,
 * search_duration 12→20→30→40→50→60, success_chance 70→45→25→20→15→10,
 * lock_duration 12→16→20→24→28→32, trap_damage% 0→2→4→6→8→10).
 * lock_required_skill уже упирается в системный потолок 300 на legendary —
 * у heroic остаётся тем же (see Admin\ItemController::fillItem rule
 * 'between:0,300'). Ресурс/зелье/отмычка-бонус в шкатулке тоже упираются в
 * потолок каталога (нет промежуточного тира между legendary и heroic для
 * этих конкретных предметов — legendary-шкатулка уже выдаёт heroic-тир
 * ресурса/зелья/отмычки), поэтому у heroic-шкатулки те же предметы, но
 * повышенные количество/шанс.
 */
return new class extends Migration
{
    private const REPUTATION_ID = 35; // «Кладоискатель»

    private const SCROLL_PLACEHOLDER = 'items/2026/10/DOzUasqeWK9RIe47GnBoKJKbhaGmnAZGvOu66LzX.webp';

    public function up(): void
    {
        $now = now();
        $clue = [
            'ru' => 'Территория определяется при сборке карты. Отправляйтесь в указанную на готовой карте область и сверяйте карту с местностью, чтобы найти место клада.',
            'uk' => 'Територія визначається під час збирання карти. Вирушайте до зазначеної на готовій карті області та звіряйте карту з місцевістю, щоб знайти місце скарбу.',
        ];

        $mapId = $this->insertShareItem([
            'type' => 'treasure_map', 'rarity' => 'heroic', 'image' => self::SCROLL_PLACEHOLDER,
            'name' => ['ru' => 'Героическая карта сокровищ', 'uk' => 'Героїчна карта скарбів'],
            'description' => $clue,
            'is_stackable' => 0, 'is_give' => 0, 'is_auction_sellable' => 0, 'is_clan_warehouse_allowed' => 0,
            'is_sell' => 0, 'is_use' => 0, 'is_droppable' => 1, 'is_weight' => 1,
        ], $now);

        $casketId = $this->insertShareItem([
            'type' => 'chest', 'rarity' => 'heroic', 'image' => null,
            'name' => ['ru' => 'Героическая шкатулка сокровищ', 'uk' => 'Героїчна скринька скарбів'],
            'description' => [
                'ru' => 'Закрытая находка кладоискателя. Откройте замок отмычкой, чтобы забрать содержимое.',
                'uk' => 'Закрита знахідка шукача скарбів. Зламайте замок відмичкою, щоб забрати вміст.',
            ],
            'is_stackable' => 0, 'is_give' => 1, 'is_auction_sellable' => 0, 'is_clan_warehouse_allowed' => 1,
            'is_sell' => 0, 'is_use' => 1, 'is_droppable' => 1, 'is_weight' => 1,
        ], $now);

        $fragmentIds = [];
        foreach (['I', 'II', 'III', 'IV'] as $roman) {
            $fragmentIds[] = $this->insertShareItem([
                'type' => 'treasure_map_fragment', 'rarity' => 'heroic', 'image' => self::SCROLL_PLACEHOLDER,
                'name' => ['ru' => "Героическая карта сокровищ — фрагмент {$roman}", 'uk' => "Героїчна карта скарбів — фрагмент {$roman}"],
                'description' => [
                    'ru' => 'Часть карты сокровищ. Соберите четыре разных фрагмента I, II, III и IV этого тира. Нажмите «Собрать карту» в рюкзаке: по одному фрагменту каждого вида превратятся в одну карту.',
                    'uk' => 'Частина карти скарбів. Зберіть чотири різні фрагменти I, II, III та IV цього рівня. Натисніть «Зібрати карту» в рюкзаку: по одному фрагменту кожного виду перетворяться на одну карту.',
                ],
                'is_stackable' => 1, 'is_give' => 1, 'is_auction_sellable' => 1, 'is_clan_warehouse_allowed' => 1,
                'is_sell' => 0, 'is_use' => 0, 'is_droppable' => 1, 'is_weight' => 1,
            ], $now);
        }

        DB::table('share_item_lock_configs')->insert([
            'share_item_id' => $casketId, 'lock_required_skill' => 300, 'lock_duration_seconds' => 32,
            'experience_reward' => 37, 'minimum_success_chance_percent' => 5, 'trap_chance_penalty_percent' => 15,
            'trap_effect_id' => 19, 'trap_effect_duration_seconds' => 210, 'trap_damage_percent' => 10,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ([
            ['share_item_id' => 1690, 'min_count' => 450, 'max_count' => 900, 'drop_chance' => 100], // Горстка монет
            ['share_item_id' => 777, 'min_count' => 3, 'max_count' => 4, 'drop_chance' => 100], // Алмазная колба (heroic-ресурс, потолок каталога)
            ['share_item_id' => 763, 'min_count' => 2, 'max_count' => 3, 'drop_chance' => 85], // Героическое зелье лечения (потолок каталога)
            ['share_item_id' => 1662, 'min_count' => 1, 'max_count' => 1, 'drop_chance' => 35], // Рунная отмычка (потолок каталога)
        ] as $content) {
            DB::table('share_item_has_items')->insert([
                'parent_item_id' => $casketId, ...$content, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        DB::table('treasure_map_configs')->insert([
            'code' => 'heroic', 'share_item_id' => $mapId, 'reputation_id' => self::REPUTATION_ID,
            'reward_share_item_id' => $casketId, 'map_id' => null,
            'required_reputation' => 12000, 'reputation_reward' => 110,
            'search_duration_seconds' => 60, 'success_chance_percent' => 10.000,
            'clue' => json_encode($clue, JSON_UNESCAPED_UNICODE), 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $assemblyId = DB::table('item_assemblies')->insertGetId([
            'name' => json_encode(['ru' => 'Сборка: Героическая карта сокровищ', 'uk' => 'Збирання: Героїчна карта скарбів'], JSON_UNESCAPED_UNICODE),
            'type' => 'treasure_map', 'result_mode' => 'fixed', 'is_active' => 1, 'sort_order' => 12000,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ($fragmentIds as $fragmentId) {
            DB::table('item_assembly_components')->insert([
                'item_assembly_id' => $assemblyId, 'share_item_id' => $fragmentId, 'count' => 1,
            ]);
        }
        DB::table('item_assembly_rewards')->insert([
            'item_assembly_id' => $assemblyId, 'share_item_id' => $mapId, 'count' => 1, 'weight' => 1,
        ]);
    }

    /** @param array<string, mixed> $fields */
    private function insertShareItem(array $fields, Carbon $now): int
    {
        return DB::table('share_items')->insertGetId(array_merge([
            'name' => json_encode($fields['name'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($fields['description'], JSON_UNESCAPED_UNICODE),
            'is_two_hand' => 0, 'count_use' => 0, 'is_active' => 1,
            'is_slot_usable' => 0, 'price' => 0, 'diamond_price' => 0, 'break_crystal' => 0,
            'gathering_speed_bonus_percent' => 0, 'gathering_double_chance_percent' => 0,
            'upgrade_gold_cost' => 0, 'drop_direct_to_backpack' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ], collect($fields)->except(['name', 'description'])->all()));
    }

    public function down(): void
    {
        $config = DB::table('treasure_map_configs')->where('code', 'heroic')->first();
        if ($config === null) {
            return;
        }
        $assembly = DB::table('item_assemblies')->where('type', 'treasure_map')->where('sort_order', 12000)->first();
        if ($assembly !== null) {
            $fragmentIds = DB::table('item_assembly_components')->where('item_assembly_id', $assembly->id)->pluck('share_item_id');
            DB::table('item_assembly_rewards')->where('item_assembly_id', $assembly->id)->delete();
            DB::table('item_assembly_components')->where('item_assembly_id', $assembly->id)->delete();
            DB::table('item_assemblies')->where('id', $assembly->id)->delete();
            DB::table('share_items')->whereIn('id', $fragmentIds)->delete();
        }
        DB::table('share_item_has_items')->where('parent_item_id', $config->reward_share_item_id)->delete();
        DB::table('share_item_lock_configs')->where('share_item_id', $config->reward_share_item_id)->delete();
        DB::table('treasure_map_configs')->where('id', $config->id)->delete();
        DB::table('share_items')->whereIn('id', [$config->share_item_id, $config->reward_share_item_id])->delete();
    }
};
