<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Стартовый набор книг заклинаний — см. спеку, раздел «Стартовый набор — 7 книг».
 * 3 книги оборачивают уже существующие атакующие заклинания (id из AttackSkillSeeder
 * прошлой сессии), 4 — новые (DoT/heal/buff/debuff), по одному архетипу магии на
 * первый релиз.
 *
 * ShareItem::$fillable не включает is_sell/is_give/price (см. ShareItem.php) —
 * makeBook() поэтому создаёт ShareItem через прямое присвоение свойств
 * (new ShareItem; ...; save()), как это уже делает ItemsSeeder.php, а не через
 * create().
 */
class MagicBookStarterSeeder extends Seeder
{
    private const SPELL_SKILL_NAME = 'Колдовство';

    public function run(): void
    {
        if (ShareItem::where('name', 'Книга: Огненная искра')->exists()) {
            $this->command->info('MagicBookStarterSeeder: уже существует, пропускаем.');

            return;
        }

        DB::transaction(function () {
            $spellSkillId = (int) Skill::where('name', self::SPELL_SKILL_NAME)->value('id');

            if ($spellSkillId === 0) {
                $this->command->warn('MagicBookStarterSeeder: навык «Колдовство» не найден — прогоните миграцию 2026_08_22_120000 и AttackSkillSeeder сначала.');

                return;
            }

            $this->bookifyExistingAttackSpells();
            $this->createDotSpell($spellSkillId);
            $this->createHealSpell($spellSkillId);
            $this->createBuffSpell($spellSkillId);
            $this->createDebuffSpell($spellSkillId);

            $this->command->info('MagicBookStarterSeeder: 7 книг созданы.');
        });
    }

    private function bookifyExistingAttackSpells(): void
    {
        // power_coefficient=0.30 было стартовым ориентиром спеки, снижено до 0.15 по
        // итогам battle:simulate-pve (Task 14) — без снижения магия била в 1.8-2.3 раза
        // быстрее меле-билдов на 20/55/100 lvl (иммунна к броне/уворту, а монстры не
        // имеют magic_resistance по дизайну — см. Monster::getMagicResistance()).
        $spells = [
            'fire_spark' => ['name' => 'Книга: Огненная искра', 'power_coefficient' => 0.15],
            'flame_barrage' => ['name' => 'Книга: Огненный залп', 'power_coefficient' => 0.15],
            'incinerating_vortex' => ['name' => 'Книга: Испепеляющий вихрь', 'power_coefficient' => 0.15],
        ];

        foreach ($spells as $slug => $data) {
            $skill = MagicSkill::where('slug', $slug)->first();

            if ($skill === null) {
                $this->command->warn(sprintf('MagicBookStarterSeeder: заклинание %s не найдено, пропускаю книгу.', $slug));

                continue;
            }

            $skill->update(['power_coefficient' => $data['power_coefficient']]);

            $this->makeBook($data['name'], $skill, priceMultiplier: 200);
        }
    }

    private function createDotSpell(int $spellSkillId): void
    {
        // Переиспользуем уже существующую строку effects.slug='burn' (её создаёт
        // GenerateSeed для монстрового дебаффа), а НЕ заводим отдельный слаг:
        // BattleEffectService::applyEffectToMonster() резолвит ActiveEffectType
        // строго по слагу эффекта, и любой слаг вне ActiveEffectType (прежний
        // 'burn_spell') давал MonsterActiveEffect.type = NULL — такая строка не
        // тикала и не истекала, заклинание было мёртвым контентом.
        //
        // Строка теперь обслуживает две подсистемы сразу, поэтому её числовые
        // поля не переписываем: value_per_tick всё равно перекрывается
        // tickValueOverride в момент каста (MagicAttackStrategy →
        // applyEffectToMonster), а duration/tick_interval/chance продолжают
        // обслуживать монстровый дебафф. Обновляем только описание — под
        // двойное назначение. Если строки ещё нет (чистое окружение), создаём
        // её по числам из спеки заклинания.
        $effect = Effect::firstOrNew(['slug' => 'burn']);

        if (! $effect->exists) {
            $effect->fill([
                'name' => 'Ожог',
                'type' => 'debuff',
                'duration' => 6,
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 2,
                'value_per_tick' => 0, // перекрывается MagicHitCalculator-расчётом при касте
                'stat_modifiers' => null,
                'is_dispellable' => true,
            ]);
        }

        $effect->description = 'Периодический урон огнём';
        $effect->save();

        $skill = MagicSkill::create([
            'name' => 'Тлеющая рана',
            'slug' => 'smoldering_wound',
            'description' => 'Поджигает цель — урон каждые 2 секунды в течение 6 секунд.',
            'type' => 'attack',
            'target_type' => 'enemy',
            'skill_id' => $spellSkillId,
            'mana_cost' => 15,
            'min_damage' => 2,
            'max_damage' => 4,
            'power_coefficient' => 0.12,
            'base_healing' => 0,
            'cooldown' => 8,
            'level' => 10,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 10],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 12],
            [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 8],
        ]);

        $this->makeBook('Книга: Тлеющая рана', $skill);
    }

    private function createHealSpell(int $spellSkillId): void
    {
        $skill = MagicSkill::create([
            'name' => 'Малое исцеление плоти',
            'slug' => 'minor_flesh_mending',
            'description' => 'Восстанавливает здоровье, усиливается интеллектом заклинателя.',
            'type' => 'heal',
            'target_type' => 'all',
            'skill_id' => $spellSkillId,
            'mana_cost' => 18,
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0.35,
            'base_healing' => 40,
            'cooldown' => 20,
            'level' => 5,
            'is_passive' => false,
        ]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 5],
            [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 10],
        ]);

        $this->makeBook('Книга: Малое исцеление плоти', $skill);
    }

    private function createBuffSpell(int $spellSkillId): void
    {
        $effect = Effect::create([
            'name' => 'Прилив магии',
            'slug' => 'arcane_surge',
            'type' => 'buff',
            'description' => '+25% к магической атаке на 15 секунд',
            'duration' => 15,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'value_per_tick' => 0,
            'stat_modifiers' => [
                ['type' => 'magic_attack', 'value' => 25, 'is_percent' => true],
            ],
            'is_dispellable' => true,
        ]);

        $skill = MagicSkill::create([
            'name' => 'Прилив магии',
            'slug' => 'arcane_surge_skill',
            'description' => 'Временно усиливает магическую атаку заклинателя на 25%.',
            'type' => 'buff',
            'target_type' => 'self',
            'skill_id' => $spellSkillId,
            'mana_cost' => 22,
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0.0,
            'base_healing' => 0,
            'cooldown' => 30,
            'level' => 15,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 15],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 18],
        ]);

        $this->makeBook('Книга: Прилив магии', $skill);
    }

    private function createDebuffSpell(int $spellSkillId): void
    {
        $effect = Effect::create([
            'name' => 'Разъедающая ржавчина',
            'slug' => 'corroding_rust',
            'type' => 'debuff',
            'description' => 'Снижает броню цели на 10 секунд',
            'duration' => 10,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'value_per_tick' => 0,
            'stat_modifiers' => [
                ['type' => 'armor', 'value' => -15, 'is_percent' => false],
            ],
            'is_dispellable' => true,
        ]);

        $skill = MagicSkill::create([
            'name' => 'Разъедающая ржавчина',
            'slug' => 'corroding_rust_skill',
            'description' => 'Разъедает броню цели, снижая защиту на 10 секунд.',
            'type' => 'attack',
            'target_type' => 'enemy',
            'skill_id' => $spellSkillId,
            'mana_cost' => 20,
            'min_damage' => 3,
            'max_damage' => 6,
            'power_coefficient' => 0.15,
            'base_healing' => 0,
            'cooldown' => 12,
            'level' => 18,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 18],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 20],
            [MagicSkillRequirementType::SKILL, null, $spellSkillId, 3],
        ]);

        $this->makeBook('Книга: Разъедающая ржавчина', $skill);
    }

    private function makeBook(string $name, MagicSkill $skill, int $priceMultiplier = 150): void
    {
        // ShareItem::$fillable не включает is_sell/is_give/price (существуют в БД,
        // но не в $fillable — см. ShareItem.php) — присваиваем напрямую,
        // как это уже делает ItemsSeeder.php.
        $book = new ShareItem;
        $book->name = $name;
        $book->type = ShareItemType::BOOK;
        $book->description = sprintf('Обучает заклинанию «%s». Расходуется при изучении.', $skill->name);
        $book->is_sell = true;
        $book->is_give = true;
        $book->price = $priceMultiplier * max(1, $skill->level);
        $book->save();

        MagicSkillBook::create(['share_item_id' => $book->id, 'magic_skill_id' => $skill->id]);
    }

    /** @param  array<int, array{0: MagicSkillRequirementType, 1: ?PlayerStatKey, 2: ?int, 3: int}>  $requirements */
    private function addRequirements(int $magicSkillId, int $spellSkillId, array $requirements): void
    {
        foreach ($requirements as [$type, $statKey, $skillId, $minValue]) {
            DB::table('magic_skill_requirements')->insert([
                'magic_skill_id' => $magicSkillId,
                'type' => $type->value,
                'stat_key' => $statKey?->value,
                'skill_id' => $skillId,
                'min_value' => $minValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
