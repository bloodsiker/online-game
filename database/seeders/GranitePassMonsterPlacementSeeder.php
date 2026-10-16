<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров «Гранитного Перевала» (map_id=13, локации 1279-1529)
 * в пул респавна `location_has_monsters` — по прямому указанию пользователя
 * на каждой локации стоит 2-3 монстра РАЗНОГО ВИДА (никогда два разных
 * уровня одного и того же вида одновременно на одной локации), с плавным
 * ростом сложности по глубине зоны (вход 1279 без монстров).
 *
 * 5 видов в порядке возрастания силы: Каменный Голем(45-47), Кристальный
 * скарабей(48-50), Ледяной Волк(51-53), Гранитный Голем(54-56),
 * Горный Грифон(57-59).
 *
 * Зона делится на 5 полос по 50 локаций — одна полоса на вид-«центр». На
 * каждой полосе центральный вид дополняется СОСЕДНИМИ по силе видами:
 * старшим щаблем вида из предыдущей полосы (если есть) и младшим щаблем
 * вида из следующей полосы (если есть) — у крайних полос соседа с одной
 * стороны нет, поэтому там ровно 2 вида, у средних — 3.
 *
 * Внутри полосы (50 локаций) сама полоса делится на 3 под-отрезка — каждый
 * со своим щаблем центрального вида (младший/средний/старший), чтобы
 * сложность росла и внутри полосы, а не только между полосами целиком.
 * Соседние виды держат фиксированный щабль на всю полосу (старший — у
 * «ветерана» из предыдущей полосы, младший — у «превью» из следующей).
 */
class GranitePassMonsterPlacementSeeder extends Seeder
{
    /**
     * Виды по порядку силы, каждый — [ucommon] 3 уровня по возрастанию.
     *
     * @var array<int, array{name: string, levels: array{0:int,1:int,2:int}}>
     */
    private const SPECIES = [
        ['name' => 'Каменный Голем', 'levels' => [45, 46, 47]],
        ['name' => 'Кристальный скарабей', 'levels' => [48, 49, 50]],
        ['name' => 'Ледяной Волк', 'levels' => [51, 52, 53]],
        ['name' => 'Гранитный Голем', 'levels' => [54, 55, 56]],
        ['name' => 'Горный Грифон', 'levels' => [57, 58, 59]],
    ];

    private const ZONE_FROM = 1280;

    private const ZONE_TO = 1529;

    private const BAND_SIZE = 50;

    public function run(): void
    {
        $monsterIds = $this->resolveAllMonsterIds();

        $locationIds = DB::table('locations')
            ->whereBetween('id', [self::ZONE_FROM, self::ZONE_TO])
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $bands = $locationIds->chunk(self::BAND_SIZE)->values();
        $inserted = 0;

        foreach ($bands as $bandIndex => $bandLocationIds) {
            $species = self::SPECIES[$bandIndex];
            $prevSpecies = self::SPECIES[$bandIndex - 1] ?? null;
            $nextSpecies = self::SPECIES[$bandIndex + 1] ?? null;

            $veteranMonsterId = $prevSpecies !== null
                ? $monsterIds[$prevSpecies['name']][$prevSpecies['levels'][2]]
                : null;
            $previewMonsterId = $nextSpecies !== null
                ? $monsterIds[$nextSpecies['name']][$nextSpecies['levels'][0]]
                : null;

            $subChunks = $bandLocationIds->chunk((int) ceil($bandLocationIds->count() / 3))->values();

            $rows = [];
            foreach ($subChunks as $tierIndex => $subChunkLocationIds) {
                $centerLevel = $species['levels'][min($tierIndex, 2)];
                $centerMonsterId = $monsterIds[$species['name']][$centerLevel];

                $rowMonsterIds = array_values(array_filter([$centerMonsterId, $veteranMonsterId, $previewMonsterId]));

                foreach ($subChunkLocationIds as $locationId) {
                    foreach ($rowMonsterIds as $monsterId) {
                        $rows[] = ['location_id' => $locationId, 'monster_id' => $monsterId, 'aggression' => null];
                    }
                }
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('location_has_monsters')->insert($chunk);
                $inserted += count($chunk);
            }

            $this->command?->info(sprintf(
                'полоса %d (%s): %d локаций, %d строк, видов на локацию=%d',
                $bandIndex + 1, $species['name'], $bandLocationIds->count(), count($rows),
                count(array_filter([true, $veteranMonsterId, $previewMonsterId])),
            ));
        }

        $this->command?->info("GranitePassMonsterPlacementSeeder: вставлено строк — {$inserted}");
    }

    /** @return array<string, array<int, int>> имя вида => [уровень => monster_id] */
    private function resolveAllMonsterIds(): array
    {
        $result = [];
        foreach (self::SPECIES as $species) {
            foreach ($species['levels'] as $level) {
                $monster = LocalizedContentSeederQuery::forTable('monsters')->where('name', $species['name'])->where('lvl', $level)->first();
                if ($monster === null) {
                    $this->command?->warn("не найден монстр «{$species['name']}» lvl {$level}");

                    continue;
                }
                $result[$species['name']][$level] = $monster->id;
            }
        }

        return $result;
    }
}
