<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TOOL_WORKSHOP_TYPE = 'tool_workshop';

    public function up(): void
    {
        DB::statement("ALTER TABLE structures MODIFY type ENUM(
            'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
            'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
            'barter_shop', 'workshop', 'tool_workshop'
        ) NULL DEFAULT 'shop'");

        $now = now();
        DB::table('structures')->updateOrInsert(
            ['location_id' => 51, 'type' => self::TOOL_WORKSHOP_TYPE],
            [
                'name' => 'Мастерская инструментов',
                'npc_id' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $chains = [
            [361, 499, 500, [[421, 1]]],
            [499, 500, 1500, [[421, 1], [429, 1]]],
            [500, 501, 5000, [[429, 1], [431, 1]]],
            [501, 502, 15000, [[431, 1], [433, 1]]],
            [502, 503, 50000, [[433, 1]]],
            [363, 504, 500, [[421, 1]]],
            [504, 505, 1500, [[421, 1], [429, 1]]],
            [505, 506, 5000, [[429, 1], [431, 1]]],
            [506, 507, 15000, [[431, 1], [433, 1]]],
            [507, 508, 50000, [[433, 1]]],
            [365, 509, 500, [[421, 1]]],
            [509, 510, 1500, [[421, 1], [429, 1]]],
            [510, 511, 5000, [[429, 1], [431, 1]]],
            [511, 512, 15000, [[431, 1], [433, 1]]],
            [512, 513, 50000, [[433, 1]]],
            [373, 514, 500, [[421, 1]]],
            [514, 515, 1500, [[421, 1], [429, 1]]],
            [515, 516, 5000, [[429, 1], [431, 1]]],
            [516, 517, 15000, [[431, 1], [433, 1]]],
            [517, 518, 50000, [[433, 1]]],
        ];

        foreach ($chains as [$sourceId, $targetId, $goldCost, $materials]) {
            DB::table('share_items')->where('id', $sourceId)->update([
                'upgrade_to_share_item_id' => $targetId,
                'upgrade_gold_cost' => $goldCost,
                'updated_at' => $now,
            ]);

            DB::table('share_item_upgrade_materials')->where('share_item_id', $sourceId)->delete();
            foreach ($materials as [$materialId, $count]) {
                DB::table('share_item_upgrade_materials')->insert([
                    'share_item_id' => $sourceId,
                    'required_share_item_id' => $materialId,
                    'count' => $count,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $sourceIds = [361, 499, 500, 501, 502, 363, 504, 505, 506, 507, 365, 509, 510, 511, 512, 373, 514, 515, 516, 517];

        DB::table('share_item_upgrade_materials')->whereIn('share_item_id', $sourceIds)->delete();
        DB::table('share_items')->whereIn('id', $sourceIds)->update([
            'upgrade_to_share_item_id' => null,
            'upgrade_gold_cost' => 0,
        ]);
        DB::table('structures')
            ->where('location_id', 51)
            ->where('type', self::TOOL_WORKSHOP_TYPE)
            ->delete();

        DB::statement("ALTER TABLE structures MODIFY type ENUM(
            'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
            'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
            'barter_shop', 'workshop'
        ) NULL DEFAULT 'shop'");
    }
};
