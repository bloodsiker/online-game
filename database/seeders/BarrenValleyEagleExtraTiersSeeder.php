<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Добавляет 2 новые ступени «Кровавого Орла» (id=69, сейчас только lvl22,
 * в полосе 3) на «Опустевшей Долине» (map_id=3) — по прямому указанию
 * пользователя: lvl 20 и lvl 30.
 *
 * Полосы те же, что и в BarrenValleyCoyoteExtraTiersSeeder. lvl20 почти
 * совпадает с диапазоном родной полосы 3 (19-22, где Орёл уже стоит) —
 * чтобы не дублировать вид дважды в одной локации, берём ближайшую полосу
 * БЕЗ Орла: полоса 2 (141-145,151-175,214-219). lvl30 — между полосой 4
 * (26-28) и 5 (32-35), обе в равном отдалении (2 уровня); по среднему
 * расстоянию до всех трёх уровней полосы ближе полоса 4 (246-270).
 *
 * Архетип реверс-инжинирен в BarrenValleyLevelCompressionSeeder:
 * hpMul=1.00, armorMit=3%, crit=18%, dmg=9%, aggression(вида)=90.
 * Агрессия override на location_has_monsters — из уже стоящих в каждой
 * полосе монстров (полоса 2 — 60%, полоса 4 — 70%).
 */
class BarrenValleyEagleExtraTiersSeeder extends Seeder
{
    private const ARCHETYPE = ['hpMul' => 1.00, 'armorMit' => 0.03, 'dodgeP' => 0.0, 'critP' => 18.0, 'dmgP' => 9.0, 'aggression' => 90];

    /** @var array<int, array{level: int, ranges: array<int, array{0:int,1:int}>, bandAggression: int}> */
    private const PLACEMENTS = [
        ['level' => 20, 'ranges' => [[141, 145], [151, 175], [214, 219]], 'bandAggression' => 60],
        ['level' => 30, 'ranges' => [[246, 270]], 'bandAggression' => 70],
    ];

    public function run(): void
    {
        foreach (self::PLACEMENTS as $placement) {
            $level = $placement['level'];

            $monster = DB::table('monsters')->where('name', 'Кровавый Орёл')->where('lvl', $level)->first();

            if ($monster === null) {
                $a = self::ARCHETYPE;
                $hp = MonsterStatFormulas::hp($level, $a['hpMul']);
                $armor = MonsterStatFormulas::armorForMitigation($level, $a['armorMit']);
                $dodge = MonsterStatFormulas::rawStatForChance($level, $a['dodgeP']);
                $critical = MonsterStatFormulas::rawStatForChance($level, $a['critP']);
                [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, $a['dmgP']);
                $exp = MonsterStatFormulas::expReward($level, 1.0);
                [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $level);

                $monsterId = DB::table('monsters')->insertGetId([
                    'name' => 'Кровавый Орёл',
                    'description' => 'Крупный хищный орёл с тёмно-рыжим оперением на груди — высматривает добычу с воздуха и бьёт в пикировании, не давая опомниться.',
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
                    'создан Кровавый Орёл lvl%d (id=%d): hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                    $level, $monsterId, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
                ));
            } else {
                $monsterId = $monster->id;
                $this->command?->info("Кровавый Орёл lvl{$level} уже существует (id={$monsterId})");
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
