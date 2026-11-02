<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Третья ступень механики осколков (common -> uncommon -> rare, см.
 * 2026_10_18_000002_..., 2026_10_22_000001_...) - rare-редкость, египетская
 * мифология, 6 осколков -> 1 случайный арт из пула 6, через ItemAssembly.
 * Экв. уровня ~6.7-6.8 (50/65/68 общий прогресс common/uncommon/rare),
 * требование уровня 35 (common=15, uncommon=25, rare=35).
 */
return new class extends Migration
{
    private const REQUIRED_LEVEL = 35;

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, stats: list<array{type: string, value: int}>}> */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Щит Ра', 'uk' => 'Щит Ра'],
            'description' => [
                'ru' => 'Осколок солнечного диска, что каждый день отвоёвывает небо у тьмы, — держишь его перед собой, и свет сам встаёт на твою защиту.',
                'uk' => 'Уламок сонячного диска, що щодня відвойовує небо в темряви, — тримаєш його перед собою, і світло саме стає на твій захист.',
            ],
            'stats' => [
                ['type' => 'armor', 'value' => 44],
                ['type' => 'magic_resistance', 'value' => 24],
            ],
        ],
        [
            'name' => ['ru' => 'Поступь Сехмет', 'uk' => 'Хода Сехмет'],
            'description' => [
                'ru' => 'Отпечаток лапы львиноголовой богини войны — те, кто слышал этот звук наяву, уже не успевали рассказать, как он звучит.',
                'uk' => 'Відбиток лапи левоголової богині війни — ті, хто чув цей звук наяву, вже не встигали розповісти, як він звучить.',
            ],
            'stats' => [
                ['type' => 'agility', 'value' => 38],
                ['type' => 'intuition', 'value' => 29],
            ],
        ],
        [
            'name' => ['ru' => 'Весы Маат', 'uk' => 'Терези Маат'],
            'description' => [
                'ru' => 'Крошечная чаша весов, на которых взвешивают сердце против пера истины, — держит равновесие даже в руке того, чьё сердце неспокойно.',
                'uk' => 'Крихітна шалька терезів, на яких зважують серце проти пера істини, — тримає рівновагу навіть у руці того, чиє серце неспокійне.',
            ],
            'stats' => [
                ['type' => 'wisdom', 'value' => 41],
                ['type' => 'intelligence', 'value' => 27],
            ],
        ],
        [
            'name' => ['ru' => 'Пелена Осириса', 'uk' => 'Пелена Осіріса'],
            'description' => [
                'ru' => 'Полоса погребальных пелён бога, что умер и воскрес первым, — тело под ней не торопится следовать за душой, сколько бы ран на нём ни было.',
                'uk' => 'Смуга поховальних пелен бога, що помер і воскрес першим, — тіло під нею не поспішає йти за душею, скільки б ран на ньому не було.',
            ],
            'stats' => [
                ['type' => 'hp_max', 'value' => 204],
                ['type' => 'crit_damage', 'value' => 26],
            ],
        ],
        [
            'name' => ['ru' => 'Клинок Сета', 'uk' => 'Клинок Сета'],
            'description' => [
                'ru' => 'Обломок клинка бога бурь и раздора — рука, что держит его, бьёт быстрее мысли, а мысль не успевает пожалеть об ударе.',
                'uk' => 'Уламок клинка бога бур і розбрату — рука, що тримає його, б\'є швидше за думку, а думка не встигає пожалкувати про удар.',
            ],
            'stats' => [
                ['type' => 'critical', 'value' => 61],
                ['type' => 'magic_critical', 'value' => 23],
            ],
        ],
        [
            'name' => ['ru' => 'Жезл Тота', 'uk' => 'Жезл Тота'],
            'description' => [
                'ru' => 'Навершие жезла бога письма и магии — говорят, он записал первое заклинание ещё до того, как придумал буквы, которыми его можно было бы записать.',
                'uk' => 'Навершя жезла бога письма і магії — кажуть, він записав перше закляття ще до того, як вигадав літери, якими його можна було б записати.',
            ],
            'stats' => [
                ['type' => 'magic_attack', 'value' => 60],
                ['type' => 'magic_crit_damage', 'value' => 23],
            ],
        ],
    ];

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}}> */
    private const SHARDS = [
        [
            'name' => ['ru' => 'Осколок Истины', 'uk' => 'Уламок Істини'],
            'description' => [
                'ru' => 'Прозрачный осколок, через который любая ложь становится видна как сквозь треснувшее стекло.',
                'uk' => 'Прозорий уламок, крізь який будь-яка брехня стає видна, наче крізь тріснуте скло.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Жертвы', 'uk' => 'Уламок Жертви'],
            'description' => [
                'ru' => 'Тёмно-красный осколок, тяжёлый не по размеру — будто всё ещё помнит, чего стоило его отдать.',
                'uk' => 'Темно-червоний уламок, важкий не за розміром — наче досі пам\'ятає, чого коштувало його віддати.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Покоя', 'uk' => 'Уламок Спокою'],
            'description' => [
                'ru' => 'Гладкий осколок без единой трещины, в руке он почти не ощущается — будто сам хочет, чтобы его забыли.',
                'uk' => 'Гладкий уламок без жодної тріщини, у руці він майже не відчувається — наче сам хоче, щоб його забули.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Испытания', 'uk' => 'Уламок Випробування'],
            'description' => [
                'ru' => 'Шершавый осколок с острыми, необработанными краями — он не прощает небрежного обращения.',
                'uk' => 'Шорсткий уламок з гострими, необробленими краями — він не прощає недбалого поводження.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Отражения', 'uk' => 'Уламок Відображення'],
            'description' => [
                'ru' => 'Зеркальный осколок, в котором видно не своё лицо, а то, каким могло бы быть.',
                'uk' => 'Дзеркальний уламок, у якому видно не своє обличчя, а те, яким воно могло б бути.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Надежды', 'uk' => 'Уламок Надії'],
            'description' => [
                'ru' => 'Тёплый мерцающий осколок, свет в котором разгорается всякий раз, когда рядом кто-то отчаивается.',
                'uk' => 'Теплий мерехтливий уламок, світло в якому розгоряється щоразу, коли поруч хтось впадає у відчай.',
            ],
        ],
    ];

    private const COMBINE_SUFFIX_RU = ' Говорят, собрав воедино все шесть осколков, можно создать нечто куда более ценное и могущественное.';

    private const COMBINE_SUFFIX_UK = ' Кажуть, зібравши воєдино всі шість уламків, можна створити щось набагато цінніше і могутніше.';

    private const ASSEMBLY_NAME_RU = 'Сборка артефакта (rare)';

    public function up(): void
    {
        $now = now();

        $artifactIds = [];
        foreach (self::ARTIFACTS as $artifact) {
            $existingId = $this->findByName('artifact', $artifact['name']['ru']);
            if ($existingId !== null) {
                $artifactIds[] = $existingId;

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
                'rarity' => 'rare',
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

            DB::table('share_item_requirements')->insert([
                'share_item_id' => $shareItemId,
                'type' => 'level',
                'stat_key' => null,
                'skill_id' => null,
                'min_value' => self::REQUIRED_LEVEL,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $artifactIds[] = $shareItemId;
        }

        $shardIds = [];
        foreach (self::SHARDS as $shard) {
            $existingId = $this->findByName('resource', $shard['name']['ru']);
            if ($existingId !== null) {
                $shardIds[] = $existingId;

                continue;
            }

            $shardIds[] = DB::table('share_items')->insertGetId([
                'type' => 'resource',
                'name' => json_encode($shard['name'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => $shard['description']['ru'].self::COMBINE_SUFFIX_RU,
                    'uk' => $shard['description']['uk'].self::COMBINE_SUFFIX_UK,
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
                'price' => 1,
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

        $assemblyId = DB::table('item_assemblies')->where('name->ru', self::ASSEMBLY_NAME_RU)->value('id');
        if ($assemblyId === null) {
            $assemblyId = DB::table('item_assemblies')->insertGetId([
                'name' => json_encode(['ru' => self::ASSEMBLY_NAME_RU, 'uk' => 'Збірка артефакта (rare)'], JSON_UNESCAPED_UNICODE),
                'type' => 'artifact',
                'result_mode' => 'random',
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($shardIds as $shardId) {
                DB::table('item_assembly_components')->insert([
                    'item_assembly_id' => $assemblyId,
                    'share_item_id' => $shardId,
                    'count' => 1,
                ]);
            }

            foreach ($artifactIds as $artifactId) {
                DB::table('item_assembly_rewards')->insert([
                    'item_assembly_id' => $assemblyId,
                    'share_item_id' => $artifactId,
                    'count' => 1,
                    'weight' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        $assemblyId = DB::table('item_assemblies')->where('name->ru', self::ASSEMBLY_NAME_RU)->value('id');
        if ($assemblyId !== null) {
            DB::table('item_assembly_rewards')->where('item_assembly_id', $assemblyId)->delete();
            DB::table('item_assembly_components')->where('item_assembly_id', $assemblyId)->delete();
            DB::table('item_assemblies')->where('id', $assemblyId)->delete();
        }

        foreach (self::SHARDS as $shard) {
            $shardId = $this->findByName('resource', $shard['name']['ru']);
            if ($shardId !== null) {
                DB::table('share_items')->where('id', $shardId)->delete();
            }
        }

        foreach (self::ARTIFACTS as $artifact) {
            $artifactId = $this->findByName('artifact', $artifact['name']['ru']);
            if ($artifactId !== null) {
                DB::table('share_item_requirements')->where('share_item_id', $artifactId)->delete();
                DB::table('share_item_stats')->where('share_item_id', $artifactId)->delete();
                DB::table('share_items')->where('id', $artifactId)->delete();
            }
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
