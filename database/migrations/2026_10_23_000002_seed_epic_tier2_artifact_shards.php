<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Четвёртая ступень механики осколков (common -> uncommon -> rare ->
 * epic-125, см. 2026_10_18_000002_..., 2026_10_22_000001_...,
 * 2026_10_23_000001_...) - epic-редкость (НЕ путать со стартовыми 1833/1834,
 * тоже rarity=epic, но это высокоуровневый грайндовый тир, не стартовый
 * буст), славянская мифология, 7 осколков -> 1 случайный арт из пула 6,
 * через ItemAssembly. Экв. уровня 18.0 (180 конв. очков, ~14.5% бюджета),
 * требование уровня 125.
 */
return new class extends Migration
{
    private const REQUIRED_LEVEL = 125;

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, stats: list<array{type: string, value: int}>}> */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Броня Перуна', 'uk' => 'Броня Перуна'],
            'description' => [
                'ru' => 'Обломок доспеха громовержца, в который однажды ударила его же молния, — с тех пор металл гудит перед грозой и не пропускает ни клинка, ни проклятья.',
                'uk' => 'Уламок обладунку громовержця, в який одного разу вдарила його ж блискавка, — відтоді метал гуде перед грозою і не пропускає ні клинка, ні прокляття.',
            ],
            'stats' => [
                ['type' => 'armor', 'value' => 115],
                ['type' => 'magic_resistance', 'value' => 65],
            ],
        ],
        [
            'name' => ['ru' => 'Нить Мокоши', 'uk' => 'Нитка Мокоші'],
            'description' => [
                'ru' => 'Обрывок нити, которой богиня судьбы прядёт жизнь каждого — кто держит его в руке, тот на миг чувствует чужие судьбы на кончиках пальцев.',
                'uk' => 'Уривок нитки, якою богиня долі пряде життя кожного — хто тримає її в руці, той на мить відчуває чужі долі на кінчиках пальців.',
            ],
            'stats' => [
                ['type' => 'agility', 'value' => 95],
                ['type' => 'intuition', 'value' => 85],
            ],
        ],
        [
            'name' => ['ru' => 'Посох Велеса', 'uk' => 'Посох Велеса'],
            'description' => [
                'ru' => 'Навершие посоха бога мудрости, магии и подземного скота, — вырезано в форме змея, свернувшегося кольцом вокруг знания, которое не каждому стоит знать.',
                'uk' => 'Навершя посоха бога мудрості, магії та підземної худоби, — вирізане у формі змія, що згорнувся кільцем навколо знання, яке не кожному варто знати.',
            ],
            'stats' => [
                ['type' => 'wisdom', 'value' => 108],
                ['type' => 'intelligence', 'value' => 72],
            ],
        ],
        [
            'name' => ['ru' => 'Лик Световида', 'uk' => 'Лик Световида'],
            'description' => [
                'ru' => 'Одно из четырёх лиц бога-стража, смотрящего на все стороны света разом, — пока хоть одно лицо бодрствует, идол не падёт, сколько бы раз в него ни били.',
                'uk' => 'Одне з чотирьох облич бога-стража, що дивиться на всі сторони світу разом, — поки хоч одне обличчя не спить, ідол не впаде, скільки б разів у нього не влучали.',
            ],
            'stats' => [
                ['type' => 'hp_max', 'value' => 540],
                ['type' => 'crit_damage', 'value' => 68],
            ],
        ],
        [
            'name' => ['ru' => 'Клык Семаргла', 'uk' => 'Ікло Семаргла'],
            'description' => [
                'ru' => 'Обломок клыка огненного крылатого пса, стерегущего посевы и границу миров, — укус, который он наносит, прожигает не кожу, а саму удачу противника.',
                'uk' => 'Уламок ікла вогняного крилатого пса, що стереже посіви й межу світів, — укус, який він завдає, пропікає не шкіру, а саму удачу супротивника.',
            ],
            'stats' => [
                ['type' => 'critical', 'value' => 180],
                ['type' => 'magic_critical', 'value' => 68],
            ],
        ],
        [
            'name' => ['ru' => 'Вихрь Стрибога', 'uk' => 'Вихор Стрибога'],
            'description' => [
                'ru' => 'Клочок бури, вырванный из дыхания бога ветров, — заклинание, брошенное вместе с ним, летит быстрее, чем успевает развеяться.',
                'uk' => 'Клаптик бурі, вирваний з подиху бога вітрів, — закляття, кинуте разом із ним, летить швидше, ніж встигає розвіятися.',
            ],
            'stats' => [
                ['type' => 'magic_attack', 'value' => 180],
                ['type' => 'magic_crit_damage', 'value' => 68],
            ],
        ],
    ];

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}}> */
    private const SHARDS = [
        [
            'name' => ['ru' => 'Осколок Начала', 'uk' => 'Уламок Початку'],
            'description' => [
                'ru' => 'Светлый осколок, который почему-то всегда тёплый на ощупь — будто всё ещё помнит момент, когда был создан.',
                'uk' => 'Світлий уламок, який чомусь завжди теплий на дотик — наче досі пам\'ятає мить, коли був створений.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Единства', 'uk' => 'Уламок Єдності'],
            'description' => [
                'ru' => 'Осколок, который никогда не встречается в одиночку в легендах — там, где находят один, рано или поздно находят ещё.',
                'uk' => 'Уламок, який ніколи не трапляється самотужки в легендах — там, де знаходять один, рано чи пізно знаходять ще.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Безумия', 'uk' => 'Уламок Шаленства'],
            'description' => [
                'ru' => 'Беспокойный осколок, свет в котором дёргается и мечется, никогда не замирая на месте.',
                'uk' => 'Неспокійний уламок, світло в якому смикається і метається, ніколи не завмираючи на місці.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Терпения', 'uk' => 'Уламок Терпіння'],
            'description' => [
                'ru' => 'Осколок, который за века не изменился ни на волос — ни трещины, ни потускнения, только тихое, упрямое ожидание.',
                'uk' => 'Уламок, який за століття не змінився ні на волосину — ні тріщини, ні потьмяніння, тільки тихе, вперте очікування.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Равновесия', 'uk' => 'Уламок Рівноваги'],
            'description' => [
                'ru' => 'Осколок, который никогда не падает одной гранью вниз — как бы его ни бросили, он находит точку опоры.',
                'uk' => 'Уламок, який ніколи не падає однією гранню вниз — як би його не кинули, він знаходить точку опори.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Прощения', 'uk' => 'Уламок Прощення'],
            'description' => [
                'ru' => 'Тёплый осколок, который становится теплее в руках того, кто искренне о чём-то сожалеет.',
                'uk' => 'Теплий уламок, який стає теплішим у руках того, хто щиро про щось шкодує.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Воли', 'uk' => 'Уламок Волі'],
            'description' => [
                'ru' => 'Твёрдый, почти неразрушимый осколок, который не поддаётся ни огню, ни холоду, ни времени.',
                'uk' => 'Твердий, майже незруйновний уламок, який не піддається ні вогню, ні холоду, ні часу.',
            ],
        ],
    ];

    private const COMBINE_SUFFIX_RU = ' Говорят, собрав воедино все семь осколков, можно создать нечто куда более ценное и могущественное.';

    private const COMBINE_SUFFIX_UK = ' Кажуть, зібравши воєдино всі сім уламків, можна створити щось набагато цінніше і могутніше.';

    private const ASSEMBLY_NAME_RU = 'Сборка артефакта (epic-125)';

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
                'rarity' => 'epic',
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
                'rarity' => 'epic',
                'upgrade_gold_cost' => 0,
                'drop_direct_to_backpack' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $assemblyId = DB::table('item_assemblies')->where('name->ru', self::ASSEMBLY_NAME_RU)->value('id');
        if ($assemblyId === null) {
            $assemblyId = DB::table('item_assemblies')->insertGetId([
                'name' => json_encode(['ru' => self::ASSEMBLY_NAME_RU, 'uk' => 'Збірка артефакта (epic-125)'], JSON_UNESCAPED_UNICODE),
                'type' => 'artifact',
                'result_mode' => 'random',
                'is_active' => 1,
                'sort_order' => 3,
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
