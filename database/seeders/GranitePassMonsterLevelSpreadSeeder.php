<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Разводит все 5 видов монстров «Гранитного Перевала» по разным уровням
 * (46-56, единые 45/49 сменились на прогрессию «чем дальше — тем сильнее»,
 * продолжающую диапазон сразу после «Сторожевых Холмов» 35-45).
 *
 * Роль каждого вида не меняется — только уровень, на котором она вычисляется
 * формулами MonsterStatFormulas. Обновляет строки ПО МЕСТУ (находит по
 * текущим name+lvl, пишет новые lvl+статы в тот же id) — «Гранитный
 * Голем»(223/224) и «Кристальный скарабей»(225/226) заданы по id в
 * quest_objectives (квесты 214/215, `target_ids`), терять id пересозданием
 * нельзя.
 */
class GranitePassMonsterLevelSpreadSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, currentLvl: int, newLvl: int,
     *     hpMultiplier: float, armorMitigation: float, dodgePercent: float,
     *     critPercent: float, dmgPercent: float, expMultiplier: float, aggression: int,
     * }>
     */
    private const CHANGES = [
        ['name' => 'Каменный Голем', 'currentLvl' => 45, 'newLvl' => 46, 'hpMultiplier' => 1.45, 'armorMitigation' => 0.28, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 55],
        ['name' => 'Каменный Голем', 'currentLvl' => 49, 'newLvl' => 48, 'hpMultiplier' => 1.50, 'armorMitigation' => 0.31, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.9, 'expMultiplier' => 1.0, 'aggression' => 55],

        ['name' => 'Кристальный скарабей', 'currentLvl' => 45, 'newLvl' => 47, 'hpMultiplier' => 0.80, 'armorMitigation' => 0.05, 'dodgePercent' => 30, 'critPercent' => 35, 'dmgPercent' => 11.0, 'expMultiplier' => 1.0, 'aggression' => 75],
        ['name' => 'Кристальный скарабей', 'currentLvl' => 49, 'newLvl' => 50, 'hpMultiplier' => 0.84, 'armorMitigation' => 0.06, 'dodgePercent' => 36, 'critPercent' => 40, 'dmgPercent' => 11.4, 'expMultiplier' => 1.0, 'aggression' => 75],

        ['name' => 'Ледяной Волк', 'currentLvl' => 45, 'newLvl' => 49, 'hpMultiplier' => 0.95, 'armorMitigation' => 0.08, 'dodgePercent' => 22, 'critPercent' => 12, 'dmgPercent' => 12.0, 'expMultiplier' => 1.0, 'aggression' => 90],
        ['name' => 'Ледяной Волк', 'currentLvl' => 49, 'newLvl' => 52, 'hpMultiplier' => 0.99, 'armorMitigation' => 0.09, 'dodgePercent' => 26, 'critPercent' => 15, 'dmgPercent' => 12.4, 'expMultiplier' => 1.0, 'aggression' => 90],

        ['name' => 'Гранитный Голем', 'currentLvl' => 45, 'newLvl' => 51, 'hpMultiplier' => 1.65, 'armorMitigation' => 0.35, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 11.8, 'expMultiplier' => 1.0, 'aggression' => 55],
        ['name' => 'Гранитный Голем', 'currentLvl' => 49, 'newLvl' => 54, 'hpMultiplier' => 1.70, 'armorMitigation' => 0.38, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 12.2, 'expMultiplier' => 1.0, 'aggression' => 55],

        ['name' => 'Горный Грифон', 'currentLvl' => 45, 'newLvl' => 53, 'hpMultiplier' => 1.15, 'armorMitigation' => 0.12, 'dodgePercent' => 18, 'critPercent' => 16, 'dmgPercent' => 13.5, 'expMultiplier' => 1.0, 'aggression' => 85],
        ['name' => 'Горный Грифон', 'currentLvl' => 49, 'newLvl' => 56, 'hpMultiplier' => 1.20, 'armorMitigation' => 0.14, 'dodgePercent' => 22, 'critPercent' => 20, 'dmgPercent' => 14.0, 'expMultiplier' => 1.0, 'aggression' => 85],
    ];

    public function run(): void
    {
        $updated = 0;

        foreach (self::CHANGES as $c) {
            $monster = Monster::query()->where('name', $c['name'])->where('lvl', $c['currentLvl'])->first();

            if ($monster === null) {
                $this->command?->warn(sprintf('не найден: «%s» lvl %d', $c['name'], $c['currentLvl']));

                continue;
            }

            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($c['newLvl'], $c['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($c['newLvl'], $c['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $c['newLvl']);

            $monster->lvl = $c['newLvl'];
            $monster->hp = MonsterStatFormulas::hp($c['newLvl'], $c['hpMultiplier']);
            $monster->armor = MonsterStatFormulas::armorForMitigation($c['newLvl'], $c['armorMitigation']);
            $monster->dodge = MonsterStatFormulas::rawStatForChance($c['newLvl'], $c['dodgePercent']);
            $monster->critical = MonsterStatFormulas::rawStatForChance($c['newLvl'], $c['critPercent']);
            $monster->min_dmg = $minDmg;
            $monster->max_dmg = $maxDmg;
            $monster->aggression = $c['aggression'];
            $monster->exp = $exp;
            $monster->min_money = $minMoney;
            $monster->max_money = $maxMoney;
            $monster->save();

            $updated++;

            $this->command?->info(sprintf(
                'monster «%s» %d->%d: hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                $monster->name, $c['currentLvl'], $monster->lvl, $monster->hp, $monster->armor, $monster->dodge,
                $monster->critical, $monster->min_dmg, $monster->max_dmg, $monster->exp,
                $monster->min_money, $monster->max_money,
            ));
        }

        $this->command?->info("GranitePassMonsterLevelSpreadSeeder: обновлено монстров — {$updated}");
    }
}
