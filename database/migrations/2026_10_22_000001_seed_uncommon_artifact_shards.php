<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Вторая ступень механики осколков (после common-тира, см.
 * 2026_10_18_000002_seed_common_artifact_shards.php) - uncommon-редкость,
 * скандинавская мифология, сборка через ItemAssembly (не ShareRecipe -
 * тот подход заменили отдельным модулем ItemAssembly). 5 осколков (не 4,
 * как в common) -> 1 случайный арт из пула 6, по прямому указанию
 * пользователя. Экв. уровня ~5 (50 конвертируемых очков) - шаг вверх от
 * common (~2.5), требование уровня 25 (common - 15).
 */
return new class extends Migration
{
    private const REQUIRED_LEVEL = 25;

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, stats: list<array{type: string, value: int}>}> */
    private const ARTIFACTS = [
        [
            'name' => ['ru' => 'Кожа Йормунганда', 'uk' => 'Шкіра Йормунганда'],
            'description' => [
                'ru' => 'Клочок чешуи мирового змея, обвившего Мидгард, — на ощупь холоднее океана и тяжелее камня, но под ней тело не берёт ни клинок, ни яд.',
                'uk' => 'Клаптик луски світового змія, що обвив Мідгард, — на дотик холодніша за океан і важча за камінь, але під нею тіло не бере ні клинок, ні отрута.',
            ],
            'stats' => [
                ['type' => 'armor', 'value' => 32],
                ['type' => 'magic_resistance', 'value' => 19],
            ],
        ],
        [
            'name' => ['ru' => 'Крыло Валькирии', 'uk' => 'Крило Валькірії'],
            'description' => [
                'ru' => 'Перо из крыла девы, выбирающей павших для Вальгаллы, — лёгкое, как последний вздох, и безошибочное, как её взгляд над полем боя.',
                'uk' => 'Перо з крила діви, що обирає полеглих для Вальгалли, — легке, як останній подих, і безпомильне, як її погляд над полем бою.',
            ],
            'stats' => [
                ['type' => 'agility', 'value' => 21],
                ['type' => 'intuition', 'value' => 27],
            ],
        ],
        [
            'name' => ['ru' => 'Око Одина', 'uk' => 'Око Одіна'],
            'description' => [
                'ru' => 'Отец богов отдал свой глаз источнику Мимира за глоток мудрости — говорят, с тех пор каждый, кто держит это око в руке, видит чуть дальше, чем положено смертному.',
                'uk' => 'Батько богів віддав своє око джерелу Мімира за ковток мудрості — кажуть, відтоді кожен, хто тримає це око в руці, бачить трохи далі, ніж належить смертному.',
            ],
            'stats' => [
                ['type' => 'intelligence', 'value' => 35],
                ['type' => 'wisdom', 'value' => 14],
            ],
        ],
        [
            'name' => ['ru' => 'Панцирь Титана', 'uk' => 'Панцир Титана'],
            'description' => [
                'ru' => 'Осколок брони древнего титана, низвергнутого ещё до начала времён, — выдерживал удары богов и до сих пор не прогнулся ни разу.',
                'uk' => 'Уламок броні стародавнього титана, повергнутого ще до початку часів, — витримував удари богів і досі не прогнувся жодного разу.',
            ],
            'stats' => [
                ['type' => 'hp_max', 'value' => 141],
                ['type' => 'crit_damage', 'value' => 18],
            ],
        ],
        [
            'name' => ['ru' => 'Хватка Фенрира', 'uk' => 'Хватка Фенріра'],
            'description' => [
                'ru' => 'Обломок клыка великого волка, что в день Рагнарёка сомкнёт челюсти на Одине, — даже сейчас, скованный цепью Глейпнир, он не забыл вкус победы.',
                'uk' => 'Уламок ікла великого вовка, що в день Рагнарьоку зімкне щелепи на Одіні, — навіть зараз, скутий ланцюгом Ґлейпнір, він не забув смак перемоги.',
            ],
            'stats' => [
                ['type' => 'critical', 'value' => 44],
                ['type' => 'magic_critical', 'value' => 16],
            ],
        ],
        [
            'name' => ['ru' => 'Гнев Сурта', 'uk' => 'Гнів Сурта'],
            'description' => [
                'ru' => 'Искра из огненного меча великана, что сожжёт мир в последней битве, — обычное пламя гаснет рядом с ней, будто стыдится своей слабости.',
                'uk' => 'Іскра з вогняного меча велетня, що спалить світ в останній битві, — звичайне полум\'я гасне поруч із нею, наче соромиться своєї слабкості.',
            ],
            'stats' => [
                ['type' => 'magic_attack', 'value' => 39],
                ['type' => 'magic_crit_damage', 'value' => 21],
            ],
        ],
    ];

    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}}> */
    private const SHARDS = [
        [
            'name' => ['ru' => 'Осколок Грёзы', 'uk' => 'Уламок Марення'],
            'description' => [
                'ru' => 'Полупрозрачный осколок, в глубине которого мерцают обрывки чужих снов — иногда среди них мелькает что-то до боли знакомое.',
                'uk' => 'Напівпрозорий уламок, у глибині якого мерехтять уривки чужих снів — іноді серед них майне щось до болю знайоме.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Угасания', 'uk' => 'Уламок Згасання'],
            'description' => [
                'ru' => 'Тусклый осколок, свет в котором медленно гаснет с каждым днём — но полностью не исчезает уже много лет.',
                'uk' => 'Тьмяний уламок, світло в якому повільно згасає з кожним днем — та повністю не зникає вже багато років.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Предела', 'uk' => 'Уламок Межі'],
            'description' => [
                'ru' => 'Холодный гладкий осколок с острой гранью, будто обрезанной по линии, которую нельзя пересечь.',
                'uk' => 'Холодний гладкий уламок з гострою гранню, наче обрізаною по лінії, яку не можна перетнути.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Полумрака', 'uk' => 'Уламок Сутіні'],
            'description' => [
                'ru' => 'Серый осколок, в котором одновременно живут и свет, и тень, не смешиваясь друг с другом.',
                'uk' => 'Сірий уламок, у якому одночасно живуть і світло, і тінь, не змішуючись одне з одним.',
            ],
        ],
        [
            'name' => ['ru' => 'Осколок Клятвы', 'uk' => 'Уламок Клятви'],
            'description' => [
                'ru' => 'Тёплый осколок, будто всё ещё хранящий чьё-то обещание.',
                'uk' => 'Теплий уламок, наче досі зберігає чиюсь обіцянку.',
            ],
        ],
    ];

    private const COMBINE_SUFFIX_RU = ' Говорят, собрав воедино все пять осколков, можно создать нечто куда более ценное и могущественное.';

    private const COMBINE_SUFFIX_UK = ' Кажуть, зібравши воєдино всі п\'ять уламків, можна створити щось набагато цінніше і могутніше.';

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
                'rarity' => 'uncommon',
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
                'rarity' => 'uncommon',
                'upgrade_gold_cost' => 0,
                'drop_direct_to_backpack' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $assemblyId = DB::table('item_assemblies')->where('legacy_recipe_id', null)->where('name->ru', 'Сборка артефакта (uncommon)')->value('id');
        if ($assemblyId === null) {
            $assemblyId = DB::table('item_assemblies')->insertGetId([
                'name' => json_encode(['ru' => 'Сборка артефакта (uncommon)', 'uk' => 'Збірка артефакта (uncommon)'], JSON_UNESCAPED_UNICODE),
                'type' => 'artifact',
                'result_mode' => 'random',
                'is_active' => 1,
                'sort_order' => 1,
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
        $assemblyId = DB::table('item_assemblies')->where('name->ru', 'Сборка артефакта (uncommon)')->value('id');
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
