<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Создаёт 3 реалистичных вида монстров «Заваленной Штольни» (map_id=14,
 * локации 1531-1581, вход 1530 без монстров) и равномерно расставляет их
 * по ВСЕЙ территории карты — по прямому указанию пользователя:
 *   1. Уровни того же диапазона, что и Гранитный Перевал — 48-55.
 *   2. Равномерное распределение по всей карте, одинаково до решётки
 *      (1531-1554) и после неё (1557-1581) — без деления на полосы,
 *      в отличие от GranitePassMonsterPlacementSeeder.
 *   3. Три «настоящих» вида (не выдуманные существа), каждый по 3 уровня:
 *      Обсидиановый Скорпион (48/50/52), Пещерный Паук (49/53/55),
 *      Чёрная Гадюка (50/52/55).
 *
 * Характеристики считаются по MonsterStatFormulas (та же методика, что и
 * для Watch Hills/Granite Pass — HitCalculator-совместимые armor/dodge/crit,
 * expReward от ExperienceCurve, moneyRange от неё же).
 *
 * Респавн — по прямому указанию пользователя: в пуле на локации все 3 вида
 * (location_has_monsters), но одновременно может респауниться только 2
 * (locations.count_monster=2); агрессия зафиксирована на 30% для всех троих
 * видов (location_has_monsters.aggression=30 — переопределяет собственную
 * агрессию вида из monsters.aggression), percent_respawn_monster=50,
 * time_not_attack=15 сек (как и на Watch Hills).
 */
class CollapsedMineMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, description: string, levels: array{0:int,1:int,2:int},
     *     hpMul: float, armorMit: float, dodgeChance: float, critChance: float,
     *     dmgPercent: float, aggression: int,
     * }>
     */
    private const SPECIES = [
        [
            'name' => 'Обсидиановый Скорпион',
            'description' => 'Крупный скорпион с панцирем, отливающим вулканическим стеклом — такие водятся в осыпях Гранитного Перевала, но здесь, в темноте штрека, подобрался ближе, чем обычно решается.',
            'levels' => [48, 50, 52],
            'hpMul' => 0.85, 'armorMit' => 0.08, 'dodgeChance' => 25.0, 'critChance' => 20.0, 'dmgPercent' => 11.5, 'aggression' => 60,
        ],
        [
            'name' => 'Пещерный Паук',
            'description' => 'Крупный паук с длинными цепкими лапами — плетёт паутину между старыми крепями и бросается на всё, что заденет нить в темноте штрека.',
            'levels' => [49, 53, 55],
            'hpMul' => 0.75, 'armorMit' => 0.05, 'dodgeChance' => 32.0, 'critChance' => 25.0, 'dmgPercent' => 11.0, 'aggression' => 65,
        ],
        [
            'name' => 'Чёрная Гадюка',
            'description' => 'Гадюка с почти чёрной от подземной сырости кожей — чувствует шаги по дрожи камня и бьёт из темноты быстрее, чем успеваешь её заметить.',
            'levels' => [50, 52, 55],
            'hpMul' => 0.70, 'armorMit' => 0.03, 'dodgeChance' => 28.0, 'critChance' => 30.0, 'dmgPercent' => 12.5, 'aggression' => 55,
        ],
    ];

    private const ZONE_FROM = 1531;

    private const ZONE_TO = 1581;

    public function run(): void
    {
        $monsterIds = $this->createOrFindMonsters();

        $locationIds = DB::table('locations')
            ->whereBetween('id', [self::ZONE_FROM, self::ZONE_TO])
            ->orderBy('id')
            ->pluck('id')
            ->values();

        // Идемпотентность: убираем свои же старые строки перед повторной
        // расстановкой (на случай повторного запуска после правок).
        $allMonsterIds = array_merge(...array_values($monsterIds));
        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', $allMonsterIds)
            ->delete();

        $rows = [];
        foreach ($locationIds as $index => $locationId) {
            // Смещение фазы на вид (0/1/2), чтобы конкретные тройки уровней
            // не повторялись каждые 3 локации подряд — тир каждого отдельного
            // вида при этом всё равно равномерно распределён по всей карте
            // (51 / 3 = ровно 17 локаций на каждый из трёх уровней вида).
            foreach (self::SPECIES as $speciesIndex => $species) {
                $tier = ($index + $speciesIndex) % 3;
                $level = $species['levels'][$tier];
                $monsterId = $monsterIds[$species['name']][$level];

                $rows[] = ['location_id' => $locationId, 'monster_id' => $monsterId, 'aggression' => 30];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_has_monsters')->insert($chunk);
        }

        DB::table('locations')
            ->whereIn('id', $locationIds)
            ->update([
                'count_monster' => 2,
                'percent_respawn_monster' => 50,
                'time_not_attack' => 15,
                'updated_at' => now(),
            ]);

        $this->command?->info(sprintf(
            'CollapsedMineMonsterSeeder: %d локаций, %d строк расстановки (3 вида в пуле на локацию, респаунится максимум 2)',
            $locationIds->count(), count($rows),
        ));
    }

    /** @return array<string, array<int, int>> имя вида => [уровень => monster_id] */
    private function createOrFindMonsters(): array
    {
        $result = [];

        foreach (self::SPECIES as $species) {
            foreach ($species['levels'] as $level) {
                $existing = DB::table('monsters')->where('name', $species['name'])->where('lvl', $level)->first();

                if ($existing !== null) {
                    $result[$species['name']][$level] = $existing->id;

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
                    'description' => $species['description'],
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

                $result[$species['name']][$level] = $monsterId;

                $this->command?->info(sprintf(
                    'создан %s lvl%d: hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                    $species['name'], $level, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
                ));
            }
        }

        return $result;
    }
}
