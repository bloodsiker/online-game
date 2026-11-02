<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Хил/баффы были тестовым стабом: длительность эффекта почти равна или
 * больше кулдауна (Благословение — 600с баф при 120с кд, т.е. почти
 * постоянный), без рангов, без требований на изучение и без книг. Доводит
 * до того же стандарта, что боевые скиллы (2026_10_26_000001/000002):
 * 3 ранга на Колдовство 0/35/70, книги+дроп, int/wis-пороги (с акцентом на
 * мудрость — вторая роль статы, см. спеку magic-combat-system-design).
 *
 * Существующие 4 эффекта (id 5-8, использует только эта группа скиллов,
 * проверено — не шарятся с кланом/монстрами) занижаются до ранга I,
 * ранги II/III — новые строки effects, по аналогии с «Божественная ярость
 * +5/+8/+13/+17/+25%» (id 22-26).
 */
return new class extends Migration
{
    private const MAGIC_SKILL_ID = 27; // «Колдовство»

    public function up(): void
    {
        $now = now();

        // 1) Исцеление: Малое (ранг I) чинится на месте, Среднее — новый скилл,
        // Великое (id=28) становится рангом III.
        DB::table('magic_skills')->where('id', 27)->update([
            'power_coefficient' => 0.35, 'cooldown' => 20,
        ]);
        DB::table('magic_skills')->where('id', 28)->update([
            'mana_cost' => 50, 'base_healing' => 320, 'power_coefficient' => 0.35, 'cooldown' => 25,
        ]);
        $midHealId = $this->ensureSkill('mid_heal', ['ru' => 'Среднее исцеление', 'uk' => 'Середнє зцілення'], 2, 'heal', [
            'mana_cost' => 30, 'min_damage' => 0, 'max_damage' => 0, 'power_coefficient' => 0.35,
            'base_healing' => 180, 'cooldown' => 20, 'target_type' => 'all',
        ], $now);

        // 2) Боевой клич: id=5 (+30%) занижается до ранга I, новые эффекты под II/III.
        DB::table('effects')->where('id', 5)->update(['stat_modifiers' => json_encode([['type' => 'attack', 'value' => 15, 'is_percent' => true]])]);
        DB::table('magic_skills')->where('id', 24)->update(['cooldown' => 20]);
        DB::table('magic_skill_effects')->where('magic_skill_id', 24)->where('effect_id', 5)->update(['duration_seconds' => 15]);
        $warCryIiEffect = $this->ensureEffect('Боевой клич II', [['type' => 'attack', 'value' => 22, 'is_percent' => true]], $now);
        $warCryIiiEffect = $this->ensureEffect('Боевой клич III', [['type' => 'attack', 'value' => 30, 'is_percent' => true]], $now);
        $warCryIiId = $this->ensureSkill('battle_cry_ii', ['ru' => 'Боевой клич II', 'uk' => 'Бойовий клич II'], 3, 'buff', [
            'mana_cost' => 35, 'cooldown' => 25, 'target_type' => 'all',
        ], $now);
        $warCryIiiId = $this->ensureSkill('battle_cry_iii', ['ru' => 'Боевой клич III', 'uk' => 'Бойовий клич III'], 4, 'buff', [
            'mana_cost' => 55, 'cooldown' => 30, 'target_type' => 'all',
        ], $now);
        $this->ensureSkillEffect($warCryIiId, $warCryIiEffect, 100, 18, $now);
        $this->ensureSkillEffect($warCryIiiId, $warCryIiiEffect, 100, 20, $now);

        // 3) Каменная кожа: id=6 (+50%) занижается до ранга I, новые эффекты под II/III.
        DB::table('effects')->where('id', 6)->update(['stat_modifiers' => json_encode([['type' => 'armor', 'value' => 20, 'is_percent' => true]])]);
        DB::table('magic_skills')->where('id', 25)->update(['cooldown' => 20]);
        DB::table('magic_skill_effects')->where('magic_skill_id', 25)->where('effect_id', 6)->update(['duration_seconds' => 15]);
        $stoneSkinIiEffect = $this->ensureEffect('Каменная кожа II', [['type' => 'armor', 'value' => 32, 'is_percent' => true]], $now);
        $stoneSkinIiiEffect = $this->ensureEffect('Каменная кожа III', [['type' => 'armor', 'value' => 50, 'is_percent' => true]], $now);
        $stoneSkinIiId = $this->ensureSkill('stone_skin_ii', ['ru' => 'Каменная кожа II', 'uk' => 'Кам\'яна шкіра II'], 3, 'buff', [
            'mana_cost' => 40, 'cooldown' => 25, 'target_type' => 'self',
        ], $now);
        $stoneSkinIiiId = $this->ensureSkill('stone_skin_iii', ['ru' => 'Каменная кожа III', 'uk' => 'Кам\'яна шкіра III'], 4, 'buff', [
            'mana_cost' => 60, 'cooldown' => 30, 'target_type' => 'self',
        ], $now);
        $this->ensureSkillEffect($stoneSkinIiId, $stoneSkinIiEffect, 100, 18, $now);
        $this->ensureSkillEffect($stoneSkinIiiId, $stoneSkinIiiEffect, 100, 20, $now);

        // 4) Благословение: id=7 (+20%/+20%) занижается до ранга I, новые эффекты под II/III.
        DB::table('effects')->where('id', 7)->update(['stat_modifiers' => json_encode([
            ['type' => 'hp_max', 'value' => 10, 'is_percent' => true],
            ['type' => 'mp_max', 'value' => 10, 'is_percent' => true],
        ])]);
        DB::table('magic_skills')->where('id', 26)->update(['cooldown' => 25]);
        DB::table('magic_skill_effects')->where('magic_skill_id', 26)->where('effect_id', 7)->update(['duration_seconds' => 15]);
        $blessingIiEffect = $this->ensureEffect('Благословение II', [
            ['type' => 'hp_max', 'value' => 15, 'is_percent' => true], ['type' => 'mp_max', 'value' => 15, 'is_percent' => true],
        ], $now);
        $blessingIiiEffect = $this->ensureEffect('Благословение III', [
            ['type' => 'hp_max', 'value' => 20, 'is_percent' => true], ['type' => 'mp_max', 'value' => 20, 'is_percent' => true],
        ], $now);
        $blessingIiId = $this->ensureSkill('blessing_ii', ['ru' => 'Благословение II', 'uk' => 'Благословення II'], 3, 'buff', [
            'mana_cost' => 55, 'cooldown' => 28, 'target_type' => 'all',
        ], $now);
        $blessingIiiId = $this->ensureSkill('blessing_iii', ['ru' => 'Благословение III', 'uk' => 'Благословення III'], 4, 'buff', [
            'mana_cost' => 75, 'cooldown' => 30, 'target_type' => 'all',
        ], $now);
        $this->ensureSkillEffect($blessingIiId, $blessingIiEffect, 100, 18, $now);
        $this->ensureSkillEffect($blessingIiiId, $blessingIiiEffect, 100, 20, $now);

        // 5) Регенерация: id=8 (value_per_tick=15) занижается до ранга I, новые
        // эффекты (value_per_tick 20/30) под II/III. tick_interval не трогаем (1).
        DB::table('effects')->where('id', 8)->update(['value_per_tick' => 12]);
        DB::table('magic_skills')->where('id', 29)->update(['cooldown' => 20]);
        DB::table('magic_skill_effects')->where('magic_skill_id', 29)->where('effect_id', 8)->update(['duration_seconds' => 8]);
        $regenIiEffect = $this->ensureRegenEffect('Регенерация II', 20, $now);
        $regenIiiEffect = $this->ensureRegenEffect('Регенерация III', 30, $now);
        $regenIiId = $this->ensureSkill('regen_skill_ii', ['ru' => 'Регенерация II', 'uk' => 'Регенерація II'], 3, 'buff', [
            'mana_cost' => 32, 'cooldown' => 25, 'target_type' => 'all',
        ], $now);
        $regenIiiId = $this->ensureSkill('regen_skill_iii', ['ru' => 'Регенерация III', 'uk' => 'Регенерація III'], 4, 'buff', [
            'mana_cost' => 50, 'cooldown' => 30, 'target_type' => 'all',
        ], $now);
        $this->ensureSkillEffect($regenIiId, $regenIiEffect, 100, 10, $now);
        $this->ensureSkillEffect($regenIiiId, $regenIiiEffect, 100, 12, $now);

        // 6) Требования (skill_id=27 «Колдовство» + int/wis, акцент на мудрость)
        // и книги — всем рангам, включая уже существующие ранги I и пассивку.
        $rankIIds = [27, 24, 25, 26, 29, 3]; // ранг I + пассивка «Атака ястреба»
        $rankIiIds = [$midHealId, $warCryIiId, $stoneSkinIiId, $blessingIiId, $regenIiId];
        $rankIiiIds = [28, $warCryIiiId, $stoneSkinIiiId, $blessingIiiId, $regenIiiId];

        foreach ($rankIIds as $id) {
            $this->ensureRequirement($id, 0, 1, 1);
            $this->ensureBook($id, 'common', 100000, [1665, 1666, 1667, 1668, 1669, 1686]);
        }
        foreach ($rankIiIds as $id) {
            $this->ensureRequirement($id, 35, 20, 30);
            $this->ensureBook($id, 'rare', 300000, [1672, 1673, 1674, 1675, 1676, 1677]);
        }
        foreach ($rankIiiIds as $id) {
            $this->ensureRequirement($id, 70, 35, 55);
            $this->ensureBook($id, 'epic', 500000, [1676, 1677, 1688, 1689]);
        }
    }

    /** @param array<string, mixed> $fields */
    private function ensureSkill(string $slug, array $name, int $tier, string $type, array $fields, Carbon $now): int
    {
        $existing = DB::table('magic_skills')->where('slug', $slug)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('magic_skills')->insertGetId(array_merge([
            'slug' => $slug,
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
            'image' => null,
            'level' => $tier,
            'skill_id' => self::MAGIC_SKILL_ID,
            'type' => $type,
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0,
            'base_healing' => 0,
            'is_passive' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $fields));
    }

    /** @param list<array{type:string,value:int,is_percent:bool}> $statModifiers */
    private function ensureEffect(string $nameRu, array $statModifiers, Carbon $now): int
    {
        $existing = DB::table('effects')->where('name->ru', $nameRu)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('effects')->insertGetId([
            'slug' => 'buff-'.str()->slug($nameRu),
            'name' => json_encode(['ru' => $nameRu, 'uk' => $nameRu], JSON_UNESCAPED_UNICODE),
            'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
            'type' => 'buff',
            'chance' => 100,
            'is_stackable' => 0,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'stat_modifiers' => json_encode($statModifiers),
            'is_dispellable' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function ensureRegenEffect(string $nameRu, int $valuePerTick, Carbon $now): int
    {
        $existing = DB::table('effects')->where('name->ru', $nameRu)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('effects')->insertGetId([
            'slug' => 'buff-'.str()->slug($nameRu),
            'name' => json_encode(['ru' => $nameRu, 'uk' => $nameRu], JSON_UNESCAPED_UNICODE),
            'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
            'type' => 'buff',
            'active_type' => 'regen',
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
    private function ensureBook(int $skillId, string $rarity, int $price, array $chestIds): void
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
            'gathering_double_chance_percent' => 0, 'rarity' => $rarity, 'upgrade_gold_cost' => 0,
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
        $slugs = ['mid_heal', 'battle_cry_ii', 'battle_cry_iii', 'stone_skin_ii', 'stone_skin_iii', 'blessing_ii', 'blessing_iii', 'regen_skill_ii', 'regen_skill_iii'];
        $newSkillIds = DB::table('magic_skills')->whereIn('slug', $slugs)->pluck('id');

        $bookItemIds = DB::table('magic_skill_books')->whereIn('magic_skill_id', $newSkillIds)->pluck('share_item_id');
        DB::table('share_item_has_items')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('share_items')->whereIn('id', $bookItemIds)->delete();

        // Книги рангов I/III, добавленные этой же миграцией поверх уже существовавших скиллов (27,28,24,25,26,29,3).
        $oldRankBookIds = DB::table('magic_skill_books')->whereIn('magic_skill_id', [27, 28, 24, 25, 26, 29, 3])->pluck('share_item_id');
        DB::table('share_item_has_items')->whereIn('share_item_id', $oldRankBookIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $oldRankBookIds)->delete();
        DB::table('share_items')->whereIn('id', $oldRankBookIds)->delete();

        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', [27, 28, 24, 25, 26, 29, 3])->delete();
        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', $newSkillIds)->delete();

        DB::table('magic_skill_effects')->whereIn('magic_skill_id', $newSkillIds)->delete();
        DB::table('magic_skills')->whereIn('id', $newSkillIds)->delete();

        DB::table('effects')->whereIn('name->ru', [
            'Боевой клич II', 'Боевой клич III', 'Каменная кожа II', 'Каменная кожа III',
            'Благословение II', 'Благословение III', 'Регенерация II', 'Регенерация III',
        ])->delete();
    }
};
