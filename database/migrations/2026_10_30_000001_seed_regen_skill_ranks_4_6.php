<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Продолжает «Регенерацию» с 3 рангов до 6 (I-III уже были, см.
 * 2026_10_27_000001 + 2026_10_29_000001/000002 для текущих живых значений:
 * 30/45/60% хила за 120/150/180с, кд 150/180/210, мана 18/32/50). Ранги
 * IV-VI продолжают те же линейные шаги, что уже были между I-II-III:
 * +15% хила, +30с длительности/кд, +18 маны, +35 к требованию «Колдовство».
 * Цена книги continues комбатный ценовой ряд 100k/175k/300k/500k/850k
 * (IV переиспользует уже известную ступень 850k за level=5, V/VI — новые
 * ступени тем же множителем ×1.7).
 */
return new class extends Migration
{
    private const MAGIC_SKILL_ID = 27; // «Колдовство»

    public function up(): void
    {
        $now = now();

        $prevId = (int) DB::table('magic_skills')->where('slug', 'regen_skill_iii')->value('id'); // 88

        $ranks = [
            [
                'slug' => 'regen_skill_iv', 'roman' => 'IV', 'tier' => 5,
                'mana' => 68, 'cooldown' => 240, 'percent' => 75, 'duration' => 210,
                'reqSkill' => 105, 'reqInt' => 50, 'reqWis' => 80,
                'price' => 850000, 'chestIds' => [1672, 1673, 1674, 1675, 1676, 1677],
            ],
            [
                'slug' => 'regen_skill_v', 'roman' => 'V', 'tier' => 6,
                'mana' => 86, 'cooldown' => 270, 'percent' => 90, 'duration' => 240,
                'reqSkill' => 140, 'reqInt' => 65, 'reqWis' => 105,
                'price' => 1450000, 'chestIds' => [1675, 1676, 1677, 1689],
            ],
            [
                'slug' => 'regen_skill_vi', 'roman' => 'VI', 'tier' => 7,
                'mana' => 104, 'cooldown' => 300, 'percent' => 105, 'duration' => 270,
                'reqSkill' => 175, 'reqInt' => 80, 'reqWis' => 130,
                'price' => 2450000, 'chestIds' => [1677, 1689],
            ],
        ];

        foreach ($ranks as $rank) {
            $description = json_encode([
                'ru' => "Восстанавливает {$rank['percent']}% максимального здоровья за {$rank['duration']} секунд. Действует в бою и вне боя. Откат — {$rank['cooldown']} секунд.",
                'uk' => "Відновлює {$rank['percent']}% максимального здоров'я за {$rank['duration']} секунд. Діє в бою та поза боєм. Відновлення — {$rank['cooldown']} секунд.",
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            $skillId = $this->ensureSkill($rank['slug'], [
                'ru' => "Регенерация {$rank['roman']}", 'uk' => "Регенерація {$rank['roman']}",
            ], $rank['tier'], $description, [
                'mana_cost' => $rank['mana'], 'cooldown' => $rank['cooldown'],
                'target_type' => 'all', 'upgrades_skill_id' => $prevId,
            ], $now);

            $effectId = $this->ensureRegenEffect("Регенерация {$rank['roman']}", $rank['percent'], $description, $now);
            $this->ensureSkillEffect($skillId, $effectId, 100, $rank['duration'], $now);
            $this->ensureRequirement($skillId, $rank['reqSkill'], $rank['reqInt'], $rank['reqWis']);
            $this->ensureBook($skillId, $rank['price'], $rank['chestIds']);

            $prevId = $skillId;
        }
    }

    /** @param array<string, mixed> $fields */
    private function ensureSkill(string $slug, array $name, int $tier, string $description, array $fields, Carbon $now): int
    {
        $existing = DB::table('magic_skills')->where('slug', $slug)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('magic_skills')->insertGetId(array_merge([
            'slug' => $slug,
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'description' => $description,
            'image' => null,
            'level' => $tier,
            'skill_id' => self::MAGIC_SKILL_ID,
            'type' => 'buff',
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0,
            'base_healing' => 0,
            'is_passive' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $fields));
    }

    private function ensureRegenEffect(string $nameRu, int $valuePerTick, string $description, Carbon $now): int
    {
        $existing = DB::table('effects')->where('name->ru', $nameRu)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('effects')->insertGetId([
            'slug' => 'buff-'.str()->slug($nameRu),
            'name' => json_encode(['ru' => $nameRu, 'uk' => str_replace('Регенерация', 'Регенерація', $nameRu)], JSON_UNESCAPED_UNICODE),
            'description' => $description,
            'type' => 'buff',
            'active_type' => 'regen',
            'damage_scaling_type' => 'target_max_hp',
            'chance' => 100,
            'is_stackable' => 0,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'value_per_tick' => $valuePerTick,
            'is_dispellable' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function ensureSkillEffect(int $skillId, int $effectId, int $chance, int $durationSeconds, Carbon $now): void
    {
        $exists = DB::table('magic_skill_effects')->where('magic_skill_id', $skillId)->where('effect_id', $effectId)->exists();
        if (! $exists) {
            DB::table('magic_skill_effects')->insert([
                'magic_skill_id' => $skillId, 'effect_id' => $effectId, 'chance' => $chance,
                'duration_seconds' => $durationSeconds, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function ensureRequirement(int $skillId, int $magicSkillLevel, int $int, int $wis): void
    {
        $now = now();

        $rows = [
            ['type' => 'skill', 'stat_key' => null, 'skill_id' => self::MAGIC_SKILL_ID, 'min_value' => $magicSkillLevel],
            ['type' => 'stat', 'stat_key' => 'intelligence', 'skill_id' => null, 'min_value' => $int],
            ['type' => 'stat', 'stat_key' => 'wisdom', 'skill_id' => null, 'min_value' => $wis],
        ];

        foreach ($rows as $row) {
            $query = DB::table('magic_skill_requirements')->where('magic_skill_id', $skillId)->where('type', $row['type']);
            $query = $row['type'] === 'stat' ? $query->where('stat_key', $row['stat_key']) : $query->where('skill_id', $row['skill_id']);

            if (! $query->exists()) {
                DB::table('magic_skill_requirements')->insert(array_merge($row, [
                    'magic_skill_id' => $skillId, 'created_at' => $now, 'updated_at' => $now,
                ]));
            }
        }
    }

    /** @param list<int> $chestIds */
    private function ensureBook(int $skillId, int $price, array $chestIds): void
    {
        $alreadyLinked = DB::table('magic_skill_books')->where('magic_skill_id', $skillId)->exists();
        if ($alreadyLinked) {
            return;
        }

        $skillName = DB::table('magic_skills')->where('id', $skillId)->value('name');
        $decoded = json_decode((string) $skillName, true);
        $now = now();

        $bookItemId = DB::table('share_items')->insertGetId([
            'type' => 'book',
            'name' => json_encode(['ru' => 'Гримуар: «'.$decoded['ru'].'»', 'uk' => 'Гримуар: «'.$decoded['uk'].'»'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'ru' => 'Прочитав этот гримуар, вы навсегда изучите заклинание «'.$decoded['ru'].'».',
                'uk' => 'Прочитавши цей гримуар, ви назавжди вивчите заклинання «'.$decoded['uk'].'».',
            ], JSON_UNESCAPED_UNICODE),
            'image' => null, 'is_two_hand' => 0, 'count_use' => 0, 'is_active' => 1, 'is_sell' => 1,
            'is_auction_sellable' => 1, 'is_give' => 1, 'is_clan_warehouse_allowed' => 1, 'is_droppable' => 1,
            'is_stackable' => 1, 'is_use' => 0, 'is_slot_usable' => 0, 'is_weight' => 0, 'price' => $price,
            'diamond_price' => 0, 'break_crystal' => 0, 'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0, 'rarity' => 'common', 'upgrade_gold_cost' => 0,
            'drop_direct_to_backpack' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('magic_skill_books')->insert([
            'share_item_id' => $bookItemId, 'magic_skill_id' => $skillId, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ($chestIds as $chestId) {
            DB::table('share_item_has_items')->insert([
                'parent_item_id' => $chestId, 'share_item_id' => $bookItemId,
                'min_count' => 1, 'max_count' => 1, 'drop_chance' => 10,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $slugs = ['regen_skill_iv', 'regen_skill_v', 'regen_skill_vi'];
        $skillIds = DB::table('magic_skills')->whereIn('slug', $slugs)->pluck('id');

        $bookItemIds = DB::table('magic_skill_books')->whereIn('magic_skill_id', $skillIds)->pluck('share_item_id');
        DB::table('share_item_has_items')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('share_items')->whereIn('id', $bookItemIds)->delete();

        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', $skillIds)->delete();
        DB::table('magic_skill_effects')->whereIn('magic_skill_id', $skillIds)->delete();
        DB::table('magic_skills')->whereIn('id', $skillIds)->delete();

        DB::table('effects')->whereIn('name->ru', ['Регенерация IV', 'Регенерация V', 'Регенерация VI'])->delete();
    }
};
