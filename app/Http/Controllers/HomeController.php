<?php

namespace App\Http\Controllers;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Location\Infrastructure\Persistence\Models\MapGatheringResource;
use App\Modules\Monster\Domain\Services\MapMonstersCache;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function login($id)
    {
        $user = User::find($id);
        Auth::login($user, true);

        return redirect()->route('game');
        //        return view('homepage');
    }

    public function gebug()
    {
        $diff = time() - Carbon::parse('2024-09-20 15:53')->timestamp;
        dd($diff / 60);
        //        $user = User::find(1);
        //        $player = $user->player;
        //        $player = app(\App\Services\PlayerStatService::class)->resolve($player);
        //
        //        echo "Min Left dmg: " . $player->getLeftHandMinDmg() . PHP_EOL . "\n";
        //        echo "Max Left dmg: " . $player->getLeftHandMaxDmg() . PHP_EOL . PHP_EOL;
        //        echo "Min Right dmg: " . $player->getRightHandMinDmg() . PHP_EOL . "\n";
        //        echo "Max Right dmg: " . $player->getRightHandMaxDmg() . PHP_EOL . PHP_EOL;
        //        echo "Сила: " . $player->getStrength() . PHP_EOL . PHP_EOL;
        //        echo "Ловкость: " . $player->getAgility(). PHP_EOL;
        //        echo "Интеллект: " . $player->getIntelligence();

        //        dd($finalDamage = 120 * (500 / (500 + 900)));

        $attaker = new \StdClass;
        $attaker->min_dmg = 5;
        $attaker->max_dmg = 20;
        $attaker->crit = 100;
        $attaker->armor = 15;

        $defender = new \StdClass;
        $defender->min_dmg = 3;
        $defender->max_dmg = 16;
        $defender->crit = 200;
        $defender->armor = 25;

        $dodge = $this->calculateDodge(200, 500);
        $crit = $this->calculateCrit(1000, 1500);
        $dmg = $this->calculateDamage($attaker, $defender);
        dd($dodge, $crit, $dmg);
    }

    /**
     * Расчет уворота на основе ловкости атакующего и защищающегося
     */
    protected function calculateDodge($attackerAgility, $defenderAgility)
    {
        return $dodgeChance = max(0, min(100, 50 + ($defenderAgility - $attackerAgility) * 0.05));  // Линейная зависимость

        return mt_rand(0, 100) < $dodgeChance;
    }

    /**
     * Расчет шанса крита на основе параметров атакующего и устойчивости к критам защитника
     */
    protected function calculateCrit($attackerCrit, $defenderCritResistance)
    {
        $critChance = max(0, min(100, 50 + ($attackerCrit - $defenderCritResistance) * 0.05));

        //        return $critChance = max(0, min(100, 50 + ($attackerCrit - $defenderCritResistance) * 0.05));
        return mt_rand(0, 100) < $critChance;
    }

    /**
     * Расчет урона с учетом брони и шанса крита
     */
    protected function calculateDamage($attacker, $defender)
    {
        $baseDamage = mt_rand($attacker->min_dmg, $attacker->max_dmg);

        // Рассчитываем шанс крита на основе параметров
        if ($this->calculateCrit($attacker->crit, $defender->crit)) {
            $baseDamage *= 2;  // Критический удар
        }

        // Учитываем броню защитника
        $finalDamage = $baseDamage * (500 / (500 + $defender->armor)); // Чем выше броня, тем меньше урон

        return max(0, round($finalDamage));
    }

    protected function calculateExperience()
    {
        $playerLvl = 12;
        $monsterLvl = 1;
        $damage = 10;
        $hpMonster = 10;
        $baseExperience = 100;
        $takeExp = $damage * $baseExperience / $hpMonster;
        $levelDifference = $playerLvl - $monsterLvl;
        $experienceMultiplier = max(0.01, 1 - 0.05 * $levelDifference); // Уменьшение опыта на 5% за каждый уровень

        // Опыт рассчитывается как базовый опыт, умноженный на урон и корректировка по уровню
        dump(max(1, $takeExp * $experienceMultiplier));
    }

    public function map()
    {
        return view('maps.city.main.map');
    }

    public function publicMap(string $slug)
    {
        $map = Map::query()->where('slug', $slug)->firstOrFail();
        $folder = trim((string) $map->folder, '/');
        abort_if($folder === '', 404);

        $view = 'maps.'.str_replace('/', '.', $folder).'.map';
        abort_unless(view()->exists($view), 404);

        $currentLocation = Auth::user()?->loadMissing('currentLocation.map')->currentLocation;
        $currentMap = $currentLocation?->map;

        return view($view, [
            'map' => $map,
            'currentMapUrl' => $currentMap?->slug !== null
                ? route('map.public', ['slug' => $currentMap->slug]).'#'.$currentLocation->id
                : null,
        ]);
    }

    public function publicMapMonsters(Map $map): JsonResponse
    {
        $monsters = Cache::remember(
            MapMonstersCache::key($map->id),
            now()->addMinutes(MapMonstersCache::TTL_MINUTES),
            function () use ($map): Collection {
                return Monster::query()
                    ->whereHas('locations', fn ($query) => $query->where('locations.map_id', $map->id))
                    ->orderBy('name')
                    ->get(['id', 'name', 'lvl', 'image', 'is_boss'])
                    ->groupBy('name')
                    ->map(static function ($sameNameMonsters): array {
                        /** @var Monster $monster */
                        $monster = $sameNameMonsters->first();

                        return [
                            'name' => (string) $monster->name,
                            'levels' => $sameNameMonsters
                                ->sortBy('lvl')
                                ->groupBy('lvl')
                                ->map(static function ($sameLevelMonsters): array {
                                    /** @var Monster $monster */
                                    $monster = $sameLevelMonsters->first();

                                    return [
                                        'value' => (int) $monster->lvl,
                                        'info_url' => route('info.monster.catalog', ['id' => $monster->id]),
                                    ];
                                })
                                ->values()
                                ->all(),
                            'image' => $monster->image,
                            'is_boss' => $sameNameMonsters->contains(static fn (Monster $item): bool => $item->isBoss()),
                        ];
                    })
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();
            },
        );

        return response()->json([
            'map' => $map->name,
            'monsters' => $monsters,
        ]);
    }

    public function publicMapResources(Map $map): JsonResponse
    {
        $resources = MapGatheringResource::query()
            ->where('map_id', $map->id)
            ->with('resource.skill')
            ->get()
            ->pluck('resource')
            ->filter()
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(static fn ($resource): array => [
                'name' => (string) $resource->name,
                'image' => $resource->transparent_image ?? $resource->image,
                'skill_name' => (string) ($resource->skill?->name ?? 'Умение'),
                'required_level' => max(1, (int) $resource->skill_lvl),
                'info_url' => route('items.info.share', ['id' => $resource->id]),
            ]);

        return response()->json([
            'map' => $map->name,
            'resources' => $resources,
        ]);
    }

    public function map2()
    {
        return view('maps.subcity.main.map');
    }

    public function map3()
    {
        return view('maps.catacomb_sacrifice.map');
    }

    public function map4()
    {
        return view('maps.city.sewers.map');
    }

    public function map5()
    {
        return view('maps.subcity.zabytiy_kurgan.map');
    }

    public function map6()
    {
        return view('maps.subcity.overgrown_road.map');
    }

    public function map7()
    {
        return view('maps.subcity.watch_hills.map');
    }

    public function map8()
    {
        return view('maps.subcity.granite_pass.map');
    }

    public function clear()
    {
        Artisan::call('cache:clear');
    }
}
