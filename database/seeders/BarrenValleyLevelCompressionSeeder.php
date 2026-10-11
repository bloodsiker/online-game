<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Сжимает диапазон уровней монстров «Опустевшей Долины» (map_id=3) с
 * прежних 6-50 до 6-35 — по прямому указанию пользователя. Правит
 * СУЩЕСТВУЮЩИЕ 15 monster-строк ПО МЕСТУ (id сохраняются — ни одна из
 * них не используется в quest_objectives, проверено, но id всё равно
 * трогать незачем: location_has_monsters и так ссылается по id).
 *
 * Новый уровень — линейное сжатие старого диапазона:
 *   new = 6 + (old - 6) * (35 - 6) / (50 - 6), округление до целого.
 *
 * Архетип (hpMultiplier/armorMitigation/dodge%/crit%/dmgPercent) не
 * придуман заново, а реверс-инжинирен из уже существующих характеристик
 * каждого вида (оба текущих уровня каждого вида дают один и тот же %
 * с точностью до погрешности округления — подтверждено перед правкой),
 * поэтому пересчёт на новый уровень сохраняет «характер» каждого вида:
 *   Каменная Ящерица / Одичавший Бык — тяжёлая броня (20%), без уворота/крита.
 *   Кровавый Орёл / Пепельный Скорпион / Свирепая Гиена — крит-архетип (18%).
 *   Пепельный Койот / Степной Шакал / Стервятник — уворотливый архетип (24%).
 * aggression у всех видов не менялась (хранится в самой monsters-таблице,
 * не зависит от уровня).
 */
class BarrenValleyLevelCompressionSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     id: int, newLvl: int, hpMul: float, armorMit: float,
     *     dodgeP: float, critP: float, dmgP: float,
     * }>
     */
    private const PLAN = [
        ['id' => 58, 'newLvl' => 14, 'hpMul' => 1.70, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 60, 'newLvl' => 27, 'hpMul' => 1.70, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 57, 'newLvl' => 7, 'hpMul' => 1.70, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 59, 'newLvl' => 21, 'hpMul' => 1.70, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 61, 'newLvl' => 34, 'hpMul' => 1.70, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 69, 'newLvl' => 22, 'hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0],
        ['id' => 64, 'newLvl' => 19, 'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 67, 'newLvl' => 9, 'hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0],
        ['id' => 70, 'newLvl' => 28, 'hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0],
        ['id' => 68, 'newLvl' => 15, 'hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0],
        ['id' => 71, 'newLvl' => 35, 'hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0],
        ['id' => 63, 'newLvl' => 13, 'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 66, 'newLvl' => 32, 'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 62, 'newLvl' => 6, 'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0],
        ['id' => 65, 'newLvl' => 26, 'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0],
    ];

    public function run(): void
    {
        $updated = 0;

        foreach (self::PLAN as $row) {
            $monster = DB::table('monsters')->where('id', $row['id'])->first();
            if ($monster === null) {
                $this->command?->warn("monster id={$row['id']} не найден, пропускаю");

                continue;
            }

            $newLvl = $row['newLvl'];
            $hp = MonsterStatFormulas::hp($newLvl, $row['hpMul']);
            $armor = MonsterStatFormulas::armorForMitigation($newLvl, $row['armorMit']);
            $dodge = MonsterStatFormulas::rawStatForChance($newLvl, $row['dodgeP']);
            $critical = MonsterStatFormulas::rawStatForChance($newLvl, $row['critP']);
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($newLvl, $row['dmgP']);
            $exp = MonsterStatFormulas::expReward($newLvl, 1.0);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $newLvl);

            DB::table('monsters')->where('id', $row['id'])->update([
                'lvl' => $newLvl,
                'hp' => $hp,
                'armor' => $armor,
                'dodge' => $dodge,
                'critical' => $critical,
                'min_dmg' => $minDmg,
                'max_dmg' => $maxDmg,
                'exp' => $exp,
                'min_money' => $minMoney,
                'max_money' => $maxMoney,
                'updated_at' => now(),
            ]);

            $this->command?->info(sprintf(
                '%s: lvl%d -> lvl%d, hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                $monster->name, $monster->lvl, $newLvl, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
            ));
            $updated++;
        }

        $this->command?->info("BarrenValleyLevelCompressionSeeder: пересчитано монстров — {$updated}");
    }
}
