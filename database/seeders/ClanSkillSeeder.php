<?php

namespace Database\Seeders;

use App\Enums\ClanSkillEffectType;
use App\Models\Clan\ClanSkillDefinition;
use App\Models\Clan\ClanSkillLevel;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use Illuminate\Database\Seeder;

class ClanSkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'name'        => 'Боевая закалка',
                'description' => 'Тренировки под руководством клана повышают силу всех членов.',
                'icon'        => null,
                'max_level'   => 5,
                'sort_order'  => 1,
                'effect_type' => ClanSkillEffectType::STR,
                'levels'      => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name'        => 'Клановая стойкость',
                'description' => 'Единство клана укрепляет тело, увеличивая максимальный запас здоровья.',
                'icon'        => null,
                'max_level'   => 5,
                'sort_order'  => 2,
                'effect_type' => ClanSkillEffectType::HP_MAX,
                'levels'      => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 20],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 50],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 90],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 140],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 200],
                ],
            ],
            [
                'name'        => 'Клановая мудрость',
                'description' => 'Совместное обучение повышает интеллект членов клана.',
                'icon'        => null,
                'max_level'   => 5,
                'sort_order'  => 3,
                'effect_type' => ClanSkillEffectType::INT,
                'levels'      => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
            [
                'name'        => 'Клановая защита',
                'description' => 'Братство клана учит защищаться, увеличивая ловкость.',
                'icon'        => null,
                'max_level'   => 5,
                'sort_order'  => 4,
                'effect_type' => ClanSkillEffectType::AGI,
                'levels'      => [
                    ['level' => 1, 'required_clan_level' => 1, 'required_bonus_points' => 100,  'effect_value' => 2],
                    ['level' => 2, 'required_clan_level' => 2, 'required_bonus_points' => 250,  'effect_value' => 5],
                    ['level' => 3, 'required_clan_level' => 3, 'required_bonus_points' => 500,  'effect_value' => 9],
                    ['level' => 4, 'required_clan_level' => 4, 'required_bonus_points' => 900,  'effect_value' => 14],
                    ['level' => 5, 'required_clan_level' => 5, 'required_bonus_points' => 1500, 'effect_value' => 20],
                ],
            ],
        ];

        foreach ($skills as $skillData) {
            $definition = ClanSkillDefinition::create([
                'name'        => $skillData['name'],
                'description' => $skillData['description'],
                'icon'        => $skillData['icon'],
                'max_level'   => $skillData['max_level'],
                'sort_order'  => $skillData['sort_order'],
            ]);

            foreach ($skillData['levels'] as $lvl) {
                // Create a corresponding passive MagicSkill
                $magicSkill = MagicSkill::create([
                    'name'        => "{$skillData['name']} (Клан Ур.{$lvl['level']})",
                    'slug'        => "clan_{$definition->id}_lvl_{$lvl['level']}",
                    'description' => $skillData['description'],
                    'level'       => $lvl['level'],
                    'type'        => 'buff',
                    'mana_cost'   => 0,
                    'min_damage'  => 0,
                    'max_damage'  => 0,
                    'base_healing'=> 0,
                    'cooldown'    => 0,
                    'target_type' => 'self',
                    'is_passive'  => true,
                    'effects'     => [
                        [
                            'type'       => $skillData['effect_type']->value,
                            'value'      => $lvl['effect_value'],
                            'is_percent' => false,
                        ]
                    ],
                ]);

                ClanSkillLevel::create([
                    'clan_skill_definition_id' => $definition->id,
                    'level'                    => $lvl['level'],
                    'required_clan_level'      => $lvl['required_clan_level'],
                    'required_bonus_points'    => $lvl['required_bonus_points'],
                    'share_item_id'            => $lvl['share_item_id'] ?? null,
                    'share_item_count'         => $lvl['share_item_count'] ?? null,
                    'magic_skill_id'           => $magicSkill->id,
                ]);
            }
        }
    }
}