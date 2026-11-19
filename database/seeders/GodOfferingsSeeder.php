<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Подношения трём богам (Бог Мёртвых, Богиня Жизни, Бог Войны).
 *
 * Каждый бог принимает рыбу, травы и камни разной редкости, их переработку
 * (Чешуя / Сбор / Пыль, 3 сырья → 1) и Искажённую эссенцию.
 * Полное подношение ≈ 2 часа сбора (темп ограничен респауном: 2 точки на карте).
 * Варианты: 20% / 50% / 100% от полного с шансом 20 / 50 / 100%, успех = 10 очков.
 *
 * Идемпотентен: перезаписывает все варианты подношений этих трёх репутаций.
 */
final class GodOfferingsSeeder extends Seeder
{
    private const DEATH = 32;

    private const LIFE = 33;

    private const WAR = 34;

    private const REWARD_POINTS = 10;

    private const ESSENCE = 998;

    /** Полное подношение по редкости: [сырьё, переработка]. */
    private const FULL_COST = [
        'common' => [340, 100],
        'uncommon' => [235, 70],
        'rare' => [175, 50],
        'epic' => [135, 40],
        'legendary' => [105, 30],
    ];

    /** [малое, среднее, полное] для эссенции — как было. */
    private const ESSENCE_COSTS = [2, 5, 12];

    /**
     * reputation_id => list<[share_item_id, ожидаемое name_legacy, rarity, processed]>
     */
    private const OFFERINGS = [
        self::DEATH => [
            [364, 'Туманный карась', 'common', false],
            [362, 'Омела', 'common', false],
            [370, 'Бирюза', 'common', false],
            [387, 'Обсидиан', 'uncommon', false],
            [469, 'Белладонна', 'rare', false],
            [480, 'Ледяной таймень', 'epic', false],
            [492, 'Пустоцвет', 'legendary', false],
            [810, 'Чешуя туманный карась', 'common', true],
            [788, 'Пыль обсидиан', 'uncommon', true],
            [739, 'Сбор белладонна', 'rare', true],
        ],
        self::LIFE => [
            [367, 'Родниковая форель', 'common', false],
            [372, 'Клевер', 'common', false],
            [369, 'Аквамарин', 'common', false],
            [381, 'Жемчужный судак', 'uncommon', false],
            [424, 'Изумруд', 'rare', false],
            [474, 'Лотос', 'epic', false],
            [495, 'Первородная', 'legendary', false],
            [727, 'Сбор клевера', 'common', true],
            [818, 'Чешуя жемчужный судак', 'uncommon', true],
            [794, 'Пыль изумруд', 'rare', true],
        ],
        self::WAR => [
            [368, 'Ивовый голавль', 'common', false],
            [371, 'Чертополох', 'common', false],
            [366, 'Агат', 'common', false],
            [379, 'Тысячелистник', 'uncommon', false],
            [423, 'Рубин', 'rare', false],
            [479, 'Огненный лосось', 'epic', false],
            [489, 'Застывшая молния', 'legendary', false],
            [780, 'Пыль агат', 'common', true],
            [737, 'Сбор тысячелистник', 'uncommon', true],
            [826, 'Чешуя электрический угорь', 'rare', true],
        ],
    ];

    /**
     * feat quest_id => [reputation_id, бог в дательном падеже, [share_item_id => rarity]]
     * Рыба, трава и камень — редкий, эпический и легендарный.
     */
    private const FEATS = [
        // Белладонна, Ледяной таймень, Сердце горы
        140 => [self::DEATH, ['ru' => 'Кайреллу', 'uk' => 'Кайреллу'], [469 => 'rare', 480 => 'epic', 488 => 'legendary']],
        // Изумруд, Лотос, Первородная
        141 => [self::LIFE, ['ru' => 'Фиоре', 'uk' => 'Фіорі'], [424 => 'rare', 474 => 'epic', 495 => 'legendary']],
        // Мандрагора, Огненный лосось, Застывшая молния
        142 => [self::WAR, ['ru' => 'Кариндору', 'uk' => 'Каріндору'], [470 => 'rare', 479 => 'epic', 489 => 'legendary']],
    ];

    /** Подвиг = 15 полных подношений: по 5 полных на каждый из трёх ресурсов. */
    private const FEAT_FULL_OFFERINGS_PER_ITEM = 5;

    /** Тексты квестов-подвигов: дар реки, земли и камня разом. */
    private const FEAT_STORIES = [
        140 => [
            'ru' => 'Кайрелл безразлично смотрит на реку. «Тебе мало моей метки на теле. Что ж — принеси мне разом редчайшие дары реки, земли и камня, столько, сколько смертный собирает за долгие недели, и я перестану считать тебя случайным гостем».',
            'uk' => 'Кайрелл байдуже дивиться на річку. «Тобі мало моєї мітки на тілі. Що ж, принеси мені разом дари річки, землі й каменю, стільки, скільки смертний збирає довгими тижнями, і я перестану вважати тебе випадковим гостем».',
        ],
        141 => [
            'ru' => 'Фиора улыбается устало. «Ты уже носишь мою метку. Но если хочешь, чтобы она осталась с тобой навсегда, без моей помощи — принеси мне дары реки, луга и камня, разом, не по крупицам. Столько, чтобы хватило на целый сезон жизни».',
            'uk' => 'Фіора посміхається стомлено. «Ти вже носиш мою мітку. Але якщо хочеш, щоб вона залишилася з тобою назавжди, без моєї допомоги, принеси мені дари річки, луки й каменю, разом, не по крихтах. Стільки, щоб вистачило на цілий сезон життя».',
        ],
        142 => [
            'ru' => 'Кариндор оценивающе смотрит на вас. «Метка — это ещё не присяга. Принеси мне разом добычу реки, поля и жилы — гору, которую не соберёт трус, — и я буду знать, что твоя ярость не уступает моей».',
            'uk' => 'Каріндор оцінююче дивиться на вас. «Мітка — це ще не присяга. Принеси мені разом здобич річки, поля й жили — гору, яку не збере боягуз, — і я знатиму, що твоя лють не поступається моїй».',
        ],
    ];

    public function run(): void
    {
        $this->assertItems();

        DB::transaction(function (): void {
            DB::table('reputation_gamble_options')
                ->whereIn('reputation_id', array_keys(self::OFFERINGS))
                ->delete();

            $now = now();
            $rows = [];

            foreach (self::OFFERINGS as $reputationId => $items) {
                $sort = 0;
                foreach ($items as [$shareItemId, , $rarity, $processed]) {
                    $full = self::FULL_COST[$rarity][$processed ? 1 : 0];
                    $costs = [(int) round($full * 0.2), (int) round($full * 0.5), $full];
                    $this->pushVariants($rows, $reputationId, $shareItemId, $costs, $sort, $now);
                }
                $this->pushVariants($rows, $reputationId, self::ESSENCE, self::ESSENCE_COSTS, $sort, $now);
            }

            DB::table('reputation_gamble_options')->insert($rows);

            foreach (self::FEATS as $questId => $feat) {
                $this->seedFeat($questId, $feat);
            }
        });
    }

    /**
     * Подвиг = 15 полных подношений (≈ 30 ч сбора) редкими, эпическими и
     * легендарными ресурсами: по 5 полных подношений каждого, сдаётся за раз.
     *
     * @param  array{0: int, 1: array{ru: string, uk: string}, 2: array<int, string>}  $feat
     */
    private function seedFeat(int $questId, array $feat): void
    {
        [$reputationId, $godDative, $rarities] = $feat;
        $itemIds = array_keys($rarities);
        $amounts = array_map(fn (string $rarity): int => self::FEAT_FULL_OFFERINGS_PER_ITEM * self::FULL_COST[$rarity][0], $rarities);
        $now = now();

        $names = DB::table('share_items')->whereIn('id', $itemIds)->pluck('name', 'id')
            ->map(fn (string $json): array => json_decode($json, true));

        $existing = DB::table('quest_objectives')->where('quest_id', $questId)->orderBy('id')->get();
        $keep = [];

        foreach ($itemIds as $itemId) {
            $amount = $amounts[$itemId];
            $description = [
                'ru' => sprintf('Принесите %s %d× %s за раз.', $godDative['ru'], $amount, $names[$itemId]['ru']),
                'uk' => sprintf('Принесіть %s %d× «%s» за один раз.', $godDative['uk'], $amount, $names[$itemId]['uk']),
            ];
            $values = [
                'type' => 'collect',
                'target_type' => 'item',
                'target_id' => $itemId,
                'consume_item' => 1,
                'required_amount' => $amount,
                'description_legacy' => $description['ru'],
                'description' => json_encode($description, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ];

            // Существующие цели обновляем по месту, чтобы не терять прогресс игроков.
            $row = $existing->firstWhere('target_id', $itemId) ?? $existing->whereNotIn('id', $keep)->first();
            if ($row !== null && ! in_array($row->id, $keep, true)) {
                DB::table('quest_objectives')->where('id', $row->id)->update($values);
                $keep[] = $row->id;
            } else {
                $keep[] = DB::table('quest_objectives')->insertGetId($values + ['quest_id' => $questId, 'created_at' => $now]);
            }
        }

        DB::table('quest_objectives')->where('quest_id', $questId)->whereNotIn('id', $keep)->delete();

        DB::table('quests')->where('id', $questId)->update([
            'description_legacy' => self::FEAT_STORIES[$questId]['ru'],
            'description' => json_encode(self::FEAT_STORIES[$questId], JSON_UNESCAPED_UNICODE),
            'updated_at' => $now,
        ]);

        $parts = array_map(fn (string $lang): string => implode(', ', array_map(
            fn (int $id): string => sprintf('%d× %s', $amounts[$id], $names[$id][$lang]),
            $itemIds,
        )), ['ru' => 'ru', 'uk' => 'uk']);
        $featDescription = [
            'ru' => sprintf('Принесите %s за раз: %s.', $godDative['ru'], $parts['ru']),
            'uk' => sprintf('Принесіть %s за один раз: %s.', $godDative['uk'], $parts['uk']),
        ];

        DB::table('reputation_tiers')
            ->where('reputation_id', $reputationId)
            ->where('feat_quest_id', $questId)
            ->update([
                'feat_description_legacy' => $featDescription['ru'],
                'feat_description' => json_encode($featDescription, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array{0: int, 1: int, 2: int}  $costs  малое, среднее, полное
     */
    private function pushVariants(array &$rows, int $reputationId, int $shareItemId, array $costs, int &$sort, mixed $now): void
    {
        foreach ([[2, 100], [1, 50], [0, 20]] as [$index, $chance]) {
            $rows[] = [
                'reputation_id' => $reputationId,
                'share_item_id' => $shareItemId,
                'resource_cost' => $costs[$index],
                'success_chance' => $chance,
                'reward_points' => self::REWARD_POINTS,
                'sort_order' => $sort++,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    private function assertItems(): void
    {
        $expected = [self::ESSENCE => 'Искажённая эссенция'];
        foreach (self::OFFERINGS as $items) {
            foreach ($items as [$id, $name]) {
                $expected[$id] = $name;
            }
        }

        $actual = DB::table('share_items')->whereIn('id', array_keys($expected))->pluck('name_legacy', 'id');

        foreach ($expected as $id => $name) {
            if (trim((string) ($actual[$id] ?? '')) !== $name) {
                throw new RuntimeException(sprintf('share_items #%d: ожидалось «%s», в базе «%s»', $id, $name, $actual[$id] ?? '—'));
            }
        }
    }
}
