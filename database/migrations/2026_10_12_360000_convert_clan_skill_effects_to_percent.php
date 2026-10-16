<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Клановые навыки (10 шт., по 5 уровней) давали фиксированное число очков
     * стата (например, Клановая мощь: +2/+5/+9/+14/+20 силы). На высоких
     * уровнях персонажа это становится незаметной прибавкой на фоне сотен
     * очков характеристик от прокачки/экипировки.
     *
     * Переводим все 50 эффектов на is_percent=true с единой прогрессией
     * 2/4/6/8/10% за уровень навыка — бонус масштабируется вместе с
     * персонажем (applyModifiers() в PlayerStatService считает процент от
     * итогового значения стата, а не от базового).
     */
    private const array LEVEL_PERCENTS = [1 => 2.0, 2 => 4.0, 3 => 6.0, 4 => 8.0, 5 => 10.0];

    /** magic_skill_id => [level, stat_type, старое_флэт_значение] — для down(). */
    private const array SKILLS = [
        4 => [1, 'strength', 2],
        5 => [2, 'strength', 5],
        6 => [3, 'strength', 9],
        7 => [4, 'strength', 14],
        8 => [5, 'strength', 20],

        9 => [1, 'hp_max', 20],
        10 => [2, 'hp_max', 50],
        11 => [3, 'hp_max', 90],
        12 => [4, 'hp_max', 140],
        13 => [5, 'hp_max', 200],

        14 => [1, 'intuition', 2],
        15 => [2, 'intuition', 5],
        16 => [3, 'intuition', 9],
        17 => [4, 'intuition', 14],
        18 => [5, 'intuition', 20],

        19 => [1, 'agility', 2],
        20 => [2, 'agility', 5],
        21 => [3, 'agility', 9],
        22 => [4, 'agility', 14],
        23 => [5, 'agility', 20],

        40 => [1, 'intelligence', 2],
        41 => [2, 'intelligence', 5],
        42 => [3, 'intelligence', 9],
        43 => [4, 'intelligence', 14],
        44 => [5, 'intelligence', 20],

        45 => [1, 'wisdom', 2],
        46 => [2, 'wisdom', 5],
        47 => [3, 'wisdom', 9],
        48 => [4, 'wisdom', 14],
        49 => [5, 'wisdom', 20],

        50 => [1, 'mp_max', 10],
        51 => [2, 'mp_max', 25],
        52 => [3, 'mp_max', 45],
        53 => [4, 'mp_max', 70],
        54 => [5, 'mp_max', 100],

        55 => [1, 'attack', 1],
        56 => [2, 'attack', 2],
        57 => [3, 'attack', 3],
        58 => [4, 'attack', 5],
        59 => [5, 'attack', 7],

        60 => [1, 'defense', 5],
        61 => [2, 'defense', 12],
        62 => [3, 'defense', 22],
        63 => [4, 'defense', 35],
        64 => [5, 'defense', 50],

        65 => [1, 'magic_resistance', 2],
        66 => [2, 'magic_resistance', 4],
        67 => [3, 'magic_resistance', 7],
        68 => [4, 'magic_resistance', 11],
        69 => [5, 'magic_resistance', 16],
    ];

    public function up(): void
    {
        foreach (self::SKILLS as $magicSkillId => [$level, $statType, $oldFlatValue]) {
            DB::table('magic_skills')->where('id', $magicSkillId)->update([
                'effects' => json_encode([[
                    'type' => $statType,
                    'value' => self::LEVEL_PERCENTS[$level],
                    'is_percent' => true,
                ]]),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (self::SKILLS as $magicSkillId => [$level, $statType, $oldFlatValue]) {
            DB::table('magic_skills')->where('id', $magicSkillId)->update([
                'effects' => json_encode([[
                    'type' => $statType,
                    'value' => $oldFlatValue,
                    'is_percent' => false,
                ]]),
                'updated_at' => now(),
            ]);
        }
    }
};
