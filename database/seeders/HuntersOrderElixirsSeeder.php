<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Effect\Domain\Services\EffectBonusService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Эликсиры лавки Ордена Охотников — по одному на каждый тир репутации, 1 покупка в сутки.
 *
 * - «Эликсир Тихой тропы»     — монстры реже нападают первыми (на боссов не действует);
 * - «Охотничий эликсир»       — +% опыта с монстров;
 * - «Эликсир удачи охотника»  — +% к шансу дропа (кроме сундуков и предметов «под эффект»).
 *
 * Эликсир тира «Легенда» у игрока, прошедшего подвиг Ордена, даёт усиленный бонус
 * (feat_value, см. EffectBonusService). Идемпотентен: всё ищется по slug / названию.
 */
class HuntersOrderElixirsSeeder extends Seeder
{
    private const REPUTATION_ID = 2;

    private const SHOP_CATEGORY_ELIXIRS = 4;

    private const ELIXIR_TEMPLATE_ID = 997; // зелье-бафф, образец колонок share_items

    private const DURATION = 3600;

    private const DAY = 86400;

    /** min_points => [имя ru, имя uk, цена] */
    private const TIERS = [
        500 => ['Охотник', 'Мисливець', 5000],
        1000 => ['Следопыт', 'Слідопит', 10000],
        2000 => ['Ловчий', 'Ловчий', 20000],
        3000 => ['Мастер охоты', 'Майстер полювання', 35000],
        4000 => ['Элита', 'Еліта', 50000],
        5000 => ['Легенда', 'Легенда', 75000],
    ];

    public function run(): void
    {
        $featQuestId = DB::table('reputation_tiers')
            ->where('reputation_id', self::REPUTATION_ID)
            ->where('min_points', 5000)
            ->value('feat_quest_id');

        $lines = [
            [
                'slug' => 'hunter_quiet_path',
                'type' => EffectBonusService::AGGRESSION_REDUCTION,
                'values' => [500 => 10, 1000 => 20, 2000 => 30, 3000 => 40, 4000 => 50, 5000 => 60],
                'feat_value' => 100,
                'feat_text' => ['монстры вообще не нападают первыми (кроме боссов)', 'монстри взагалі не нападають першими (крім босів)'],
                'item' => ['Эликсир Тихой тропы', 'Еліксир Тихої стежки'],
                'effect' => ['Тихая тропа', 'Тиха стежка'],
                'text' => ['монстры нападают первыми на %d%% реже (на боссов не действует)', 'монстри нападають першими на %d%% рідше (на босів не діє)'],
                'lore' => ['Отвар из трав, отбивающих запах. Звери не замечают охотника, пока он сам не нападёт.', 'Відвар із трав, що відбивають запах. Звірі не помічають мисливця, доки він сам не нападе.'],
                'sort' => 100,
            ],
            [
                'slug' => 'hunter_experience',
                'type' => EffectBonusService::MONSTER_EXPERIENCE,
                'values' => [500 => 3, 1000 => 5, 2000 => 7, 3000 => 10, 4000 => 12, 5000 => 15],
                'feat_value' => 20,
                'item' => ['Охотничий эликсир', 'Мисливський еліксир'],
                'effect' => ['Охотничий азарт', 'Мисливський азарт'],
                'text' => ['+%d%% опыта за победы над монстрами', '+%d%% досвіду за перемоги над монстрами'],
                'lore' => ['Терпкий напиток старых охотников Ордена: каждая схватка учит большему.', 'Терпкий напій старих мисливців Ордену: кожна сутичка вчить більшого.'],
                'sort' => 200,
            ],
            [
                'slug' => 'hunter_luck',
                'type' => EffectBonusService::DROP_CHANCE,
                'values' => [500 => 5, 1000 => 10, 2000 => 15, 3000 => 20, 4000 => 25, 5000 => 30],
                'feat_value' => 40,
                'item' => ['Эликсир удачи охотника', 'Еліксир удачі мисливця'],
                'effect' => ['Удача охотника', 'Удача мисливця'],
                'text' => ['+%d%% к шансу добычи с монстров (кроме сундуков)', '+%d%% до шансу здобичі з монстрів (крім скринь)'],
                'lore' => ['Глоток — и трофеи сами просятся в руки.', 'Ковток — і трофеї самі просяться до рук.'],
                'sort' => 300,
            ],
        ];

        DB::transaction(function () use ($lines, $featQuestId): void {
            foreach ($lines as $line) {
                $sort = $line['sort'];
                foreach (self::TIERS as $minPoints => [$tierRu, $tierUk, $price]) {
                    $value = $line['values'][$minPoints];
                    $isLegend = $minPoints === 5000 && $featQuestId !== null;

                    $bonusRu = sprintf($line['text'][0], $value);
                    $bonusUk = sprintf($line['text'][1], $value);
                    $featRu = $isLegend ? ' С медалью подвига «Легенды» — '.($line['feat_text'][0] ?? sprintf($line['text'][0], $line['feat_value'])).'.' : '';
                    $featUk = $isLegend ? ' З медаллю подвигу «Легенди» — '.($line['feat_text'][1] ?? sprintf($line['text'][1], $line['feat_value'])).'.' : '';

                    $modifier = ['type' => $line['type'], 'value' => $value];
                    if ($isLegend) {
                        $modifier += ['feat_quest_id' => (int) $featQuestId, 'feat_value' => $line['feat_value']];
                    }

                    $effectId = $this->upsertEffect(
                        slug: $line['slug'].'_'.$minPoints,
                        name: [$line['effect'][0].' ('.$tierRu.')', $line['effect'][1].' ('.$tierUk.')'],
                        desc: [$this->ucfirst($bonusRu).' на 1 час.'.$featRu, $this->ucfirst($bonusUk).' на 1 годину.'.$featUk],
                        modifier: $modifier,
                    );

                    $itemId = $this->upsertElixir(
                        name: [$line['item'][0].' ('.$tierRu.')', $line['item'][1].' ('.$tierUk.')'],
                        desc: [
                            $line['lore'][0].'<br><br><b>Действие:</b> '.$bonusRu.' на 1 час.'.$featRu,
                            $line['lore'][1].'<br><br><b>Дія:</b> '.$bonusUk.' на 1 годину.'.$featUk,
                        ],
                        effectId: $effectId,
                    );

                    DB::table('reputation_shop_items')->updateOrInsert(
                        ['reputation_id' => self::REPUTATION_ID, 'share_item_id' => $itemId],
                        [
                            'share_structure_category_id' => self::SHOP_CATEGORY_ELIXIRS,
                            'price' => $price,
                            'diamond' => 0,
                            'min_points' => $minPoints,
                            'sort_order' => $sort++,
                            'purchase_limit_quantity' => 1,
                            'purchase_limit_period_seconds' => self::DAY,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            }
        });
    }

    /**
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     * @param  array<string, int|string>  $modifier
     */
    private function upsertEffect(string $slug, array $name, array $desc, array $modifier): int
    {
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'type' => 'buff',
            'chance' => 0,
            'is_stackable' => 0,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'stat_modifiers' => json_encode([$modifier]),
            'is_dispellable' => 0,
            'updated_at' => now(),
        ];

        $id = DB::table('effects')->where('slug', $slug)->value('id');
        if ($id === null) {
            return (int) DB::table('effects')->insertGetId($row + [
                'slug' => $slug,
                'image' => DB::table('effects')->where('slug', 'blood_surge')->value('image'),
                'created_at' => now(),
            ]);
        }

        DB::table('effects')->where('id', $id)->update($row);

        return (int) $id;
    }

    /**
     * @param  array{0: string, 1: string}  $name
     * @param  array{0: string, 1: string}  $desc
     */
    private function upsertElixir(array $name, array $desc, int $effectId): int
    {
        $row = [
            'name_legacy' => $name[0],
            'name' => $this->json($name),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'is_give' => 0,
            'is_clan_warehouse_allowed' => 0,
            'price' => 1,
            'break_crystal' => 0,
            'rarity' => 'rare',
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('type', 'potion')->where('name->ru', $name[0])->value('id');
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
            ['effect_id' => $effectId, 'duration_seconds' => self::DURATION, 'reapply_policy' => 'refresh', 'created_at' => now(), 'updated_at' => now()],
        );

        return $id;
    }

    private function ucfirst(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    /** @param  array{0: string, 1: string}  $pair */
    private function json(array $pair): string
    {
        return json_encode(['ru' => $pair[0], 'uk' => $pair[1]], JSON_UNESCAPED_UNICODE);
    }
}
