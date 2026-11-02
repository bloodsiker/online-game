<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 6 классонейтральных боевых пассивок × 6 рангов — доступны и магу, и
 * воину одинаково: гейт только по уровню персонажа (type=level, реально
 * проверяется — см. правку MagicSkillRequirementService в этом же патче),
 * без int/wis/str, чтобы не давать перекос ни одному билду.
 *
 * Для 4 из 6 пассивок (броня/магрезист/физ.урон/маг.урон) ничего кодить не
 * пришлось — они целиком идут через уже существующий общий пайплайн
 * PlayerStatService::fromPassiveSkills() (читает is_passive=true скиллы
 * игрока и применяет их effects/stat_modifiers как обычные модификаторы
 * статы). Для «Стойкости духа» и «Инстинкта выживания» это не стата, а
 * модификатор длительности/шанса смерти — используют synthetic-ключи
 * "debuff_duration_reduction"/"lethal_survive_chance" в тех же
 * stat_modifiers, которые читает новый код в BattleEffectService
 * (shortenByDebuffResistPassive/rollLethalSurvival), а не общий пайплайн.
 */
return new class extends Migration
{
    private const LEVEL_GATES = [1 => 1, 2 => 10, 3 => 20, 4 => 50, 5 => 75, 6 => 100];

    /** @var array<int, array{rarity: string, price: int, chestIds: list<int>}> */
    private const TIER_BOOK_META = [
        1 => ['rarity' => 'common', 'price' => 100000, 'chestIds' => [1665, 1666, 1667, 1668, 1669, 1686]],
        2 => ['rarity' => 'uncommon', 'price' => 175000, 'chestIds' => [1670, 1671, 1672, 1673]],
        3 => ['rarity' => 'rare', 'price' => 300000, 'chestIds' => [1672, 1673, 1674, 1675, 1676, 1677]],
        4 => ['rarity' => 'epic', 'price' => 500000, 'chestIds' => [1676, 1677, 1688, 1689]],
        5 => ['rarity' => 'legendary', 'price' => 850000, 'chestIds' => [1677, 1689]],
        6 => ['rarity' => 'heroic', 'price' => 1500000, 'chestIds' => [1689]],
    ];

    /**
     * @var list<array{slug:string, nameRu:string, nameUk:string, statType:string, ranks:list<int>}>
     *                                                                                               statType — ключ в stat_modifiers: 'armor'/'magic_resistance'/'attack'/'magic_damage_percent'
     *                                                                                               идут через общий пайплайн PlayerStatService; 'debuff_duration_reduction'/
     *                                                                                               'lethal_survive_chance' — synthetic-ключи для специального кода.
     */
    private const PASSIVES = [
        [
            'slug' => 'spirit-resilience', 'nameRu' => 'Стойкость духа', 'nameUk' => 'Стійкість духу',
            'statType' => 'debuff_duration_reduction', 'ranks' => [10, 15, 20, 25, 30, 40],
        ],
        [
            'slug' => 'hardened-skin', 'nameRu' => 'Закалённая кожа', 'nameUk' => 'Загартована шкіра',
            'statType' => 'armor', 'ranks' => [5, 8, 12, 16, 20, 25],
        ],
        [
            'slug' => 'astral-skin', 'nameRu' => 'Астральная кожа', 'nameUk' => 'Астральна шкіра',
            'statType' => 'magic_resistance', 'ranks' => [5, 8, 12, 16, 20, 25],
        ],
        [
            'slug' => 'survival-instinct', 'nameRu' => 'Инстинкт выживания', 'nameUk' => 'Інстинкт виживання',
            'statType' => 'lethal_survive_chance', 'ranks' => [5, 8, 12, 16, 20, 25],
        ],
        [
            'slug' => 'strike-power', 'nameRu' => 'Сила удара', 'nameUk' => 'Сила удару',
            'statType' => 'attack', 'ranks' => [3, 5, 8, 11, 15, 20],
        ],
        [
            'slug' => 'spell-power', 'nameRu' => 'Сила заклинаний', 'nameUk' => 'Сила заклинань',
            'statType' => 'magic_damage_percent', 'ranks' => [3, 5, 8, 11, 15, 20],
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PASSIVES as $passive) {
            foreach ($passive['ranks'] as $i => $value) {
                $tier = $i + 1;
                $rankSuffix = ['I', 'II', 'III', 'IV', 'V', 'VI'][$i];
                $nameRu = $passive['nameRu'].' '.$rankSuffix;
                $nameUk = $passive['nameUk'].' '.$rankSuffix;
                $slug = $passive['slug'].'-'.$tier;

                $effectId = DB::table('effects')->where('name->ru', $nameRu)->value('id');
                if ($effectId === null) {
                    $effectId = DB::table('effects')->insertGetId([
                        'slug' => 'passive-'.$slug,
                        'name' => json_encode(['ru' => $nameRu, 'uk' => $nameUk], JSON_UNESCAPED_UNICODE),
                        'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
                        'type' => 'buff',
                        'chance' => 100,
                        'is_stackable' => 0,
                        'max_stacks' => 1,
                        'tick_interval' => 1,
                        'stat_modifiers' => json_encode([['type' => $passive['statType'], 'value' => $value, 'is_percent' => true]]),
                        'is_dispellable' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $skillId = DB::table('magic_skills')->where('slug', $slug)->value('id');
                if ($skillId === null) {
                    $skillId = DB::table('magic_skills')->insertGetId([
                        'slug' => $slug,
                        'name' => json_encode(['ru' => $nameRu, 'uk' => $nameUk], JSON_UNESCAPED_UNICODE),
                        'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
                        'image' => null,
                        'level' => $tier,
                        'skill_id' => null,
                        'type' => 'utility',
                        'mana_cost' => 0,
                        'min_damage' => 0,
                        'max_damage' => 0,
                        'power_coefficient' => 0,
                        'base_healing' => 0,
                        'cooldown' => 0,
                        'target_type' => 'self',
                        'is_passive' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $effectLinked = DB::table('magic_skill_effects')
                    ->where('magic_skill_id', $skillId)->where('effect_id', $effectId)->exists();
                if (! $effectLinked) {
                    DB::table('magic_skill_effects')->insert([
                        'magic_skill_id' => $skillId, 'effect_id' => $effectId,
                        'chance' => 100, 'duration_seconds' => 0,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                $hasLevelReq = DB::table('magic_skill_requirements')
                    ->where('magic_skill_id', $skillId)->where('type', 'level')->exists();
                if (! $hasLevelReq) {
                    DB::table('magic_skill_requirements')->insert([
                        'magic_skill_id' => $skillId, 'type' => 'level',
                        'min_value' => self::LEVEL_GATES[$tier], 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                $this->ensureBook($skillId, $nameRu, $nameUk, self::TIER_BOOK_META[$tier], $now);
            }
        }
    }

    /** @param array{rarity: string, price: int, chestIds: list<int>} $meta */
    private function ensureBook(int $skillId, string $nameRu, string $nameUk, array $meta, Carbon $now): void
    {
        $alreadyLinked = DB::table('magic_skill_books')->where('magic_skill_id', $skillId)->exists();
        if ($alreadyLinked) {
            return;
        }

        $bookItemId = DB::table('share_items')->insertGetId([
            'type' => 'book',
            'name' => json_encode(['ru' => 'Гримуар: «'.$nameRu.'»', 'uk' => 'Гримуар: «'.$nameUk.'»'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'ru' => 'Прочитав этот гримуар, вы навсегда изучите пассивный навык «'.$nameRu.'».',
                'uk' => 'Прочитавши цей гримуар, ви назавжди вивчите пасивну навичку «'.$nameUk.'».',
            ], JSON_UNESCAPED_UNICODE),
            'image' => null, 'is_two_hand' => 0, 'count_use' => 0, 'is_active' => 1, 'is_sell' => 1,
            'is_auction_sellable' => 1, 'is_give' => 1, 'is_clan_warehouse_allowed' => 1, 'is_droppable' => 1,
            'is_stackable' => 1, 'is_use' => 0, 'is_slot_usable' => 0, 'is_weight' => 0, 'price' => $meta['price'],
            'diamond_price' => 0, 'break_crystal' => 0, 'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0, 'rarity' => $meta['rarity'], 'upgrade_gold_cost' => 0,
            'drop_direct_to_backpack' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('magic_skill_books')->insert([
            'share_item_id' => $bookItemId, 'magic_skill_id' => $skillId, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ($meta['chestIds'] as $chestId) {
            DB::table('share_item_has_items')->insert([
                'parent_item_id' => $chestId, 'share_item_id' => $bookItemId,
                'min_count' => 1, 'max_count' => 1, 'drop_chance' => 10,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $slugs = [];
        foreach (self::PASSIVES as $passive) {
            for ($tier = 1; $tier <= 6; $tier++) {
                $slugs[] = $passive['slug'].'-'.$tier;
            }
        }

        $skillIds = DB::table('magic_skills')->whereIn('slug', $slugs)->pluck('id');
        $bookItemIds = DB::table('magic_skill_books')->whereIn('magic_skill_id', $skillIds)->pluck('share_item_id');

        DB::table('share_item_has_items')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('share_items')->whereIn('id', $bookItemIds)->delete();

        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', $skillIds)->delete();

        $effectIds = DB::table('magic_skill_effects')->whereIn('magic_skill_id', $skillIds)->pluck('effect_id');
        DB::table('magic_skill_effects')->whereIn('magic_skill_id', $skillIds)->delete();
        DB::table('effects')->whereIn('id', $effectIds)->where('slug', 'like', 'passive-%')->delete();

        DB::table('magic_skills')->whereIn('id', $skillIds)->delete();
    }
};
