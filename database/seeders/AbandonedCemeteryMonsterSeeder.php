<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Создаёт 8 видов нежити «Заброшенного кладбища» (map_id=5, 79 локаций,
 * 308-386) по 3 ступени (уровни 40-50) и расставляет их случайным
 * образом — по прямому указанию пользователя: пул 6 мобов на локацию,
 * респаунится 2, агрессия override 80%, percent_respawn_monster=80,
 * time_not_attack=15 сек.
 *
 * «Случайно» реализовано как: для каждой локации случайно выбираются 6
 * ИЗ 8 видов (без повтора вида на одной локации — иначе «пул из 6» почти
 * всегда означал бы два уровня одного и того же монстра рядом), а для
 * каждого выбранного вида случайно берётся один из его 3 уровней.
 *
 * Существующий monster_id=43 «Кровавый Хранитель Склепа» (lvl=-100,
 * обнулённые статы, is_boss=1, стоит только на локации 350) — не
 * относится к этому запросу и не трогается.
 */
class AbandonedCemeteryMonsterSeeder extends Seeder
{
    private const MAP_ID = 5;

    private const POOL_SIZE_PER_LOCATION = 6;

    private const LOCATION_AGGRESSION_OVERRIDE = 80;

    /**
     * @var array<int, array{
     *     name: string, levels: array{0:int,1:int,2:int}, hpMul: float,
     *     armorMit: float, dodgeP: float, critP: float, dmgP: float, aggr: int,
     *     description: string,
     * }>
     */
    private const SPECIES = [
        [
            'name' => 'Дряхлый Скелет', 'levels' => [40, 43, 46],
            'hpMul' => 0.70, 'armorMit' => 0.10, 'dodgeP' => 10.0, 'critP' => 0.0, 'dmgP' => 10.0, 'aggr' => 60,
            'description' => 'Иссохший скелет в полуистлевших доспехах — кости держатся на одной лишь злой воле, но рубящий удар клинка всё ещё способен свалить с ног.',
        ],
        [
            'name' => 'Зомби', 'levels' => [41, 44, 47],
            'hpMul' => 1.60, 'armorMit' => 0.15, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 11.0, 'aggr' => 70,
            'description' => 'Распухшее мёртвое тело, упрямо бредущее на звук чужих шагов — рана не остановит его так, как остановила бы живого.',
        ],
        [
            'name' => 'Кровавый Слуга', 'levels' => [42, 45, 48],
            'hpMul' => 0.90, 'armorMit' => 0.05, 'dodgeP' => 10.0, 'critP' => 20.0, 'dmgP' => 11.0, 'aggr' => 85,
            'description' => 'Бывший слуга фамильного склепа, превращённый в жадную до крови тварь — бросается резко и бьёт точно туда, где кровь ближе к коже.',
        ],
        [
            'name' => 'Мертвец', 'levels' => [40, 44, 48],
            'hpMul' => 1.00, 'armorMit' => 0.08, 'dodgeP' => 5.0, 'critP' => 5.0, 'dmgP' => 10.0, 'aggr' => 65,
            'description' => 'Безымянный покойник, поднятый из земли неизвестно чьей волей — в нём не осталось ничего, кроме голода по теплу живых.',
        ],
        [
            'name' => 'Могильный Страж', 'levels' => [43, 46, 49],
            'hpMul' => 1.50, 'armorMit' => 0.25, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 11.0, 'aggr' => 50,
            'description' => 'Закованный в тяжёлые латы истукан, поставленный когда-то охранять фамильный склеп — покоя не нарушает первым, но кто побеспокоит гробницу, не уйдёт невредимым.',
        ],
        [
            'name' => 'Могильный Упырь', 'levels' => [41, 45, 50],
            'hpMul' => 0.75, 'armorMit' => 0.05, 'dodgeP' => 28.0, 'critP' => 15.0, 'dmgP' => 11.0, 'aggr' => 90,
            'description' => 'Костлявая тварь на четырёх конечностях, вырывшаяся из разорённой могилы — передвигается рывками, слишком быстрыми для мёртвой плоти.',
        ],
        [
            'name' => 'Плакальщица', 'levels' => [42, 47, 50],
            'hpMul' => 0.65, 'armorMit' => 0.03, 'dodgeP' => 20.0, 'critP' => 25.0, 'dmgP' => 12.0, 'aggr' => 55,
            'description' => 'Полупрозрачная фигура в траурном одеянии, что бродит меж могил с неумолчным плачем — пронзительный вой на миг глушит слух, и тогда её когти бьют без промаха.',
        ],
        [
            'name' => 'Саван', 'levels' => [40, 45, 49],
            'hpMul' => 0.60, 'armorMit' => 0.0, 'dodgeP' => 35.0, 'critP' => 10.0, 'dmgP' => 10.0, 'aggr' => 45,
            'description' => 'Погребальная ткань, поднятая в воздух без тела под ней — вьётся между надгробий почти неразличимо, пока не обовьётся вокруг жертвы.',
        ],
    ];

    public function run(): void
    {
        $monsterIdsBySpecies = $this->createOrFindMonsters();

        $locationIds = DB::table('locations')
            ->where('map_id', self::MAP_ID)
            ->orderBy('id')
            ->pluck('id');

        $allMonsterIds = collect($monsterIdsBySpecies)->flatMap(fn (array $byLevel) => array_values($byLevel))->all();

        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', $allMonsterIds)
            ->delete();

        $speciesNames = array_column(self::SPECIES, 'name');
        $rows = [];

        foreach ($locationIds as $locationId) {
            $chosenSpecies = (array) array_rand(array_flip($speciesNames), self::POOL_SIZE_PER_LOCATION);

            foreach ($chosenSpecies as $speciesName) {
                $levels = array_keys($monsterIdsBySpecies[$speciesName]);
                $level = $levels[array_rand($levels)];
                $monsterId = $monsterIdsBySpecies[$speciesName][$level];

                $rows[] = [
                    'location_id' => $locationId,
                    'monster_id' => $monsterId,
                    'aggression' => self::LOCATION_AGGRESSION_OVERRIDE,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_has_monsters')->insert($chunk);
        }

        DB::table('locations')
            ->whereIn('id', $locationIds)
            ->update([
                'count_monster' => 2,
                'percent_respawn_monster' => 80,
                'time_not_attack' => 15,
                'updated_at' => now(),
            ]);

        $this->command?->info(sprintf(
            'AbandonedCemeteryMonsterSeeder: %d локаций, %d строк расстановки (6 случайных видов из 8 на локацию)',
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
                $dodge = MonsterStatFormulas::rawStatForChance($level, $species['dodgeP']);
                $critical = MonsterStatFormulas::rawStatForChance($level, $species['critP']);
                [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, $species['dmgP']);
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
                    'aggression' => $species['aggr'],
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
