<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Расширение пула случайного результата рецепта осколков (share_recipe_id=145,
 * см. 2026_10_18_000002_seed_common_artifact_shards.php) ещё двумя обычными
 * артефактами — по прямому указанию пользователя. Пул становится: 924, 925,
 * 926, 1835 («Рог Натиска», переименован из «Шип Ярости» прямым SQL — тот же
 * предмет, те же статы armor+critical) + два новых отсюда. Все 6 по
 * равному весу 25 (25/150 ≈ 16.7% каждый).
 */
return new class extends Migration
{
    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, stats: list<array{type: string, value: int}>}> */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Хребет Берсерка', 'uk' => 'Хребет Берсерка'],
            'description' => [
                'ru' => 'Один позвонок-шип, вырванный из хребта берсерка-зверя ещё при жизни, — оправлен грубыми железными скобами, будто боится срастись обратно.',
                'uk' => 'Один хребець-шип, вирваний із хребта берсерка-звіра ще за життя, — оправлений грубими залізними скобами, наче боїться зрости назад.',
            ],
            'stats' => [
                ['type' => 'dodge', 'value' => 4],
                ['type' => 'crit_damage', 'value' => 4],
            ],
        ],
        [
            'name' => ['ru' => 'Чешуя Ярости', 'uk' => 'Луска Люті'],
            'description' => [
                'ru' => 'Единственная чешуйка с хребта дракона, что сгорел в собственной ярости, — тёплая на ощупь даже спустя века, будто пламя всё ещё тлеет внутри.',
                'uk' => 'Єдина лусочка з хребта дракона, що згорів у власній люті, — тепла на дотик навіть через століття, наче полум\'я досі тліє всередині.',
            ],
            'stats' => [
                ['type' => 'wisdom', 'value' => 4],
                ['type' => 'magic_crit_damage', 'value' => 4],
            ],
        ],
    ];

    private const RECIPE_ID = 145;

    private const POOL_CHANCE = 25;

    public function up(): void
    {
        $now = now();

        foreach (self::ARTIFACTS as $artifact) {
            $existingId = DB::table('share_items')
                ->where('type', 'artifact')
                ->where('name->ru', $artifact['name']['ru'])
                ->value('id');

            if ($existingId !== null) {
                $shareItemId = $existingId;
            } else {
                $shareItemId = DB::table('share_items')->insertGetId([
                    'type' => 'artifact',
                    'name' => json_encode($artifact['name'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode($artifact['description'], JSON_UNESCAPED_UNICODE),
                    'image' => null,
                    'is_two_hand' => 0,
                    'count_use' => 0,
                    'is_active' => 1,
                    'is_sell' => 1,
                    'is_auction_sellable' => 1,
                    'is_give' => 1,
                    'is_clan_warehouse_allowed' => 1,
                    'is_droppable' => 1,
                    'is_stackable' => 0,
                    'is_use' => 0,
                    'is_slot_usable' => 0,
                    'is_weight' => 0,
                    'price' => 0,
                    'diamond_price' => 0,
                    'break_crystal' => 100,
                    'gathering_speed_bonus_percent' => 0,
                    'gathering_double_chance_percent' => 0,
                    'rarity' => 'common',
                    'upgrade_gold_cost' => 0,
                    'drop_direct_to_backpack' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($artifact['stats'] as $stat) {
                    DB::table('share_item_stats')->insert([
                        'share_item_id' => $shareItemId,
                        'stat_type' => $stat['type'],
                        'value' => $stat['value'],
                        'value_type' => 'flat',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $alreadyInPool = DB::table('share_recipe_results')
                ->where('share_recipe_id', self::RECIPE_ID)
                ->where('share_item_id', $shareItemId)
                ->exists();

            if (! $alreadyInPool) {
                DB::table('share_recipe_results')->insert([
                    'share_recipe_id' => self::RECIPE_ID,
                    'share_item_id' => $shareItemId,
                    'chance' => self::POOL_CHANCE,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::ARTIFACTS as $artifact) {
            $shareItemId = DB::table('share_items')
                ->where('type', 'artifact')
                ->where('name->ru', $artifact['name']['ru'])
                ->value('id');

            if ($shareItemId === null) {
                continue;
            }

            DB::table('share_recipe_results')
                ->where('share_recipe_id', self::RECIPE_ID)
                ->where('share_item_id', $shareItemId)
                ->delete();
            DB::table('share_item_stats')->where('share_item_id', $shareItemId)->delete();
            DB::table('share_items')->where('id', $shareItemId)->delete();
        }
    }
};
