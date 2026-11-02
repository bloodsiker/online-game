<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Боевой ростер заклинаний: чинит 2 уже существующих (Огненный шар,
 * Ледяная стрела — power_coefficient=0, cooldown=0) и добавляет 10 новых
 * атак/дебаффов на лестнице уровня «Колдовства» (skill_id=27) 0/15/35/70/110.
 * Гейт — требование type=skill (а не устаревший type=level, см.
 * MagicSkillRequirementService), т.к. именно это проверяется при изучении.
 * Каждому скиллу — книга для изучения (их не было вообще ни у одного скилла)
 * с дропом из тех же сундуков, что уже используются под осколки/печати.
 */
return new class extends Migration
{
    private const MAGIC_SKILL_ID = 27; // «Колдовство»

    /** @var list<array{slug:string,name:array{ru:string,uk:string},type:string,mana:int,minDmg:int,maxDmg:int,cooldown:int,effectId:?int,chance:int,duration:int,tier:int,minSkillLevel:int}> */
    private const NEW_SKILLS = [
        [
            'slug' => 'lightning-bolt-t2', 'name' => ['ru' => 'Молния', 'uk' => 'Блискавка'],
            'type' => 'attack', 'mana' => 18, 'minDmg' => 8, 'maxDmg' => 12, 'cooldown' => 1,
            'effectId' => 2, 'chance' => 15, 'duration' => 3, 'tier' => 2, 'minSkillLevel' => 15,
        ],
        [
            'slug' => 'weakness-shock-t2', 'name' => ['ru' => 'Разряд слабости', 'uk' => 'Розряд слабкості'],
            'type' => 'debuff', 'mana' => 15, 'minDmg' => 0, 'maxDmg' => 0, 'cooldown' => 12,
            'effectId' => 19, 'chance' => 100, 'duration' => 10, 'tier' => 2, 'minSkillLevel' => 15,
        ],
        [
            'slug' => 'fire-volley-t3', 'name' => ['ru' => 'Огненный залп', 'uk' => 'Огненний залп'],
            'type' => 'attack', 'mana' => 32, 'minDmg' => 22, 'maxDmg' => 30, 'cooldown' => 1,
            'effectId' => 1, 'chance' => 65, 'duration' => 10, 'tier' => 3, 'minSkillLevel' => 35,
        ],
        [
            'slug' => 'venom-bite-t3', 'name' => ['ru' => 'Укус яда', 'uk' => 'Укус отрути'],
            'type' => 'attack', 'mana' => 28, 'minDmg' => 18, 'maxDmg' => 24, 'cooldown' => 1,
            'effectId' => 15, 'chance' => 40, 'duration' => 10, 'tier' => 3, 'minSkillLevel' => 35,
        ],
        [
            'slug' => 'frost-spike-t3', 'name' => ['ru' => 'Ледяной шип', 'uk' => 'Крижаний шип'],
            'type' => 'debuff', 'mana' => 22, 'minDmg' => 0, 'maxDmg' => 0, 'cooldown' => 12,
            'effectId' => 18, 'chance' => 100, 'duration' => 10, 'tier' => 3, 'minSkillLevel' => 35,
        ],
        [
            'slug' => 'blood-blade-t4', 'name' => ['ru' => 'Кровавый клинок', 'uk' => 'Кривавий клинок'],
            'type' => 'attack', 'mana' => 60, 'minDmg' => 70, 'maxDmg' => 90, 'cooldown' => 1,
            'effectId' => 14, 'chance' => 35, 'duration' => 10, 'tier' => 4, 'minSkillLevel' => 70,
        ],
        [
            'slug' => 'armor-break-t4', 'name' => ['ru' => 'Разрушение доспеха', 'uk' => 'Руйнування обладунку'],
            'type' => 'debuff', 'mana' => 40, 'minDmg' => 0, 'maxDmg' => 0, 'cooldown' => 15,
            'effectId' => 17, 'chance' => 100, 'duration' => 12, 'tier' => 4, 'minSkillLevel' => 70,
        ],
        [
            'slug' => 'paralyzing-gaze-t4', 'name' => ['ru' => 'Парализующий взгляд', 'uk' => 'Паралізуючий погляд'],
            'type' => 'debuff', 'mana' => 45, 'minDmg' => 0, 'maxDmg' => 0, 'cooldown' => 20,
            'effectId' => 3, 'chance' => 50, 'duration' => 5, 'tier' => 4, 'minSkillLevel' => 70,
        ],
        [
            'slug' => 'incinerating-vortex-t5', 'name' => ['ru' => 'Испепеляющий вихрь', 'uk' => 'Спопеляючий вихор'],
            'type' => 'attack', 'mana' => 85, 'minDmg' => 95, 'maxDmg' => 125, 'cooldown' => 1,
            'effectId' => 1, 'chance' => 80, 'duration' => 12, 'tier' => 5, 'minSkillLevel' => 110,
        ],
        [
            'slug' => 'cry-of-terror-t5', 'name' => ['ru' => 'Клич ужаса', 'uk' => 'Клич жаху'],
            'type' => 'debuff', 'mana' => 70, 'minDmg' => 0, 'maxDmg' => 0, 'cooldown' => 25,
            'effectId' => 48, 'chance' => 100, 'duration' => 10, 'tier' => 5, 'minSkillLevel' => 110,
        ],
    ];

    /** @var array<int, array{rarity:string, price:int, chestIds:list<int>}> тир → редкость книги/цена/сундуки дропа */
    private const TIER_BOOK_META = [
        1 => ['rarity' => 'common', 'price' => 100000, 'chestIds' => [1665, 1666, 1667, 1668, 1669, 1686]],
        2 => ['rarity' => 'uncommon', 'price' => 175000, 'chestIds' => [1670, 1671, 1672, 1673]],
        3 => ['rarity' => 'rare', 'price' => 300000, 'chestIds' => [1672, 1673, 1674, 1675, 1676, 1677]],
        4 => ['rarity' => 'epic', 'price' => 500000, 'chestIds' => [1676, 1677, 1688, 1689]],
        5 => ['rarity' => 'legendary', 'price' => 850000, 'chestIds' => [1677, 1689]],
    ];

    public function up(): void
    {
        $now = now();

        // 1) Чиним 2 уже существующих скилла: даём силу от стат (power_coefficient),
        // снимаем кулдаун атаки до 1с, привязываем к «Колдовству».
        DB::table('magic_skills')->where('id', 1)->update([
            'skill_id' => self::MAGIC_SKILL_ID, 'power_coefficient' => 0.30, 'cooldown' => 1, 'mana_cost' => 5,
        ]);
        DB::table('magic_skills')->where('id', 2)->update([
            'skill_id' => self::MAGIC_SKILL_ID, 'power_coefficient' => 0.30, 'cooldown' => 1, 'mana_cost' => 7,
        ]);
        $this->ensureRequirement(1, 0);
        $this->ensureRequirement(2, 0);
        $this->ensureBook(1, tier: 1);
        $this->ensureBook(2, tier: 1);

        // 2) Остальные реальные скиллы игрока (не клановые баффы) тоже привязываем
        // к «Колдовству», чтобы применение качало навык — просто без геймплейного
        // требования на изучение (они уже выучены у части игроков вручную).
        DB::table('magic_skills')->whereIn('id', [3, 24, 25, 26, 27, 28, 29])->update(['skill_id' => self::MAGIC_SKILL_ID]);

        // 3) Новые боевые скиллы.
        foreach (self::NEW_SKILLS as $skill) {
            $skillId = DB::table('magic_skills')->where('slug', $skill['slug'])->value('id');

            if ($skillId === null) {
                $skillId = DB::table('magic_skills')->insertGetId([
                    'slug' => $skill['slug'],
                    'name' => json_encode($skill['name'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(['ru' => '', 'uk' => ''], JSON_UNESCAPED_UNICODE),
                    'image' => null,
                    'level' => $skill['tier'],
                    'skill_id' => self::MAGIC_SKILL_ID,
                    'type' => $skill['type'],
                    'mana_cost' => $skill['mana'],
                    'min_damage' => $skill['minDmg'],
                    'max_damage' => $skill['maxDmg'],
                    'power_coefficient' => $skill['type'] === 'attack' ? 0.30 : 0.0,
                    'base_healing' => 0,
                    'cooldown' => $skill['cooldown'],
                    'target_type' => 'enemy',
                    'is_passive' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $effectExists = DB::table('magic_skill_effects')
                ->where('magic_skill_id', $skillId)->where('effect_id', $skill['effectId'])->exists();
            if (! $effectExists) {
                DB::table('magic_skill_effects')->insert([
                    'magic_skill_id' => $skillId, 'effect_id' => $skill['effectId'],
                    'chance' => $skill['chance'], 'duration_seconds' => $skill['duration'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $this->ensureRequirement($skillId, $skill['minSkillLevel']);
            $this->ensureBook($skillId, $skill['tier']);
        }
    }

    private function ensureRequirement(int $skillId, int $minSkillLevel): void
    {
        $exists = DB::table('magic_skill_requirements')
            ->where('magic_skill_id', $skillId)->where('type', 'skill')->where('skill_id', self::MAGIC_SKILL_ID)
            ->exists();

        if (! $exists) {
            DB::table('magic_skill_requirements')->insert([
                'magic_skill_id' => $skillId, 'type' => 'skill', 'skill_id' => self::MAGIC_SKILL_ID,
                'min_value' => $minSkillLevel, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function ensureBook(int $skillId, int $tier): void
    {
        $alreadyLinked = DB::table('magic_skill_books')->where('magic_skill_id', $skillId)->exists();
        if ($alreadyLinked) {
            return;
        }

        $skillName = DB::table('magic_skills')->where('id', $skillId)->value('name');
        $skillNameRu = json_decode((string) $skillName, true)['ru'];
        $skillNameUk = json_decode((string) $skillName, true)['uk'];
        $meta = self::TIER_BOOK_META[$tier];
        $now = now();

        $bookItemId = DB::table('share_items')->insertGetId([
            'type' => 'book',
            'name' => json_encode(['ru' => 'Гримуар: «'.$skillNameRu.'»', 'uk' => 'Гримуар: «'.$skillNameUk.'»'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'ru' => 'Прочитав этот гримуар, вы навсегда изучите заклинание «'.$skillNameRu.'».',
                'uk' => 'Прочитавши цей гримуар, ви назавжди вивчите заклинання «'.$skillNameUk.'».',
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
            'price' => $meta['price'],
            'diamond_price' => 0,
            'break_crystal' => 0,
            'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0,
            'rarity' => $meta['rarity'],
            'upgrade_gold_cost' => 0,
            'drop_direct_to_backpack' => 0,
            'created_at' => $now,
            'updated_at' => $now,
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
        $slugs = array_column(self::NEW_SKILLS, 'slug');
        $slugs[] = 'fireball';
        $slugs[] = 'ice_arrow';

        $bookItemIds = DB::table('magic_skill_books')
            ->join('magic_skills', 'magic_skills.id', '=', 'magic_skill_books.magic_skill_id')
            ->whereIn('magic_skills.slug', $slugs)
            ->pluck('magic_skill_books.share_item_id');

        DB::table('share_item_has_items')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('magic_skill_books')->whereIn('share_item_id', $bookItemIds)->delete();
        DB::table('share_items')->whereIn('id', $bookItemIds)->delete();

        $newSkillSlugs = array_column(self::NEW_SKILLS, 'slug');
        $newSkillIds = DB::table('magic_skills')->whereIn('slug', $newSkillSlugs)->pluck('id');
        DB::table('magic_skill_requirements')->whereIn('magic_skill_id', $newSkillIds)->delete();
        DB::table('magic_skill_effects')->whereIn('magic_skill_id', $newSkillIds)->delete();
        DB::table('magic_skills')->whereIn('id', $newSkillIds)->delete();

        DB::table('magic_skill_requirements')->where('magic_skill_id', 1)->where('type', 'skill')->where('min_value', 0)->delete();
        DB::table('magic_skill_requirements')->where('magic_skill_id', 2)->where('type', 'skill')->where('min_value', 0)->delete();
    }
};
