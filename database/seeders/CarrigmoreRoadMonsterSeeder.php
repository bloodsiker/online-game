<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Создаёт новые, более высокие ступени двух УЖЕ СУЩЕСТВУЮЩИХ видов —
 * Вепрь и Стервятник — под дорогу к Карригмору (map_id=15, 1582-1592),
 * по прямому указанию пользователя: тот же диапазон уровней, что и у
 * Заваленной Штольни (48-55), по 3 ступени на вид.
 *
 * Архетип (hpMultiplier/armorMitigation/dodge%/crit%/dmg% в
 * MonsterStatFormulas) реверс-инжинирен из уже существующих ступеней тех
 * же видов (Вепрь lvl35, Стервятник lvl36 — ближайшие к новому диапазону),
 * чтобы новые уровни были тем же персонажем, просто сильнее — а не
 * внезапно другим архетипом:
 *   Вепрь — чистый танк без уворота/крита (бьёт в лоб, не уклоняется).
 *   Стервятник — лёгкий, очень уклончивый, слабый урон (падальщик, а не боец).
 *
 * Расстановка по локациям — отдельным шагом (ждём параметров респавна от
 * пользователя), этот сидер только создаёт сами monster-строки.
 */
class CarrigmoreRoadMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, levels: array{0:int,1:int,2:int},
     *     hpMul: float, armorMit: float, dodgeChance: float, critChance: float,
     *     dmgPercent: float, aggression: int,
     * }>
     */
    private const SPECIES = [
        [
            'name' => 'Вепрь',
            'levels' => [48, 51, 54],
            'hpMul' => 1.50, 'armorMit' => 0.16, 'dodgeChance' => 0.0, 'critChance' => 0.0, 'dmgPercent' => 12.5, 'aggression' => 90,
        ],
        [
            'name' => 'Стервятник',
            'levels' => [49, 52, 55],
            'hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeChance' => 24.0, 'critChance' => 0.0, 'dmgPercent' => 9.0, 'aggression' => 60,
        ],
    ];

    public function run(): void
    {
        foreach (self::SPECIES as $species) {
            foreach ($species['levels'] as $level) {
                $existing = DB::table('monsters')->where('name', $species['name'])->where('lvl', $level)->first();

                if ($existing !== null) {
                    $this->command?->info("{$species['name']} lvl{$level} уже существует (id={$existing->id}), пропускаю");

                    continue;
                }

                $hp = MonsterStatFormulas::hp($level, $species['hpMul']);
                $armor = MonsterStatFormulas::armorForMitigation($level, $species['armorMit']);
                $dodge = MonsterStatFormulas::rawStatForChance($level, $species['dodgeChance']);
                $critical = MonsterStatFormulas::rawStatForChance($level, $species['critChance']);
                [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, $species['dmgPercent']);
                $exp = MonsterStatFormulas::expReward($level, 1.0);
                [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $level);

                $monsterId = DB::table('monsters')->insertGetId([
                    'name' => $species['name'],
                    'description' => $species['name'] === 'Вепрь'
                        ? 'Матёрый кабан, что пасётся у самой дороги на Карригмор — издали кажется безобидным, но стоит подойти ближе, и он без колебаний идёт на таран.'
                        : 'Крупный стервятник, что кружит над дорогой к Карригмору — высматривает не только падаль, но и зазевавшегося путника.',
                    'image' => null,
                    'lvl' => $level,
                    'hp' => $hp,
                    'armor' => $armor,
                    'dodge' => $dodge,
                    'critical' => $critical,
                    'min_dmg' => $minDmg,
                    'max_dmg' => $maxDmg,
                    'attack_type' => 'physical',
                    'magic_attack' => 0,
                    'magic_power_coefficient' => 0,
                    'magic_resistance' => 0,
                    'aggression' => $species['aggression'],
                    'exp' => $exp,
                    'min_money' => $minMoney,
                    'max_money' => $maxMoney,
                    'is_boss' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command?->info(sprintf(
                    'создан %s lvl%d (id=%d): hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                    $species['name'], $level, $monsterId, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
                ));
            }
        }
    }
}
