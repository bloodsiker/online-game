<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Три новых героических артефакта-подвески премиум-магазина — по прямому
 * указанию пользователя, по образцу уже существующих 927-930 («Сердце
 * Пламени», «Слеза Океана», «Искра Шторма», «Жало Топи»): та же схема
 * (type=artifact, rarity=heroic, is_sell/is_auction_sellable/is_give=0,
 * без слота — артефакты экипируются через PlayerArtifact, не share_items.slot),
 * вокабуляр статов строго из набора, уже использованного этими четырьмя
 * (critical/magic_attack/crit_damage/armor/magic_resistance/hp_max/
 * agility/dodge/magic_critical/intuition), новые сочетания троек, не
 * повторяющие ни одну из существующих. См. docs/premium-artifact-pendant-prompts.md
 * (секции «Клык Исполина», «Перо Небес», «Шип Полуночи») для описаний и
 * промптов картинок.
 *
 * В отличие от референсов требование уровня 35 — по прямому указанию
 * пользователя («можно одеть с 35 уровня») — задаётся явно через
 * share_item_requirements (type=level), у референсов такого требования нет.
 *
 * Продаются в премиум-магазине (structure_id=10), категория «Артефакты»
 * (id=2), 900 диамантов — та же цена, что и у 927-929.
 */
return new class extends Migration
{
    private const PREMIUM_SHOP_STRUCTURE_ID = 10;

    private const ARTIFACTS_CATEGORY_ID = 2;

    private const REQUIRED_LEVEL = 35;

    private const SHOP_DIAMOND_PRICE = 900;

    /**
     * @var list<array{
     *     name: array{ru: string, uk: string}, description: array{ru: string, uk: string},
     *     stats: list<array{type: string, value: int}>, sortOrder: int,
     * }>
     */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Клык Исполина', 'uk' => 'Ікло Велетня'],
            'description' => [
                'ru' => 'Окаменевший клык, вросший в оправу из тёмной бронзы, тяжелее, чем кажется на вид, — кузнецы говорят, что в нём до сих пор звучит эхо шагов горного великана, затихшего под толщей камня.',
                'uk' => 'Скам\'яніле ікло, вросле в оправу з темної бронзи, важче, ніж здається на вигляд, — ковалі кажуть, що в ньому досі лунає відлуння кроків гірського велетня, що затих під товщею каменю.',
            ],
            'stats' => [
                ['type' => 'armor', 'value' => 30],
                ['type' => 'hp_max', 'value' => 90],
                ['type' => 'crit_damage', 'value' => 20],
            ],
            'sortOrder' => 3,
        ],
        [
            'name' => ['ru' => 'Перо Небес', 'uk' => 'Перо Небес'],
            'description' => [
                'ru' => 'Единственное перо, выпавшее из крыла грозового грифона, застыло в кольце из полированного серебра и всё ещё ловит ветер, даже когда в комнате нет ни единого сквозняка.',
                'uk' => 'Єдине перо, що випало з крила грозового грифона, застигло в кільці з полірованого срібла і досі ловить вітер, навіть коли в кімнаті немає жодного протягу.',
            ],
            'stats' => [
                ['type' => 'agility', 'value' => 18],
                ['type' => 'dodge', 'value' => 22],
                ['type' => 'critical', 'value' => 18],
            ],
            'sortOrder' => 4,
        ],
        [
            'name' => ['ru' => 'Шип Полуночи', 'uk' => 'Шип Опівночі'],
            'description' => [
                'ru' => 'Чёрный терновый шип в оправе из тусклого железа растёт на долю волоса каждую ночь — никто так и не решился выяснить, что случится, когда он дорастёт до конца.',
                'uk' => 'Чорний тернистий шип в оправі з тьмяного заліза росте на частку волосини щоночі — ніхто так і не наважився з\'ясувати, що станеться, коли він доросте до кінця.',
            ],
            'stats' => [
                ['type' => 'intuition', 'value' => 18],
                ['type' => 'magic_attack', 'value' => 28],
                ['type' => 'magic_critical', 'value' => 18],
            ],
            'sortOrder' => 5,
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
                'rarity' => 'heroic',
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
