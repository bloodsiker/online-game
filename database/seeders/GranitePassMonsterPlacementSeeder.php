<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров GranitePassMonsterSeeder по локациям «Гранитного
 * Перевала» (1280-1529, без входа 1279). Карта делится пополам по уровню —
 * та же логика пула, что в OvergrownRoadMonsterPlacementSeeder.
 */
class GranitePassMonsterPlacementSeeder extends Seeder
{
    private const FIRST_HALF_RANGE = [1280, 1404];

    private const SECOND_HALF_RANGE = [1405, 1529];

    private const FIRST_HALF_MONSTERS = [
        ['Гранитный Голем', 45],
        ['Кристальный скарабей', 45],
    ];

    private const SECOND_HALF_MONSTERS = [
        ['Гранитный Голем', 49],
        ['Кристальный скарабей', 49],
    ];

    private const PERCENT_RESPAWN_MONSTER = 70;

    private const TIME_NOT_ATTACK = 15;

    private const COUNT_MONSTER_CYCLE = [1, 2, 1, 2, 3];

    public function run(): void
    {
        $firstHalfIds = $this->monsterIds(self::FIRST_HALF_MONSTERS);
        $secondHalfIds = $this->monsterIds(self::SECOND_HALF_MONSTERS);

        $placedLocations = $this->placeMonsters(self::FIRST_HALF_RANGE, $firstHalfIds)
            + $this->placeMonsters(self::SECOND_HALF_RANGE, $secondHalfIds);

        $this->command?->info("GranitePassMonsterPlacementSeeder: расставлено локаций — {$placedLocations}");
    }

    /** @param  array<int, array{0: string, 1: int}>  $pairs
     * @return array<int, int> */
    private function monsterIds(array $pairs): array
    {
        return array_map(
            fn (array $pair) => Monster::where('name', $pair[0])->where('lvl', $pair[1])->value('id')
                ?? throw new \RuntimeException("Монстр «{$pair[0]}» lvl {$pair[1]} не найден — прогоните GranitePassMonsterSeeder сначала"),
            $pairs,
        );
    }

    /**
     * @param  array{0: int, 1: int}  $range
     * @param  int[]  $monsterIds
     */
    private function placeMonsters(array $range, array $monsterIds): int
    {
        [$min, $max] = $range;
        $now = now();
        $rows = [];
        $locationUpdates = [];

        for ($locationId = $min; $locationId <= $max; $locationId++) {
            foreach ($monsterIds as $monsterId) {
                $rows[] = [
                    'location_id' => $locationId,
                    'monster_id' => $monsterId,
                    'aggression' => null,
                ];
            }

            $locationUpdates[] = $locationId;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_has_monsters')->insert($chunk);
        }

        foreach ($locationUpdates as $locationId) {
            $countMonster = self::COUNT_MONSTER_CYCLE[$locationId % 5];

            DB::table('locations')->where('id', $locationId)->update([
                'count_monster' => $countMonster,
                'percent_respawn_monster' => self::PERCENT_RESPAWN_MONSTER,
                'time_not_attack' => self::TIME_NOT_ATTACK,
                'updated_at' => $now,
            ]);
        }

        return count($locationUpdates);
    }
}
