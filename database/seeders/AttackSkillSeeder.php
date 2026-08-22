<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Атакующие заклинания, зеркалящие 3 тира оружия (StarterEquipmentSeeder lvl1,
 * TIER2_SLOT_LEVEL['weapon']=20, HighTierEquipmentSeeder::WEAPON_LEVEL=55):
 * тот же уровень открытия и тот же диапазон урона
 * (EquipmentStatFormulas::weaponDamage), умноженный на SPELL_DAMAGE_MULTIPLIER —
 * «как с учётом двух оружий в руках, но чуть меньше» (DUAL_WIELD_DAMAGE_SHARE=0.75
 * даёт ×1.5 суммарно за 2 удара, спелл — ×1.3 одним хитом).
 *
 * Изучение/экипировка гейтится через magic_skill_requirements (интеллект,
 * мудрость, навык «Колдовство» — см. MagicSkillRequirementService,
 * UpdateEquippedMagicSkills). Навык «Колдовство» качается за каждое успешное
 * применение (MagicAttackStrategy → AttackService::gainExperienceSkill), урон
 * дополнительно растёт от интеллекта (PlayerStatFormulas::intelligenceDamagePercent).
 */
class AttackSkillSeeder extends Seeder
{
    private const PLAYER_ID = 1;

    private const SPELL_SKILL_NAME = 'Колдовство';

    public function run(): void
    {
        if (MagicSkill::where('slug', 'fire_spark')->exists()) {
            $this->command->info('AttackSkillSeeder: уже существует, пропускаем.');

            return;
        }

        DB::transaction(function () {
            $spellSkillId = (int) Skill::where('name', self::SPELL_SKILL_NAME)->value('id');

            if ($spellSkillId === 0) {
                $this->command->warn('AttackSkillSeeder: навык «Колдовство» не найден — прогоните миграцию 2026_08_22_120000.');

                return;
            }

            $fireSpark = MagicSkill::create([
                'name' => 'Огненная искра',
                'slug' => 'fire_spark',
                'description' => 'Небольшой сгусток пламени, срывающийся с пальцев. Первое боевое заклинание, доступное с самого начала пути.',
                'type' => 'attack',
                'target_type' => 'enemy',
                'skill_id' => $spellSkillId,
                'mana_cost' => 8,
                'min_damage' => 4,
                'max_damage' => 7,
                'base_healing' => 0,
                'cooldown' => 0,
                'level' => 1,
                'is_passive' => false,
            ]);
            $this->addRequirements($fireSpark->id, [
                [MagicSkillRequirementType::LEVEL, null, null, 1],
                [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 3],
                [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 2],
            ]);

            $fireball = MagicSkill::create([
                'name' => 'Огненный залп',
                'slug' => 'flame_barrage',
                'description' => 'Плотный огненный снаряд, взрывающийся при попадании. Заклинание для окрепшего заклинателя, наравне с оружием второго тира.',
                'type' => 'attack',
                'target_type' => 'enemy',
                'skill_id' => $spellSkillId,
                'mana_cost' => 25,
                'min_damage' => 21,
                'max_damage' => 30,
                'base_healing' => 0,
                'cooldown' => 0,
                'level' => 20,
                'is_passive' => false,
            ]);
            $this->addRequirements($fireball->id, [
                [MagicSkillRequirementType::LEVEL, null, null, 20],
                [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 35],
                [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 20],
                [MagicSkillRequirementType::SKILL, null, $spellSkillId, 5],
            ]);

            $vortex = MagicSkill::create([
                'name' => 'Испепеляющий вихрь',
                'slug' => 'incinerating_vortex',
                'description' => 'Смерч из пламени и раскалённого пепла, сжигающий всё на своём пути. Вершина боевой магии — под стать оружию третьего тира.',
                'type' => 'attack',
                'target_type' => 'enemy',
                'skill_id' => $spellSkillId,
                'mana_cost' => 55,
                'min_damage' => 59,
                'max_damage' => 87,
                'base_healing' => 0,
                'cooldown' => 0,
                'level' => 55,
                'is_passive' => false,
            ]);
            $this->addRequirements($vortex->id, [
                [MagicSkillRequirementType::LEVEL, null, null, 55],
                [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 90],
                [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 45],
                [MagicSkillRequirementType::SKILL, null, $spellSkillId, 15],
            ]);

            $player = Player::find(self::PLAYER_ID);

            if (! $player) {
                $this->command->warn('AttackSkillSeeder: игрок id=1 не найден, скиллы не выданы.');

                return;
            }

            foreach ([$fireSpark->id, $fireball->id, $vortex->id] as $skillId) {
                $player->magicSkills()->attach($skillId, [
                    'is_equipped' => false,
                    'cooldown_end_at' => null,
                ]);
            }

            $this->command->info(sprintf(
                'AttackSkillSeeder: создано 3 атакующих заклинания → выданы игроку id=%d (%s).',
                self::PLAYER_ID,
                $player->name,
            ));
        });
    }

    /** @param  array<int, array{0: MagicSkillRequirementType, 1: ?PlayerStatKey, 2: ?int, 3: int}>  $requirements */
    private function addRequirements(int $magicSkillId, array $requirements): void
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
