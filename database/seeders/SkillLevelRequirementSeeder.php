<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Skill\SkillLevelRequirement;
use Illuminate\Database\Seeder;

/**
 * Линейный рост стоимости за уровень (exp_diff(L) = round(BASE_EXP × L)),
 * даёт квадратичный рост кумулятивного опыта — заменяет прежний
 * экспоненциальный `×1.5 каждые 10 уровней`, который был рассчитан только
 * на кап 100 и при экстраполяции до 2000 давал абсурдные числа (1.5^199 ≈
 * 10^35, переполнение любого integer).
 *
 * Откалибровано по двум якорям (учитывая, что share_items.skill_exp даёт
 * РАЗНЫЙ опыт за удар в зависимости от оружия — 1 у стартового «Кожаный
 * меч», 2 у всего, что дальше, см. StarterEquipmentSeeder::TIER1_SKILL_EXP_*):
 *  - skill 18 (гейт «Кожаный клинок») должен требовать ~1850 опыта
 *    (естественный темп к 10 lvl персонажа НА СТАРТОВОМ мече, 1 опыт/удар)
 *    + ~65% доп. ударов;
 *  - skill 2000 должен занимать ~3-4 года игры при ~4.5ч/день реального боя
 *    (из ~5-8ч/день игровой сессии), 1 сек/раунд (см. cooldownDuration) —
 *    почти весь этот путь проходит УЖЕ НЕ на стартовом мече (2 опыта/удар),
 *    поэтому целевой опыт вдвое больше «сырых» ударов за 3.4 года (~18 млн
 *    ударов × 2 = ~36 млн опыта).
 * BASE_EXP=18 даёт cum(18)≈3078 и cum(2000)≈36 млн — оба якоря выдержаны.
 * Нет риска переполнения int (cum(2000) на 2 порядка меньше предела
 * unsignedInteger), поэтому таблица сразу генерируется до 2000, а не
 * капается на 100, как было раньше.
 *
 * ВАЖНО: это первая прикидка — обе точки калибровки опираются на грубые
 * оценки (4.5ч/день, ~90% попаданий против уворота, 2 опыта/удар «дальше»),
 * а не на реальный темп боёв 20+ уровня персонажа (мобов там пока нет).
 * Пересчитать, когда появятся мобы и реальные данные по темпу боя выше
 * 20 уровня.
 */
class SkillLevelRequirementSeeder extends Seeder
{
    private const MAX_LEVELS = 2000;

    private const BASE_EXP = 18;

    public function run(): void
    {
        $skills = Skill::all();

        foreach ($skills as $skill) {
            SkillLevelRequirement::where('skill_id', $skill->id)->delete();

            $rows = [];
            $totalExp = 0;

            for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
                $expDiff = (int) round(self::BASE_EXP * $level);
                $totalExp += $expDiff;

                $rows[] = [
                    'skill_id' => $skill->id,
                    'lvl' => $level,
                    'exp_required' => $totalExp,
                    'exp_diff' => $expDiff,
                ];
            }

            SkillLevelRequirement::insert($rows);
        }
    }
}
