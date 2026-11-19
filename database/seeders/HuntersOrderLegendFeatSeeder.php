<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Quest\Domain\Services\QuestDefinitionsCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Подвиг «Легенды» (тир 5000+) Ордена Охотников — «Тень Прародителя», ≈ 11–15 дней.
 *
 * 1. «Испытание когтем»   — 16 когтей горного грифона (5%).
 * 2. «Эликсир Чутья»      — принести ресурсы → ⏳ 2 дня варки → 3 «Эликсира Чутья Прародителя».
 * 3. «Тень Прародителя»   — под эликсиром 8 «Следов Прародителя» (1,5%) с монстров Перевала
 *                            → ⏳ 3 дня следопыты идут по следу (+1 эликсир) → убить босса,
 *                            который виден только под действием эликсира.
 *
 * Эликсир: 2 часа, пить можно 1 раз в сутки; ещё один можно купить у Вульфгара (1 в сутки).
 * Идемпотентен: всё ищется по slug / русскому названию.
 */
class HuntersOrderLegendFeatSeeder extends Seeder
{
    private const REPUTATION_ID = 2;

    private const NPC_ID = 4;

    private const FEAT_MIN_POINTS = 5000;

    private const BOSS_LOCATION_ID = 1529; // Гранитный Перевал — «Площадка Расколотого Карниза»

    private const GRIFFINS = [229, 230, 249];

    /** Монстры Гранитного Перевала ур. 51–59: ледяные волки, гранитные големы, горные грифоны. */
    private const PASS_MONSTERS = [231, 232, 247, 223, 224, 248, 229, 230, 249];

    private const ELIXIR_TEMPLATE_ID = 997; // «Кровавый эликсир Братства» — образец зелья-баффа

    private const DAY = 86400;

    public function run(): void
    {
        DB::transaction(function (): void {
            $effectId = $this->upsertEffect();
            $elixirId = $this->upsertElixir($effectId);
            $clawId = $this->upsertQuestItem(
                ['Коготь горного грифона', 'Кіготь гірського грифона'],
                ['Острый как клинок коготь горного грифона. Трофей Ордена Охотников.', 'Гострий, як клинок, кіготь гірського грифона. Трофей Ордену Мисливців.'],
                'rare',
            );
            $trailId = $this->upsertQuestItem(
                ['След Прародителя', 'Слід Прародителя'],
                ['Клок серой шерсти и отпечаток огромной лапы, видимые лишь под действием Эликсира Чутья Прародителя. Нужен Вульфгару, чтобы выследить Тень.', 'Жмут сірої шерсті та відбиток величезної лапи, видимі лише під дією Еліксиру Чуття Прародителя. Потрібен Вульфгару, щоб вистежити Тінь.'],
                'epic',
            );
            $bossId = $this->upsertBoss($effectId);
            $this->upsertShopItem($elixirId);

            $q1 = $this->upsertQuest(
                titles: ['Подвиг: Испытание когтем', 'Подвиг: Випробування кігтем'],
                oldTitles: ['Подвиг: Испытание чешуёй'],
                desc: ['Первое испытание Легенды. Вульфгар хочет убедиться, что вы способны добыть самого опасного зверя Перевала: принесите 16 когтей горного грифона.', 'Перше випробування Легенди. Вульфгар хоче переконатися, що ви здатні вполювати найнебезпечнішого звіра Перевалу: принесіть 16 кігтів гірського грифона.'],
                afterQuestId: null,
                stages: [[
                    'title' => ['Испытание когтем', 'Випробування кігтем'],
                    'desc' => ['Добудьте 16 когтей горного грифона.', 'Добудьте 16 кігтів гірського грифона.'],
                    'objs' => [[
                        'type' => 'collect', 'target_type' => 'monster', 'targets' => self::GRIFFINS, 'item' => $clawId,
                        'amount' => 16, 'chance' => 5.0, 'effect' => null,
                        'desc' => ['Добыть 16 когтей горного грифона', 'Добути 16 кігтів гірського грифона'],
                    ]],
                ]],
                rewards: [300, 12000, 24000],
            );

            $q2 = $this->upsertQuest(
                titles: ['Подвиг: Эликсир Чутья', 'Подвиг: Еліксир Чуття'],
                oldTitles: ['Подвиг: Испытание камнем', 'Подвиг: Истребитель властелинов'],
                desc: ['Когти грифона — основа старинного охотничьего эликсира. Под его действием охотник чует то, чего не видят другие: следы Прародителя всех хищников. Принесите Вульфгару всё нужное для варки.', 'Кігті грифона — основа старовинного мисливського еліксиру. Під його дією мисливець чує те, чого не бачать інші: сліди Прародителя всіх хижаків. Принесіть Вульфгару все потрібне для варіння.'],
                afterQuestId: $q1,
                stages: [
                    [
                        'title' => ['Снаряжение охоты', 'Спорядження полювання'],
                        'desc' => ['Принесите Вульфгару ресурсы для эликсира. Взять задание можно, только имея их при себе.', 'Принесіть Вульфгару ресурси для еліксиру. Взяти завдання можна, лише маючи їх при собі.'],
                        'objs' => [
                            ['type' => 'collect', 'target_type' => 'item', 'targets' => [], 'item' => 912, 'amount' => 20, 'chance' => null, 'effect' => null,
                                'desc' => ['Принести 20 «Доска огненного клёна»', 'Принести 20 «Дошка вогняного клена»']],
                            ['type' => 'collect', 'target_type' => 'item', 'targets' => [], 'item' => 856, 'amount' => 10, 'chance' => null, 'effect' => null,
                                'desc' => ['Принести 10 «Настойка лунного цветка»', 'Принести 10 «Настоянка місячної квітки»']],
                            ['type' => 'collect', 'target_type' => 'item', 'targets' => [], 'item' => 712, 'amount' => 10, 'chance' => null, 'effect' => null,
                                'desc' => ['Принести 10 «Коготь теневого хищника»', 'Принести 10 «Кіготь тіньового хижака»']],
                        ],
                    ],
                    [
                        'title' => ['Варка эликсира', 'Варіння еліксиру'],
                        'desc' => ['Вульфгар варит Эликсир Чутья Прародителя. Возвращайтесь через 2 дня.', 'Вульфгар варить Еліксир Чуття Прародителя. Повертайтеся через 2 дні.'],
                        'wait' => 2 * self::DAY,
                        'waiting' => ['Котёл ещё булькает. Эликсир Чутья не терпит спешки — возвращайся позже.', 'Казан ще булькає. Еліксир Чуття не терпить поспіху — повертайся пізніше.'],
                        'ready' => ['Эликсир готов. Три склянки — береги их: пить больше одной в сутки нельзя.', 'Еліксир готовий. Три склянки — бережи їх: пити більше однієї на добу не можна.'],
                        'grant' => [$elixirId, 3],
                        'objs' => [],
                    ],
                ],
                rewards: [500, 20000, 40000],
            );

            $q3 = $this->upsertQuest(
                titles: ['Подвиг: Тень Прародителя', 'Подвиг: Тінь Прародителя'],
                oldTitles: ['Подвиг: Печать Бездны', 'Подвиг: Тёмная эссенция'],
                desc: ['Среди скал Гранитного Перевала бродит Тень Прародителя — древний зверь, которого не видит обычный глаз. Выпейте Эликсир Чутья, соберите его следы и выследите Тень.', 'Серед скель Гранітного Перевалу блукає Тінь Прародителя — прадавній звір, якого не бачить звичайне око. Випийте Еліксир Чуття, зберіть його сліди й вистежте Тінь.'],
                afterQuestId: $q2,
                stages: [
                    [
                        'title' => ['Кровавый след', 'Кривавий слід'],
                        'desc' => ['Под действием Эликсира Чутья Прародителя добудьте 8 Следов Прародителя с волков, големов и грифонов Гранитного Перевала.', 'Під дією Еліксиру Чуття Прародителя добудьте 8 Слідів Прародителя з вовків, големів і грифонів Гранітного Перевалу.'],
                        'objs' => [[
                            'type' => 'collect', 'target_type' => 'monster', 'targets' => self::PASS_MONSTERS, 'item' => $trailId,
                            'amount' => 8, 'chance' => 1.5, 'effect' => $effectId,
                            'desc' => ['Добыть 8 Следов Прародителя (только под Эликсиром Чутья)', 'Добути 8 Слідів Прародителя (лише під Еліксиром Чуття)'],
                        ]],
                    ],
                    [
                        'title' => ['По следу', 'По сліду'],
                        'desc' => ['Следопыты Ордена идут по следу Тени. Возвращайтесь через 3 дня.', 'Слідопити Ордену йдуть по сліду Тіні. Повертайтеся через 3 дні.'],
                        'wait' => 3 * self::DAY,
                        'waiting' => ['Следопыты ещё не вернулись. Тень хитра — жди вестей.', 'Слідопити ще не повернулися. Тінь хитра — чекай звісток.'],
                        'ready' => ['Логово найдено: Площадка Расколотого Карниза на Гранитном Перевале. Выпей эликсир — без него Тень не увидеть.', 'Лігво знайдено: Майданчик Розколотого Карнизу на Гранітному Перевалі. Випий еліксир — без нього Тінь не побачити.'],
                        'grant' => [$elixirId, 1],
                        'objs' => [],
                    ],
                    [
                        'title' => ['Логово Прародителя', 'Лігво Прародителя'],
                        'desc' => ['Под действием Эликсира Чутья найдите Тень Прародителя на Площадке Расколотого Карниза и победите её.', 'Під дією Еліксиру Чуття знайдіть Тінь Прародителя на Майданчику Розколотого Карнизу та переможіть її.'],
                        'objs' => [[
                            'type' => 'kill', 'target_type' => 'monster', 'targets' => [$bossId], 'item' => null,
                            'amount' => 1, 'chance' => null, 'effect' => null,
                            'desc' => ['Победить Тень Прародителя', 'Перемогти Тінь Прародителя'],
                        ]],
                    ],
                ],
                rewards: [1500, 40000, 100000],
            );

            DB::table('reputation_tiers')
                ->where('reputation_id', self::REPUTATION_ID)
                ->where('min_points', self::FEAT_MIN_POINTS)
                ->update([
                    'feat_quest_id' => $q3,
                    'feat_description_legacy' => 'Выследите и победите Тень Прародителя: когти грифонов, Эликсир Чутья, следы под эликсиром и бой в логове.',
                    'feat_description' => $this->json([
                        'Выследите и победите Тень Прародителя: когти грифонов, Эликсир Чутья, следы под эликсиром и бой в логове.',
                        'Вистежте й переможіть Тінь Прародителя: кігті грифонів, Еліксир Чуття, сліди під еліксиром і бій у лігві.',
                    ]),
                    'updated_at' => now(),
                ]);
        });

        QuestDefinitionsCache::flush();
    }

    private function upsertEffect(): int
    {
        $row = [
            'name_legacy' => 'Чутьё Прародителя',
            'name' => $this->json(['Чутьё Прародителя', 'Чуття Прародителя']),
            'description_legacy' => 'Вы чуете следы Прародителя и видите его Тень. +5% к атаке на 2 часа.',
            'description' => $this->json([
                'Вы чуете следы Прародителя и видите его Тень. +5% к атаке на 2 часа.',
                'Ви чуєте сліди Прародителя й бачите його Тінь. +5% до атаки на 2 години.',
            ]),
            'type' => 'buff',
            'image' => DB::table('effects')->where('slug', 'blood_surge')->value('image'),
            'chance' => 0,
            'is_stackable' => 0,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'stat_modifiers' => json_encode([['type' => 'attack', 'value' => 5, 'is_percent' => true]]),
            'is_dispellable' => 0,
            'updated_at' => now(),
        ];

        $id = DB::table('effects')->where('slug', 'progenitor_scent')->value('id');
        if ($id === null) {
            return (int) DB::table('effects')->insertGetId($row + ['slug' => 'progenitor_scent', 'created_at' => now()]);
        }

        unset($row['image']); // картинку эффекта, загруженную в админке, не перетираем
        DB::table('effects')->where('id', $id)->update($row);

        return (int) $id;
    }

    private function upsertElixir(int $effectId): int
    {
        $ru = 'Эликсир Чутья Прародителя';
        $descRu = 'Горький отвар на когтях горного грифона. На 2 часа обостряет чутьё: вы видите <b>Следы Прародителя</b> на монстрах Гранитного Перевала и саму <b>Тень Прародителя</b>.<br><br><b>Примечание:</b> пить можно не чаще 1 раза в сутки.';
        $descUk = 'Гіркий відвар на кігтях гірського грифона. На 2 години загострює чуття: ви бачите <b>Сліди Прародителя</b> на монстрах Гранітного Перевалу і саму <b>Тінь Прародителя</b>.<br><br><b>Примітка:</b> пити можна не частіше 1 разу на добу.';

        $row = [
            'name_legacy' => $ru,
            'name' => $this->json([$ru, 'Еліксир Чуття Прародителя']),
            'description_legacy' => $descRu,
            'description' => $this->json([$descRu, $descUk]),
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'is_give' => 0,
            'is_clan_warehouse_allowed' => 0,
            'price' => 1,
            'break_crystal' => 0,
            'rarity' => 'epic',
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('name->ru', $ru)->value('id');
        if ($id === null) {
            $template = (array) DB::table('share_items')->where('id', self::ELIXIR_TEMPLATE_ID)->first();
            unset($template['id']);
            $id = (int) DB::table('share_items')->insertGetId(array_merge($template, $row, [
                'image' => null,
                'transparent_image' => null,
                'created_at' => now(),
            ]));
        } else {
            $id = (int) $id;
            DB::table('share_items')->where('id', $id)->update($row);
        }

        DB::table('share_item_buffs')->updateOrInsert(
            ['share_item_id' => $id],
            ['effect_id' => $effectId, 'duration_seconds' => 2 * 3600, 'reapply_policy' => 'block', 'created_at' => now(), 'updated_at' => now()],
        );
        DB::table('share_item_use_limits')->updateOrInsert(
            ['share_item_id' => $id],
            ['max_uses' => 1, 'period_seconds' => self::DAY, 'created_at' => now(), 'updated_at' => now()],
        );

        return $id;
    }

    /**
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     */
    private function upsertQuestItem(array $name, array $desc, string $rarity): int
    {
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'is_give' => 0,
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'rarity' => $rarity,
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('type', 'quest')->where('name->ru', $name[0])->value('id');
        if ($id !== null) {
            DB::table('share_items')->where('id', $id)->update($row);

            return (int) $id;
        }

        return (int) DB::table('share_items')->insertGetId($row + [
            'type' => 'quest',
            'image' => null,
            'is_active' => 1,
            'is_droppable' => 1,
            'is_stackable' => 1,
            'is_use' => 0,
            'is_weight' => 1,
            'price' => 1,
            'created_at' => now(),
        ]);
    }

    private function upsertBoss(int $effectId): int
    {
        $ru = 'Тень Прародителя';
        $descRu = 'Древний зверь, от которого пошли все хищники Долины. Огромная седая тень с горящими глазами — увидеть её может лишь тот, кто выпил Эликсир Чутья Прародителя.';
        $descUk = 'Прадавній звір, від якого пішли всі хижаки Долини. Величезна сива тінь із палаючими очима — побачити її може лише той, хто випив Еліксир Чуття Прародителя.';

        $row = [
            'name_legacy' => $ru,
            'name' => $this->json([$ru, 'Тінь Прародителя']),
            'description_legacy' => $descRu,
            'description' => $this->json([$descRu, $descUk]),
            'lvl' => 60,
            'hp' => 12000,
            'armor' => 360,
            'dodge' => 25,
            'critical' => 40,
            'min_dmg' => 300,
            'max_dmg' => 480,
            'attack_type' => 'physical',
            'attack_style' => 'bite',
            'magic_resistance' => 360,
            'aggression' => 0,
            'exp' => 50000,
            'min_money' => 100000,
            'max_money' => 200000,
            'is_boss' => 1,
            'does_not_regenerate_hp' => 1,
            'respawn_min_minutes' => 360,
            'respawn_max_minutes' => 480,
            'required_active_effect_id' => $effectId,
            'updated_at' => now(),
        ];

        $id = DB::table('monsters')->where('name->ru', $ru)->value('id');
        if ($id === null) {
            $id = (int) DB::table('monsters')->insertGetId($row + ['image' => null, 'created_at' => now()]);
        } else {
            $id = (int) $id;
            DB::table('monsters')->where('id', $id)->update($row);
        }

        DB::table('location_has_monsters')->updateOrInsert(
            ['location_id' => self::BOSS_LOCATION_ID, 'monster_id' => $id],
            ['aggression' => 0],
        );

        return $id;
    }

    private function upsertShopItem(int $elixirId): void
    {
        DB::table('reputation_shop_items')->updateOrInsert(
            ['reputation_id' => self::REPUTATION_ID, 'share_item_id' => $elixirId],
            [
                'share_structure_category_id' => 4, // Эликсиры
                'price' => 25000,
                'diamond' => 0,
                'min_points' => self::FEAT_MIN_POINTS,
                'sort_order' => 10,
                'purchase_limit_quantity' => 1,
                'purchase_limit_period_seconds' => self::DAY,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * @param  array{0: string, 1: string}  $titles
     * @param  list<string>  $oldTitles
     * @param  array{0: string, 1: string}  $desc
     * @param  list<array<string, mixed>>  $stages
     * @param  array{0: int, 1: int, 2: int}  $rewards
     */
    private function upsertQuest(array $titles, array $oldTitles, array $desc, ?int $afterQuestId, array $stages, array $rewards): int
    {
        $questId = null;
        foreach ([$titles[0], ...$oldTitles] as $title) {
            $questId = DB::table('quests')->where('title->ru', '[Орден] '.$title)->value('id');
            if ($questId !== null) {
                break;
            }
        }

        $row = [
            'title_legacy' => '[Орден] '.$titles[0],
            'title' => $this->json(['[Орден] '.$titles[0], '[Орден] '.$titles[1]]),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'type' => 'reputation',
            'start_npc_id' => self::NPC_ID,
            'complete_npc_id' => self::NPC_ID,
            'after_quest_id' => $afterQuestId,
            'is_active' => 1,
            'updated_at' => now(),
        ];

        if ($questId === null) {
            $questId = (int) DB::table('quests')->insertGetId($row + ['created_at' => now()]);
        } else {
            $questId = (int) $questId;
            DB::table('quests')->where('id', $questId)->update($row);
        }

        DB::table('quest_objectives')->where('quest_id', $questId)->delete();
        DB::table('quest_stages')->where('quest_id', $questId)->delete();

        $useStages = count($stages) > 1;
        $objectiveIds = [];
        $firstStageId = null;

        foreach (array_values($stages) as $index => $stage) {
            $stageId = null;
            if ($useStages) {
                $isWait = isset($stage['wait']);
                $stageId = (int) DB::table('quest_stages')->insertGetId([
                    'quest_id' => $questId,
                    'complete_npc_id' => self::NPC_ID,
                    'order' => $index + 1,
                    'title_legacy' => $stage['title'][0],
                    'title' => $this->json($stage['title']),
                    'description_legacy' => $stage['desc'][0],
                    'description' => $this->json($stage['desc']),
                    'stage_type' => $isWait ? 'wait' : 'action',
                    'wait_duration_seconds' => $stage['wait'] ?? null,
                    'waiting_text_legacy' => $stage['waiting'][0] ?? null,
                    'waiting_text' => isset($stage['waiting']) ? $this->json($stage['waiting']) : null,
                    'ready_text_legacy' => $stage['ready'][0] ?? null,
                    'ready_text' => isset($stage['ready']) ? $this->json($stage['ready']) : null,
                    'grant_share_item_id' => $stage['grant'][0] ?? null,
                    'grant_amount' => $stage['grant'][1] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $firstStageId ??= $stageId;
            }

            foreach ($stage['objs'] as $obj) {
                $isItemGate = $obj['target_type'] === 'item';
                $objectiveIds[] = (int) DB::table('quest_objectives')->insertGetId([
                    'quest_id' => $questId,
                    'stage_id' => $stageId,
                    'type' => $obj['type'],
                    'target_type' => $obj['target_type'],
                    'target_id' => $isItemGate ? $obj['item'] : $obj['targets'][0],
                    'target_ids' => $isItemGate ? null : json_encode($obj['targets']),
                    'share_item_id' => $obj['item'],
                    'consume_item' => 1,
                    'required_amount' => $obj['amount'],
                    'drop_chance' => $obj['chance'],
                    'required_active_effect_id' => $obj['effect'],
                    'description_legacy' => $obj['desc'][0],
                    'description' => $this->json($obj['desc']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Игроки с этим квестом «в процессе» начинают его заново с первого этапа.
        $activeQuestPlayerIds = DB::table('quest_players')
            ->where('quest_id', $questId)
            ->where('status', 'in_progress')
            ->pluck('id');
        foreach ($activeQuestPlayerIds as $questPlayerId) {
            DB::table('quest_player_objectives')->where('quest_player_id', $questPlayerId)->delete();
            DB::table('quest_players')->where('id', $questPlayerId)->update([
                'current_stage_id' => $firstStageId,
                'current_stage_started_at' => $firstStageId !== null ? now() : null,
                'current_stage_ready_at' => null,
                'revision' => DB::raw('revision + 1'),
            ]);
            foreach ($objectiveIds as $objectiveId) {
                DB::table('quest_player_objectives')->insert([
                    'quest_player_id' => $questPlayerId,
                    'quest_objective_id' => $objectiveId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('quest_rewards')->where('quest_id', $questId)->delete();
        [$rep, $exp, $money] = $rewards;
        DB::table('quest_rewards')->insert([
            ['quest_id' => $questId, 'type' => 'reputation_points', 'amount' => $rep, 'reputation_id' => self::REPUTATION_ID, 'created_at' => now(), 'updated_at' => now()],
            ['quest_id' => $questId, 'type' => 'exp', 'amount' => $exp, 'reputation_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['quest_id' => $questId, 'type' => 'money', 'amount' => $money, 'reputation_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $questId;
    }

    /** @param  array{0: string, 1: string}  $pair */
    private function json(array $pair): string
    {
        return json_encode(['ru' => $pair[0], 'uk' => $pair[1]], JSON_UNESCAPED_UNICODE);
    }
}
