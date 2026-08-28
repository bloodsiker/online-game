<?php

namespace Database\Seeders;

use App\Modules\Clan\Domain\Enums\ClanSkillEffectType;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Models\ClanSkillLevel;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use Illuminate\Database\Seeder;

class ClanSkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'name' => 'Клановая мощь',
                'description' => 'Тренировки под руководством клана повышают силу всех членов.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 1,
                'effect_type' => ClanSkillEffectType::STRENGTH,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name' => 'Клановая стойкость',
                'description' => 'Единство клана укрепляет тело, увеличивая максимальный запас здоровья.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 2,
                'effect_type' => ClanSkillEffectType::HP_MAX,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 20],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 50],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 90],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 140],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 200],
                ],
            ],
            [
                'name' => 'Клановое чутьё',
                'description' => 'Обмен боевым опытом между соратниками обостряет чутьё каждого члена клана, повышая интуицию.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 3,
                'effect_type' => ClanSkillEffectType::INTUITION,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name' => 'Клановая защита',
                'description' => 'Братство клана учит защищаться, увеличивая ловкость.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 4,
                'effect_type' => ClanSkillEffectType::AGILITY,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name' => 'Клановая эрудиция',
                'description' => 'Библиотека клана открыта для всех — совместное изучение трудов повышает интеллект членов.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 5,
                'effect_type' => ClanSkillEffectType::INTELLIGENCE,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name' => 'Клановое благословение',
                'description' => 'Общие ритуалы и медитации укрепляют духовную связь клана, повышая мудрость.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 6,
                'effect_type' => ClanSkillEffectType::WISDOM,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name' => 'Клановый источник',
                'description' => 'Своя закрытая линия маны, проведённая через земли клана, увеличивает запас маны членов.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 7,
                'effect_type' => ClanSkillEffectType::MP_MAX,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 10],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 25],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 45],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 70],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 100],
                ],
            ],
            [
                'name' => 'Клановая ярость',
                'description' => 'Боевой клич клана поднимает боевой пыл — увеличивает атаку в бою.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 8,
                'effect_type' => ClanSkillEffectType::ATTACK,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 1],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 2],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 3],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 5],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 7],
                ],
            ],
            [
                'name' => 'Клановая броня',
                'description' => 'Кузнецы клана куют по общему образцу — повышает защиту всех членов.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 9,
                'effect_type' => ClanSkillEffectType::DEFENSE,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 5],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 12],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 22],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 35],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 50],
                ],
            ],
            [
                'name' => 'Клановый оберег',
                'description' => 'Древние обереги клана рассеивают чужую магию, повышая сопротивление заклинаниям.',
                'icon' => null,
                'max_level' => 5,
                'sort_order' => 10,
                'effect_type' => ClanSkillEffectType::MAGIC_RESISTANCE,
                'levels' => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 4],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 7],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 11],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 16],
                ],
            ],
        ];

        foreach ($skills as $skillData) {
            if (ClanSkillDefinition::where('name', $skillData['name'])->exists()) {
                continue;
            }

            $definition = ClanSkillDefinition::create([
                'name' => $skillData['name'],
                'description' => $skillData['description'],
                'icon' => $skillData['icon'],
                'max_level' => $skillData['max_level'],
                'sort_order' => $skillData['sort_order'],
            ]);

            foreach ($skillData['levels'] as $lvl) {
                // Create a corresponding passive MagicSkill
                $magicSkill = MagicSkill::create([
                    'name' => "{$skillData['name']} (Клан Ур.{$lvl['level']})",
                    'slug' => "clan_{$definition->id}_lvl_{$lvl['level']}",
                    'description' => $skillData['description'],
                    'level' => $lvl['level'],
                    'type' => 'buff',
                    'mana_cost' => 0,
                    'min_damage' => 0,
                    'max_damage' => 0,
                    'base_healing' => 0,
                    'cooldown' => 0,
                    'target_type' => 'self',
                    'is_passive' => true,
                    'effects' => [
                        [
                            'type' => $skillData['effect_type']->value,
                            'value' => $lvl['effect_value'],
                            'is_percent' => false,
                        ],
                    ],
                ]);

                ClanSkillLevel::create([
                    'clan_skill_definition_id' => $definition->id,
                    'level' => $lvl['level'],
                    'required_clan_level' => $lvl['required_clan_level'],
                    'required_bonus_points' => $lvl['required_bonus_points'],
                    'share_item_id' => $lvl['share_item_id'] ?? null,
                    'share_item_count' => $lvl['share_item_count'] ?? null,
                    'magic_skill_id' => $magicSkill->id,
                ]);
            }
        }
    }
}
