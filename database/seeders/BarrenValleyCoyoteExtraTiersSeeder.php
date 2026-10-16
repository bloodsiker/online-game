<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Добавляет 2 новые ступени «Пепельного Койота» (id=64, сейчас только
 * lvl19) на «Опустевшей Долине» (map_id=3) — по прямому указанию
 * пользователя: lvl 11 и lvl 33, поставить на локации тех «уровневых
 * групп», к которым эти уровни ближе всего.
 *
 * После сжатия диапазона (см. BarrenValleyLevelCompressionSeeder) карта
 * делится на 5 чётких полос (каждая — 3 вида на одном уровне):
 *   Полоса 1 (113-140,146-150): Бык(7)/Скорпион(9)/Стервятник(6)
 *   Полоса 2 (141-145,151-175,214-219): Ящерица(14)/Гиена(15)/Шакал(13)  <- lvl11 ближе сюда
 *   Полоса 3 (176-213): Орёл(22)/Бык(21)/Койот(19) — уже существующая ступень Койота
 *   Полоса 4 (246-270): Ящерица(27)/Скорпион(28)/Стервятник(26)
 *   Полоса 5 (271-304,306-307): Бык(34)/Гиена(35)/Шакал(32)  <- lvl33 ближе сюда
 *
 * Архетип — тот же самый, что у существующей lvl19 ступени (реверс-инжинирен
 * в BarrenValleyLevelCompressionSeeder): hpMul=0.75, armorMit=3%, dodge=24%,
 * crit=0%, dmg=9%, aggression(вида)=75. Агрессия override на location_has_monsters
 * берётся из уже стоящих в каждой полосе монстров (полоса 2 — 60%, полоса 5 — 70%),
 * чтобы новый вид не выбивался из уже заданного баланса полосы.
 */
class BarrenValleyCoyoteExtraTiersSeeder extends Seeder
{
    private const ARCHETYPE = ['hpMul' => 0.75, 'armorMit' => 0.03, 'dodgeP' => 24.0, 'critP' => 0.0, 'dmgP' => 9.0, 'aggression' => 75];

    /** @var array<int, array{level: int, locationIds: array<int,int>, bandAggression: int}> */
    private const PLACEMENTS = [
        ['level' => 11, 'ranges' => [[141, 145], [151, 175], [214, 219]], 'bandAggression' => 60],
        ['level' => 33, 'ranges' => [[271, 304], [306, 307]], 'bandAggression' => 70],
    ];

    public function run(): void
    {
        foreach (self::PLACEMENTS as $placement) {
            $level = $placement['level'];

            $monster = LocalizedContentSeederQuery::forTable('monsters')->where('name', 'Пепельный Койот')->where('lvl', $level)->first();

            if ($monster === null) {
                $a = self::ARCHETYPE;
                $hp = MonsterStatFormulas::hp($level, $a['hpMul']);
                $armor = MonsterStatFormulas::armorForMitigation($level, $a['armorMit']);
                $dodge = MonsterStatFormulas::rawStatForChance($level, $a['dodgeP']);
                $critical = MonsterStatFormulas::rawStatForChance($level, $a['critP']);
                [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, $a['dmgP']);
                $exp = MonsterStatFormulas::expReward($level, 1.0);
                [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $level);

                $monsterId = LocalizedContentSeederQuery::forTable('monsters')->insertGetId([
                    'name' => 'Пепельный Койот',
                    'description' => 'Поджарый степной хищник пепельного окраса — держится по одному, полагаясь не на силу, а на увёртливость и выносливость погони.',
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
                    'aggression' => $a['aggression'],
                    'exp' => $exp,
                    'min_money' => $minMoney,
                    'max_money' => $maxMoney,
                    'is_boss' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command?->info(sprintf(
                    'создан Пепельный Койот lvl%d (id=%d): hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                    $level, $monsterId, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
                ));
            } else {
                $monsterId = $monster->id;
                $this->command?->info("Пепельный Койот lvl{$level} уже существует (id={$monsterId})");
            }

            $locationIds = [];
            foreach ($placement['ranges'] as [$from, $to]) {
                $locationIds = array_merge($locationIds, range($from, $to));
            }

            DB::table('location_has_monsters')
                ->whereIn('location_id', $locationIds)
                ->where('monster_id', $monsterId)
                ->delete();

            $rows = array_map(fn (int $locationId) => [
                'location_id' => $locationId,
                'monster_id' => $monsterId,
                'aggression' => $placement['bandAggression'],
            ], $locationIds);

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('location_has_monsters')->insert($chunk);
            }

            $this->command?->info(sprintf(
                'lvl%d: расставлен на %d локациях (агрессия override=%d%%)',
                $level, count($locationIds), $placement['bandAggression'],
            ));
        }
    }
}
