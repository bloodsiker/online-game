<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Механика «осколков» для случайных артефактов обычной редкости - по
 * прямому указанию пользователя. Собираешь 4 разных осколка (дроп/покупка)
 * и объединяешь их в кузнице (Кузнечный ряд, location_id=27, structure_id=8,
 * вкладка «Крафт») в один случайный артефакт из пула четырёх (924, 925,
 * 926 + новый "Шип Ярости").
 *
 * Технически: 3 осколка - обычные ингредиенты (share_recipe_has_items),
 * 4-й осколок ("Осколок Безмолвия") сам является share_items.type=recipe -
 * это и есть "книга рецепта", которую игрок использует в Кузне, чтобы
 * скомбинировать остальные три. percent=100 - шанс случайный не в том,
 * сработает ли комбинация, а в том, какой артефакт выпадет из пула
 * (see ShareRecipe::is_random_result + share_recipe_results, добавлено в
 * 2026_10_18_000001_add_random_result_pool_to_share_recipes.php).
 */
return new class extends Migration
{
    private const RESULT_POOL_IDS = [924, 925, 926];

    private const NEW_ARTIFACT = [
        'name' => ['ru' => 'Шип Ярости', 'uk' => 'Шип Люті'],
        'description' => [
            'ru' => 'Чёрный роговой шип, снятый с хребта свирепого зверя и оправленный в тёмную сталь — держишь его в руке, и кровь сама просит боя.',
            'uk' => 'Чорний роговий шип, знятий з хребта лютого звіра й оправлений у темну сталь — тримаєш його в руці, і кров сама просить бою.',
        ],
        'stats' => [
            // 'strength' не является валидным share_item_stats.stat_type (ENUM
            // его не содержит вовсе) - предметы дают силу только через её
            // прямой эквивалент 'armor' (ARMOR_PER_STR=1), см. project memory
            // feedback_item_balance_direct_sql / docs/premium-artifact-stat-balance.md.
            ['type' => 'armor', 'value' => 4],
            ['type' => 'critical', 'value' => 4],
        ],
    ];

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, isRecipe: bool}> */
    private const SHARDS = [
        [
            'name' => ['ru' => 'Осколок Эха', 'uk' => 'Уламок Відлуння'],
            'description' => [
                'ru' => 'Тонкий звенящий осколок — если приложить его к уху, слышно отдалённое эхо чужого боя, которого никто не вёл.',
                'uk' => 'Тонкий дзвінкий уламок — якщо прикласти його до вуха, чути віддалене відлуння чужого бою, якого ніхто не вів.',
            ],
            'isRecipe' => false,
        ],
        [
            'name' => ['ru' => 'Осколок Забвения', 'uk' => 'Уламок Забуття'],
            'description' => [
                'ru' => 'Матовый тёмный осколок, холодный даже на солнце — держать его долго не хочется, будто он забирает что-то взамен тепла.',
                'uk' => 'Матовий темний уламок, холодний навіть на сонці — тримати його довго не хочеться, наче він забирає щось натомість тепла.',
            ],
            'isRecipe' => false,
        ],
        [
            'name' => ['ru' => 'Осколок Истока', 'uk' => 'Уламок Джерела'],
            'description' => [
                'ru' => 'Светлый осколок с прожилками, похожими на застывшие капли воды — тёплый на ощупь, словно всё ещё помнит источник, из которого вышел.',
                'uk' => 'Світлий уламок із прожилками, схожими на застиглі краплі води — теплий на дотик, наче й досі пам\'ятає джерело, з якого вийшов.',
            ],
            'isRecipe' => false,
        ],
        [
            'name' => ['ru' => 'Осколок Безмолвия', 'uk' => 'Уламок Безмовності'],
            'description' => [
                'ru' => 'Последний из четырёх — поднесите его к остальным трём у кузнеца, и все четыре осколка срастутся в единый артефакт. Расходуется при объединении.',
                'uk' => 'Останній з чотирьох — піднесіть його до решти трьох у коваля, і всі чотири уламки зростуться в єдиний артефакт. Витрачається під час поєднання.',
            ],
            'isRecipe' => true,
        ],
    ];

    public function up(): void
    {
        $now = now();

        $newArtifactId = $this->findByName('artifact', self::NEW_ARTIFACT['name']['ru']);
        if ($newArtifactId === null) {
            $newArtifactId = DB::table('share_items')->insertGetId([
                'type' => 'artifact',
                'name' => json_encode(self::NEW_ARTIFACT['name'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(self::NEW_ARTIFACT['description'], JSON_UNESCAPED_UNICODE),
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

            foreach (self::NEW_ARTIFACT['stats'] as $stat) {
                DB::table('share_item_stats')->insert([
                    'share_item_id' => $newArtifactId,
                    'stat_type' => $stat['type'],
                    'value' => $stat['value'],
                    'value_type' => 'flat',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $shardIds = [];
        foreach (self::SHARDS as $shard) {
            $shardId = $this->findByName($shard['isRecipe'] ? 'recipe' : 'resource', $shard['name']['ru']);

            if ($shardId === null) {
                $shardId = DB::table('share_items')->insertGetId([
                    'type' => $shard['isRecipe'] ? 'recipe' : 'resource',
                    'name' => json_encode($shard['name'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode($shard['description'], JSON_UNESCAPED_UNICODE),
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
                    'is_weight' => $shard['isRecipe'] ? 1 : 0,
                    'price' => 1,
                    'diamond_price' => 0,
                    'break_crystal' => 0,
                    'gathering_speed_bonus_percent' => 0,
                    'gathering_double_chance_percent' => 0,
                    'rarity' => 'common',
                    'upgrade_gold_cost' => 0,
                    'drop_direct_to_backpack' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $shardIds[$shard['isRecipe'] ? 'recipe' : 'resource_'.count($shardIds)] = $shardId;
        }

        $recipeShardId = $shardIds['recipe'];
        $ingredientShardIds = array_values(array_filter(
            $shardIds,
            fn ($id, $key) => $key !== 'recipe',
            ARRAY_FILTER_USE_BOTH,
        ));

        $recipeId = DB::table('share_recipes')->where('share_item_id', $recipeShardId)->value('id');
        if ($recipeId === null) {
            $recipeId = DB::table('share_recipes')->insertGetId([
                'share_item_id' => $recipeShardId,
                'kraft_item_id' => null,
                'is_random_result' => 1,
                'percent' => 100,
                'unlock_type' => 'single_use',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($ingredientShardIds as $ingredientId) {
                DB::table('share_recipe_has_items')->insert([
                    'share_recipe_id' => $recipeId,
                    'share_item_id' => $ingredientId,
                    'count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ([...self::RESULT_POOL_IDS, $newArtifactId] as $resultItemId) {
                DB::table('share_recipe_results')->insert([
                    'share_recipe_id' => $recipeId,
                    'share_item_id' => $resultItemId,
                    'chance' => 25,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $recipeShardId = $this->findByName('recipe', self::SHARDS[3]['name']['ru']);
        if ($recipeShardId !== null) {
            $recipeId = DB::table('share_recipes')->where('share_item_id', $recipeShardId)->value('id');
            if ($recipeId !== null) {
                DB::table('share_recipe_results')->where('share_recipe_id', $recipeId)->delete();
                DB::table('share_recipe_has_items')->where('share_recipe_id', $recipeId)->delete();
                DB::table('share_recipes')->where('id', $recipeId)->delete();
            }
        }

        foreach (self::SHARDS as $shard) {
            $shardId = $this->findByName($shard['isRecipe'] ? 'recipe' : 'resource', $shard['name']['ru']);
            if ($shardId !== null) {
                DB::table('share_items')->where('id', $shardId)->delete();
            }
        }

        $newArtifactId = $this->findByName('artifact', self::NEW_ARTIFACT['name']['ru']);
        if ($newArtifactId !== null) {
            DB::table('share_item_stats')->where('share_item_id', $newArtifactId)->delete();
            DB::table('share_items')->where('id', $newArtifactId)->delete();
        }
    }

    private function findByName(string $type, string $nameRu): ?int
    {
        return DB::table('share_items')
            ->where('type', $type)
            ->where('name->ru', $nameRu)
            ->value('id');
    }
};
