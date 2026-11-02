<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Продолжает «Благословение» с 3 рангов до 6 (I-III: +5/+8/+12% к HP и MP
 * max, мана 35/55/75, кд/длительность 150/120 → 180/150 → 210/180,
 * требования «Колдовство»+int+wis 0/1/1 → 35/20/30 → 70/35/55, см.
 * 2026_10_27_000001). IV-VI продолжают тот же ускоряющийся шаг % (+3,+4 →
 * +5,+6,+7) и ту же лестницу кд/длительности/маны/требований/цен, уже
 * переиспользованную для «Регенерации» и «Каменной кожи»
 * (2026_10_30_000001/000002) — все три линии баффов делят одну и ту же
 * прогрессию инвестиций мага.
 */
return new class extends Migration
{
    private const MAGIC_SKILL_ID = 27; // «Колдовство»

    public function up(): void
    {
        $now = now();

        $prevId = (int) DB::table('magic_skills')->where('slug', 'blessing_iii')->value('id'); // 86

        $ranks = [
            [
                'slug' => 'blessing_iv', 'roman' => 'IV', 'tier' => 5,
                'mana' => 95, 'cooldown' => 240, 'duration' => 210, 'percent' => 17,
                'reqSkill' => 105, 'reqInt' => 50, 'reqWis' => 80,
                'price' => 850000, 'chestIds' => [1672, 1673, 1674, 1675, 1676, 1677],
                'descRu' => 'Благословение, льющееся из самого сердца небес — тело и дух цели наполняются силой, которой позавидовали бы герои легенд.',
                'descUk' => 'Благословення, що ллється з самого серця небес — тіло і дух цілі наповнюються силою, якій позаздрили б герої легенд.',
            ],
            [
                'slug' => 'blessing_v', 'roman' => 'V', 'tier' => 6,
                'mana' => 115, 'cooldown' => 270, 'duration' => 240, 'percent' => 23,
                'reqSkill' => 140, 'reqInt' => 65, 'reqWis' => 105,
                'price' => 1450000, 'chestIds' => [1675, 1676, 1677, 1689],
                'descRu' => 'Свет благословения становится осязаемым щитом из чистой жизненной силы, укрывающим цель с головы до ног.',
                'descUk' => 'Світло благословення стає відчутним щитом із чистої життєвої сили, що вкриває ціль з голови до ніг.',
            ],
            [
                'slug' => 'blessing_vi', 'roman' => 'VI', 'tier' => 7,
                'mana' => 135, 'cooldown' => 300, 'duration' => 270, 'percent' => 30,
                'reqSkill' => 175, 'reqInt' => 80, 'reqWis' => 130,
                'price' => 2450000, 'chestIds' => [1677, 1689],
                'descRu' => 'Высшая милость богов, разлитая по венам цели — сама смерть на миг замирает перед сиянием этого благословения.',
                'descUk' => 'Найвища милість богів, розлита по венах цілі — сама смерть на мить завмирає перед сяйвом цього благословення.',
            ],
        ];

        foreach ($ranks as $rank) {
            $description = json_encode(['ru' => $rank['descRu'], 'uk' => $rank['descUk']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            $skillId = $this->ensureSkill($rank['slug'], [
                'ru' => "Благословение {$rank['roman']}", 'uk' => "Благословення {$rank['roman']}",
            ], $rank['tier'], $description, [
                'mana_cost' => $rank['mana'], 'cooldown' => $rank['cooldown'],
                'target_type' => 'all', 'upgrades_skill_id' => $prevId,
            ], $now);

            $effectId = $this->ensureEffect("Благословение {$rank['roman']}", [
                ['type' => 'hp_max', 'value' => $rank['percent'], 'is_percent' => true],
                ['type' => 'mp_max', 'value' => $rank['percent'], 'is_percent' => true],
            ], $now);
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

    /** @param list<array{type:string,value:int,is_percent:bool}> $statModifiers */
    private function ensureEffect(string $nameRu, array $statModifiers, Carbon $now): int
    {
        $existing = DB::table('effects')->where('name->ru', $nameRu)->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return DB::table('effects')->insertGetId([
            'slug' => 'buff-'.str()->slug($nameRu),
            'name' => json_encode(['ru' => $nameRu, 'uk' => str_replace('Благословение', 'Благословення', $nameRu)], JSON_UNESCAPED_UNICODE),
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
        $slugs = ['blessing_iv', 'blessing_v', 'blessing_vi'];
        $skillIds = DB::table('magic_skills')->whereIn('slug', $slugs)->pluck('id');

        $bookItemIds = DB::table('magic_skill_books')->whereIn('magic_skill_id', $skillIds)->pluck('share_item_id');
        DB::table('share_item_has_items')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('share_items')->whereIn('id', $bookItemIds)->delete();

        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', $skillIds)->delete();
        DB::table('magic_skill_effects')->whereIn('magic_skill_id', $skillIds)->delete();
        DB::table('magic_skills')->whereIn('id', $skillIds)->delete();

        DB::table('effects')->whereIn('name->ru', ['Благословение IV', 'Благословение V', 'Благословение VI'])->delete();
    }
};
