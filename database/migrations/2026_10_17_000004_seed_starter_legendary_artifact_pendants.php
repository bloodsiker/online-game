<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Два стартовых артефакта-подвески премиум-магазина для игроков с 1 уровня —
 * по прямому указанию пользователя. Легендарная редкость (на тон ниже
 * геройской — ту носят эндгейм-подвески 927-930, см.
 * 2026_10_17_000003_rebalance_original_artifact_pendants.php).
 *
 * Идея буста: плоские (не процентные) бонусы, подобранные так, чтобы
 * ощущаться огромными на фоне крошечных стартовых характеристик (стартовое
 * оружие 1 уровня даёт attack_min/max 3-5, базовый HP без выносливости на
 * 1 уровне — 10, см. MonsterStatFormulas::referencePlayerHp()), но
 * естественно теряющие значимость к позднему левелингу — отдельное
 * ограничение по уровню сверху не нужно, бюджет сам себя балансирует.
 * См. docs/premium-artifact-pendant-prompts.md, секция «Стартовые
 * артефакты», для описаний и промптов картинок.
 *
 * Цена ниже, чем у героических эндгейм-подвесок (900 диамантов) — товар
 * нацелен на новых игроков с небольшим запасом премиум-валюты.
 */
return new class extends Migration
{
    private const PREMIUM_SHOP_STRUCTURE_ID = 10;

    private const ARTIFACTS_CATEGORY_ID = 2;

    private const REQUIRED_LEVEL = 1;

    private const SHOP_DIAMOND_PRICE = 350;

    /**
     * @var list<array{
     *     name: array{ru: string, uk: string}, description: array{ru: string, uk: string},
     *     stats: list<array{type: string, value: int}>, sortOrder: int,
     * }>
     */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Благословение Новичка', 'uk' => 'Благословення Новачка'],
            'description' => [
                'ru' => 'Простой оберег, что вручают каждому, кто впервые переступает порог таверны, — говорят, удача странника живёт в нём до тех пор, пока он сам не станет легендой.',
                'uk' => 'Простий оберіг, що вручають кожному, хто вперше переступає поріг таверни, — кажуть, удача мандрівника живе в ньому доти, доки він сам не стане легендою.',
            ],
            'stats' => [
                ['type' => 'attack_min', 'value' => 5],
                ['type' => 'attack_max', 'value' => 8],
                ['type' => 'critical', 'value' => 10],
            ],
            'sortOrder' => 6,
        ],
        [
            'name' => ['ru' => 'Талисман Первых Шагов', 'uk' => 'Талісман Перших Кроків'],
            'description' => [
                'ru' => 'Потёртый амулет из неприметного зелёного камня — ничем не примечателен на вид, но почему-то именно с ним даже самый слабый путник доходит туда, куда другие не добираются.',
                'uk' => 'Потертий амулет з непримітного зеленого каменю — нічим не примітний на вигляд, але чомусь саме з ним навіть найслабший мандрівник доходить туди, куди інші не добираються.',
            ],
            'stats' => [
                ['type' => 'hp_max', 'value' => 40],
                ['type' => 'bag_slot', 'value' => 3],
            ],
            'sortOrder' => 7,
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::ARTIFACTS as $artifact) {
            $existingId = DB::table('share_items')
                ->where('type', 'artifact')
                ->where('name->ru', $artifact['name']['ru'])
                ->value('id');

            if ($existingId !== null) {
                continue;
            }

            $shareItemId = DB::table('share_items')->insertGetId([
                'type' => 'artifact',
                'name' => json_encode($artifact['name'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($artifact['description'], JSON_UNESCAPED_UNICODE),
                'image' => null,
                'is_two_hand' => 0,
                'count_use' => 0,
                'is_active' => 1,
                'is_sell' => 0,
                'is_auction_sellable' => 0,
                'is_give' => 0,
                'is_clan_warehouse_allowed' => 0,
                'is_droppable' => 1,
                'is_stackable' => 1,
                'is_slot_usable' => 0,
                'is_weight' => 0,
                'price' => 0,
                'break_crystal' => 0,
                'rarity' => 'legendary',
                'upgrade_gold_cost' => 0,
                'gathering_speed_bonus_percent' => 0,
                'gathering_double_chance_percent' => 0,
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

            DB::table('share_item_requirements')->insert([
                'share_item_id' => $shareItemId,
                'type' => 'level',
                'stat_key' => null,
                'skill_id' => null,
                'min_value' => self::REQUIRED_LEVEL,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('shop_items')->insert([
                'structure_id' => self::PREMIUM_SHOP_STRUCTURE_ID,
                'share_item_id' => $shareItemId,
                'share_structure_category_id' => self::ARTIFACTS_CATEGORY_ID,
                'price' => 0,
                'diamond' => self::SHOP_DIAMOND_PRICE,
                'sort_order' => $artifact['sortOrder'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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

            DB::table('shop_items')->where('share_item_id', $shareItemId)->delete();
            DB::table('share_item_requirements')->where('share_item_id', $shareItemId)->delete();
            DB::table('share_item_stats')->where('share_item_id', $shareItemId)->delete();
            DB::table('share_items')->where('id', $shareItemId)->delete();
        }
    }
};
