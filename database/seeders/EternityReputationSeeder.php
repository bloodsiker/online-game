<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Eternum и репутация «Вечность».
 *
 * - Eternum — редкий непередаваемый кристалл. Источники: «Хронолит»
 *   (1 использование в сутки) и редкий дроп с боссов.
 * - Хронолит продаётся в лавке Искателей реликтов (Хальдор) и улучшается
 *   у кузнеца стандартным апгрейдом редкости (upgrade_to_share_item_id), материал —
 *   чертёж из той же лавки (1 → 6 Eternum за использование). Улучшение всегда успешно.
 * - Eternum используется из рюкзака (можно пачкой): 1 шт. = 1 очко репутации
 *   «Вечность» (Эйдран). Репутация бесконечная, без медалей; пороги используются
 *   только магазином «Вечности» (сумки и пояса, цена — монеты + Eternum).
 *
 * Идемпотентен: всё ищется по названию / slug.
 */
class EternityReputationSeeder extends Seeder
{
    private const RELIC_REPUTATION_ID = 3;

    private const HALDOR_NPC_ID = 18;

    private const EIDRAN_NPC_ID = 1;

    private const CATEGORY_TOOLS = 9;

    private const CATEGORY_MISC = 8;

    /** Шаблоны колонок share_items: «Рюкзак путешественника» и «Пояс титана». */
    private const BAG_TEMPLATE_ID = 20;

    private const BELT_TEMPLATE_ID = 19;

    /**
     * Магазин «Вечности» (Эйдран): [тип, ru, uk, редкость, бонус слотов, порог репутации, монеты, Eternum].
     * bag — ячейки рюкзака (bag_slot), belt — ячейки хотбара (belt_slot). Лимита покупки нет.
     */
    private const ETERNITY_SHOP = [
        ['belt', 'Пояс путника', 'Пояс подорожнього', 'common', 1, 0, 3000, 2],
        ['bag', 'Сумка путника', 'Сумка подорожнього', 'common', 3, 0, 5000, 3],
        ['belt', 'Пояс хрониста', 'Пояс хроніста', 'rare', 4, 150, 20000, 10],
        ['bag', 'Сумка странника времени', 'Сумка мандрівника часу', 'rare', 12, 200, 30000, 15],
        ['bag', 'Сумка застывшего часа', 'Сумка застиглої години', 'epic', 14, 500, 75000, 40],
        ['belt', 'Пояс песочных часов', 'Пояс пісочного годинника', 'epic', 5, 600, 100000, 50],
        ['bag', 'Сумка Вечности', 'Сумка Вічності', 'legendary', 16, 1000, 200000, 100],
        ['belt', 'Пояс Вечности', 'Пояс Вічності', 'legendary', 6, 2000, 400000, 200],
        ['bag', 'Сума Первых', 'Сума Перших', 'heroic', 18, 3000, 750000, 300],
    ];

    private const DAY = 86400;

    /** «Один раз навсегда» для лимита покупки: 10 лет. */
    private const ONCE = 315360000;

    private const BOSS_DROP_CHANCE = 2;

    /** [ru, uk, rarity, Eternum за использование] */
    private const MECHANISMS = [
        ['Треснувший хронолит', 'Тріснутий хронолит', 'common', 1],
        ['Восстановленный хронолит', 'Відновлений хронолит', 'uncommon', 2],
        ['Рунный хронолит', 'Рунічний хронолит', 'rare', 3],
        ['Сияющий хронолит', 'Сяйливий хронолит', 'epic', 4],
        ['Пробуждённый хронолит', 'Пробуджений хронолит', 'legendary', 5],
        ['Хронолит Первых', 'Хронолит Перших', 'heroic', 6],
    ];

    /** Прежние названия ступеней (до переименования в «Хронолит») — для переименования без дублей. */
    private const LEGACY_MECHANISM_NAMES = [
        'Ржавый реликтовый механизм',
        'Отлаженный реликтовый механизм',
        'Латунный реликтовый механизм',
        'Серебряный реликтовый механизм',
        'Золотой реликтовый механизм',
        'Механизм Первых',
    ];

    /**
     * Лавка Хальдора: [индекс предмета (0 — хронолит, 1..5 — чертёж до уровня N+1),
     * мин. репутация, монеты, [share_item_id реликта => кол-во]].
     */
    private const RELIC_SHOP = [
        [0, 500, 10000, [355 => 10]],
        [1, 1000, 20000, [356 => 10]],
        [2, 1500, 40000, [357 => 10]],
        [3, 2000, 70000, [358 => 10]],
        [4, 2500, 100000, [359 => 5]],
        [5, 3000, 150000, [360 => 3]],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->renameLegacyItems();
            $reputationId = $this->upsertEternityReputation();
            $eternumId = $this->upsertEternum($reputationId);

            $mechanismIds = [];
            foreach (self::MECHANISMS as $level => [$ru, $uk, $rarity, $amount]) {
                $mechanismIds[$level] = $this->upsertMechanism($level + 1, $ru, $uk, $rarity, $amount, $eternumId);
            }

            $scrollIds = [];
            foreach (range(1, count(self::MECHANISMS) - 1) as $level) {
                $scrollIds[$level] = $this->upsertScroll($level);
                $this->linkRarityUpgrade($mechanismIds[$level - 1], $mechanismIds[$level], $scrollIds[$level]);
            }

            $this->seedRelicShop($mechanismIds[0], $scrollIds);
            $this->removeEternumExchange($eternumId);
            $this->seedEternityShop($reputationId, $eternumId);
            $this->seedBossDrops($eternumId);
        });
    }

    /** «Реликтовый механизм» переименован в «Хронолит»: переименовываем существующие предметы, чтобы upsert не создал дубли. */
    private function renameLegacyItems(): void
    {
        foreach (self::LEGACY_MECHANISM_NAMES as $level => $legacyName) {
            [$ru] = self::MECHANISMS[$level];
            DB::table('share_items')->where('type', 'misc')->where('name_legacy', $legacyName)->update(['name_legacy' => $ru]);
            if ($level > 0) {
                DB::table('share_items')->where('type', 'misc')
                    ->where('name_legacy', 'Чертёж механизма: '.$legacyName)
                    ->update(['name_legacy' => 'Чертёж: '.$ru]);
            }
        }
    }

    private function upsertEternum(int $reputationId): int
    {
        $id = $this->upsertItem(
            name: ['Eternum', 'Eternum'],
            desc: [
                'Редчайший кристалл, в котором застыло само время. Говорят, его свет не меркнет даже в полной темноте.<br><br>'
                    .'Источники: «Хронолит» Искателей реликтов и, изредка, могущественные боссы.<br>'
                    .'<b>Действие:</b> +1 к репутации «Вечность» за каждый кристалл. Можно использовать сразу несколько.',
                'Найрідкісніший кристал, у якому застиг сам час. Кажуть, його світло не згасає навіть у цілковитій темряві.<br><br>'
                    .'Джерела: «Хронолит» Шукачів реліктів і, зрідка, могутні боси.<br>'
                    .'<b>Дія:</b> +1 до репутації «Вічність» за кожен кристал. Можна використати одразу кілька.',
            ],
            type: 'misc',
            rarity: 'legendary',
            stackable: true,
            usable: true,
        );

        $this->setEffect($id, 'grant_reputation', 1, null, $reputationId);

        return $id;
    }

    private function upsertMechanism(int $level, string $ru, string $uk, string $rarity, int $amount, int $eternumId): int
    {
        $next = self::MECHANISMS[$level] ?? null;
        $upgradeRu = $next !== null ? '<br>Кузнец (вкладка «Апгрейд») улучшит его до «'.$next[0].'» по чертежу Искателей.' : '<br>Это высшая ступень хронолита.';
        $upgradeUk = $next !== null ? '<br>Коваль (вкладка «Апгрейд») покращить його до «'.$next[1].'» за кресленням Шукачів.' : '<br>Це найвищий ступінь хронолита.';

        $id = $this->upsertItem(
            name: [$ru, $uk],
            desc: [
                'Хронолит — каменный артефакт исчезнувшей цивилизации с часовым механизмом внутри. Искатели реликтов вернули его к жизни, и он снова медленно сгущает Eternum из самой ткани времени.<br><br>'
                    .'<b>Действие:</b> раз в сутки даёт '.$amount.' Eternum. Не расходуется.'.$upgradeRu,
                'Хронолит — кам’яний артефакт зниклої цивілізації з годинниковим механізмом усередині. Шукачі реліктів повернули його до життя, і він знову повільно згущує Eternum із самої тканини часу.<br><br>'
                    .'<b>Дія:</b> раз на добу дає '.$amount.' Eternum. Не витрачається.'.$upgradeUk,
            ],
            type: 'misc',
            rarity: $rarity,
            stackable: false,
            usable: true,
        );

        $this->setEffect($id, 'produce_item', $amount, $eternumId, null);
        DB::table('share_item_use_limits')->updateOrInsert(
            ['share_item_id' => $id],
            ['max_uses' => 1, 'period_seconds' => self::DAY, 'created_at' => now(), 'updated_at' => now()],
        );

        return $id;
    }

    private function upsertScroll(int $toLevel): int
    {
        [$fromRu, $fromUk] = self::MECHANISMS[$toLevel - 1];
        [$toRu, $toUk, $rarity, $amount] = self::MECHANISMS[$toLevel];

        $id = $this->upsertItem(
            name: ['Чертёж: '.$toRu, 'Креслення: '.$toUk],
            desc: [
                'Схема Искателей реликтов с пометками мастера Хальдора.<br><br>'
                    .'Отнесите кузнецу вместе с «'.$fromRu.'» (вкладка «Апгрейд») — он соберёт «'.$toRu.'» ('.$amount.' Eternum в сутки). '
                    .'Улучшение всегда успешно.',
                'Схема Шукачів реліктів із позначками майстра Хальдора.<br><br>'
                    .'Віднесіть ковалю разом із «'.$fromUk.'» (вкладка «Апгрейд») — він збере «'.$toUk.'» ('.$amount.' Eternum на добу). '
                    .'Покращення завжди успішне.',
            ],
            type: 'misc',
            rarity: $rarity,
            stackable: true,
            usable: false,
        );

        DB::table('share_item_effects')->where('share_item_id', $id)->delete();

        return $id;
    }

    /** Апгрейд редкости у кузнеца: хронолит → следующая ступень, материал — 1 чертёж. */
    private function linkRarityUpgrade(int $fromId, int $toId, int $scrollId): void
    {
        DB::table('share_items')->where('id', $fromId)->update([
            'upgrade_to_share_item_id' => $toId,
            'upgrade_gold_cost' => 0,
        ]);

        DB::table('share_item_upgrade_materials')->where('share_item_id', $fromId)
            ->where('required_share_item_id', '!=', $scrollId)->delete();
        DB::table('share_item_upgrade_materials')->updateOrInsert(
            ['share_item_id' => $fromId, 'required_share_item_id' => $scrollId],
            ['count' => 1, 'created_at' => now(), 'updated_at' => now()],
        );
    }

    /** @param  array<int, int>  $scrollIds  уровень => share_item_id */
    private function seedRelicShop(int $mechanismId, array $scrollIds): void
    {
        $shopId = $this->upsertStructure(
            type: 'shop',
            npcId: self::HALDOR_NPC_ID,
            name: ['Лавка Искателей реликтов', 'Крамниця Шукачів реліктів'],
            desc: [
                'Хальдор продаёт то, что Искатели восстановили из древних руин. Платят здесь не только монетами — реликтами тоже.',
                'Хальдор продає те, що Шукачі відновили з давніх руїн. Платять тут не лише монетами — реліктами теж.',
            ],
            reputationId: self::RELIC_REPUTATION_ID,
        );

        DB::table('shop_categories')->updateOrInsert(
            ['structure_id' => $shopId, 'share_structure_category_id' => self::CATEGORY_TOOLS],
        );

        foreach (self::RELIC_SHOP as $sort => [$index, $minPoints, $price, $relics]) {
            $itemId = $index === 0 ? $mechanismId : $scrollIds[$index];

            DB::table('shop_items')->updateOrInsert(
                ['structure_id' => $shopId, 'share_item_id' => $itemId],
                [
                    'share_structure_category_id' => self::CATEGORY_TOOLS,
                    'price' => $price,
                    'diamond' => 0,
                    'min_reputation_points' => $minPoints,
                    'purchase_limit_quantity' => 1,
                    'purchase_limit_period_seconds' => self::ONCE,
                    'sort_order' => 100 - $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $shopItemId = (int) DB::table('shop_items')
                ->where('structure_id', $shopId)->where('share_item_id', $itemId)->value('id');

            DB::table('shop_item_requirements')->where('shop_item_id', $shopItemId)
                ->whereNotIn('share_item_id', array_keys($relics))->delete();
            foreach ($relics as $relicId => $quantity) {
                DB::table('shop_item_requirements')->updateOrInsert(
                    ['shop_item_id' => $shopItemId, 'share_item_id' => $relicId],
                    ['quantity' => $quantity, 'created_at' => now(), 'updated_at' => now()],
                );
            }
        }
    }

    private function upsertEternityReputation(): int
    {
        $name = ['Вечность', 'Вічність'];
        $desc = [
            'Эйдран, глава города, ведёт летопись тех, кто собирает Eternum — кристаллы застывшего времени. '
                .'Каждый использованный кристалл укрепляет вашу связь с Вечностью (+1 очко). '
                .'У этой репутации нет предела и медалей: чем больше очков, тем больше товаров открывается в магазине «Вечности».',
            'Ейдран, голова міста, веде літопис тих, хто збирає Eternum — кристали застиглого часу. '
                .'Кожен використаний кристал зміцнює ваш зв’язок із Вічністю (+1 очко). '
                .'Ця репутація не має меж і медалей: що більше очок, то більше товарів відкривається в магазині «Вічності».',
        ];

        $reputationId = DB::table('reputations')->where('name_legacy', $name[0])->value('id');
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'npc_id' => self::EIDRAN_NPC_ID,
            'updated_at' => now(),
        ];
        if ($reputationId === null) {
            $reputationId = DB::table('reputations')->insertGetId($row + ['created_at' => now()]);
        } else {
            DB::table('reputations')->where('id', $reputationId)->update($row);
        }

        // Один бесконечный тир без медалей.
        if (! DB::table('reputation_tiers')->where('reputation_id', $reputationId)->exists()) {
            DB::table('reputation_tiers')->insert([
                'reputation_id' => $reputationId,
                'min_points' => 0,
                'max_points' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $reputationId;
    }

    /** Ранняя версия сдавала Eternum Эйдрану через обмен — теперь он используется из рюкзака. */
    private function removeEternumExchange(int $eternumId): void
    {
        DB::table('reputation_exchanges')->where('share_item_id', $eternumId)->delete();
        DB::table('structures')
            ->where('type', 'reputation_exchange')
            ->where('npc_id', self::EIDRAN_NPC_ID)
            ->where('name_legacy', 'Обмен Eternum')
            ->delete();
    }

    private function seedEternityShop(int $reputationId, int $eternumId): void
    {
        $shopId = $this->upsertStructure(
            type: 'shop',
            npcId: self::EIDRAN_NPC_ID,
            name: ['Магазин Вечности', 'Крамниця Вічності'],
            desc: [
                'Эйдран открывает свои запасы тем, кто доказал связь с Вечностью. Товары открываются по мере роста репутации «Вечность»; '
                    .'платить можно монетами и кристаллами Eternum.',
                'Ейдран відкриває свої запаси тим, хто довів зв’язок із Вічністю. Товари відкриваються в міру зростання репутації «Вічність»; '
                    .'платити можна монетами й кристалами Eternum.',
            ],
            reputationId: $reputationId,
        );

        DB::table('shop_categories')->updateOrInsert(
            ['structure_id' => $shopId, 'share_structure_category_id' => self::CATEGORY_MISC],
        );

        foreach (self::ETERNITY_SHOP as $sort => [$kind, $ru, $uk, $rarity, $slots, $minPoints, $price, $eternum]) {
            $isBag = $kind === 'bag';
            $bonusRu = $isBag ? '+'.$slots.' ячеек рюкзака' : '+'.$slots.' ячеек панели быстрого доступа';
            $bonusUk = $isBag ? '+'.$slots.' комірок рюкзака' : '+'.$slots.' комірок панелі швидкого доступу';

            $itemId = $this->upsertEquipment(
                templateId: $isBag ? self::BAG_TEMPLATE_ID : self::BELT_TEMPLATE_ID,
                name: [$ru, $uk],
                desc: [
                    'Награда Эйдрана за верность Вечности. Время внутри неё течёт иначе — и вмещает больше, чем кажется.<br><br><b>Бонус:</b> '.$bonusRu.'.',
                    'Нагорода Ейдрана за вірність Вічності. Час усередині плине інакше — і вміщує більше, ніж здається.<br><br><b>Бонус:</b> '.$bonusUk.'.',
                ],
                rarity: $rarity,
                sellPrice: $price,
                statType: $isBag ? 'bag_slot' : 'belt_slot',
                slots: $slots,
            );

            DB::table('shop_items')->updateOrInsert(
                ['structure_id' => $shopId, 'share_item_id' => $itemId],
                [
                    'share_structure_category_id' => self::CATEGORY_MISC,
                    'price' => $price,
                    'diamond' => 0,
                    'min_reputation_points' => $minPoints,
                    // Без лимита: можно купить сколько угодно (например, две одинаковые сумки).
                    'purchase_limit_quantity' => 0,
                    'purchase_limit_period_seconds' => 0,
                    'sort_order' => 100 - $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $shopItemId = (int) DB::table('shop_items')
                ->where('structure_id', $shopId)->where('share_item_id', $itemId)->value('id');

            DB::table('shop_item_requirements')->where('shop_item_id', $shopItemId)
                ->where('share_item_id', '!=', $eternumId)->delete();
            DB::table('shop_item_requirements')->updateOrInsert(
                ['shop_item_id' => $shopItemId, 'share_item_id' => $eternumId],
                ['quantity' => $eternum, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    /**
     * Сумка/пояс: копия колонок шаблона (тип, слот, вес) + бонус слотов; передать нельзя, продать в магазин можно.
     *
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     */
    private function upsertEquipment(int $templateId, array $name, array $desc, string $rarity, int $sellPrice, string $statType, int $slots): int
    {
        $template = (array) DB::table('share_items')->where('id', $templateId)->first();
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'rarity' => $rarity,
            'is_active' => 1,
            // Можно продать в магазин (за половину цены в монетах, Eternum не возвращается), но не передать.
            'is_sell' => 1,
            'is_give' => 0,
            'is_droppable' => 0,
            'is_auction_sellable' => 0,
            'is_clan_warehouse_allowed' => 0,
            'price' => $sellPrice,
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('type', $template['type'])->where('name_legacy', $name[0])->value('id');
        if ($id === null) {
            unset($template['id']);
            $id = DB::table('share_items')->insertGetId(array_merge($template, $row, [
                'image' => null,
                'transparent_image' => null,
                'created_at' => now(),
            ]));
        } else {
            DB::table('share_items')->where('id', $id)->update($row);
        }

        DB::table('share_item_stats')->where('share_item_id', $id)->delete();
        DB::table('share_item_stats')->insert([
            'share_item_id' => $id,
            'stat_type' => $statType,
            'value' => $slots,
            'value_type' => 'flat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $id;
    }

    private function seedBossDrops(int $eternumId): void
    {
        $bossIds = DB::table('monsters')->where('is_boss', 1)->pluck('id');
        foreach ($bossIds as $bossId) {
            DB::table('monster_has_items')->updateOrInsert(
                ['monster_id' => $bossId, 'share_item_id' => $eternumId],
                ['drop_chance' => self::BOSS_DROP_CHANCE, 'min_count' => 1, 'max_count' => 1],
            );
        }
    }

    /**
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     */
    private function upsertItem(array $name, array $desc, string $type, string $rarity, bool $stackable, bool $usable): int
    {
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'type' => $type,
            'rarity' => $rarity,
            'is_active' => 1,
            'is_stackable' => $stackable ? 1 : 0,
            'is_use' => $usable ? 1 : 0,
            'is_weight' => 0,
            'is_sell' => 0,
            'is_give' => 0,
            'is_droppable' => 0,
            'is_auction_sellable' => 0,
            'is_clan_warehouse_allowed' => 0,
            'price' => 1,
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('name_legacy', $name[0])->where('type', $type)->value('id');
        if ($id !== null) {
            DB::table('share_items')->where('id', $id)->update($row);

            return (int) $id;
        }

        return (int) DB::table('share_items')->insertGetId($row + ['created_at' => now()]);
    }

    private function setEffect(int $itemId, string $type, int $value, ?int $targetItemId, ?int $reputationId): void
    {
        DB::table('share_item_effects')->where('share_item_id', $itemId)->where('effect_type', '!=', $type)->delete();
        DB::table('share_item_effects')->updateOrInsert(
            ['share_item_id' => $itemId, 'effect_type' => $type],
            [
                'value' => $value,
                'value_type' => 'flat',
                'duration_seconds' => null,
                'target_share_item_id' => $targetItemId,
                'reputation_id' => $reputationId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     */
    private function upsertStructure(string $type, int $npcId, array $name, array $desc, ?int $reputationId): int
    {
        $locationId = DB::table('npcs')->where('id', $npcId)->value('location_id');
        $row = [
            'type' => $type,
            'npc_id' => $npcId,
            'location_id' => $locationId,
            'reputation_id' => $reputationId,
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'updated_at' => now(),
        ];

        $id = DB::table('structures')->where('type', $type)->where('npc_id', $npcId)->where('name_legacy', $name[0])->value('id');
        if ($id !== null) {
            DB::table('structures')->where('id', $id)->update($row);

            return (int) $id;
        }

        return (int) DB::table('structures')->insertGetId($row + ['created_at' => now()]);
    }

    /** @param  array{0: string, 1: string}  $pair */
    private function json(array $pair): string
    {
        return json_encode(['ru' => $pair[0], 'uk' => $pair[1]], JSON_UNESCAPED_UNICODE);
    }
}
