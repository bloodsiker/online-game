<?php

declare(strict_types=1);

/**
 * Транзакционный live-run прокачки 1-26 через реальные игровые сервисы и БД.
 *
 * Запуск в локальном Docker-окружении:
 * docker compose -f ../docker-compose.yaml exec -T php-www php scripts/live_progression_runner.php
 * docker compose -f ../docker-compose.yaml exec -T -e LIVE_RUN_GEAR_MODE=shop php-www php scripts/live_progression_runner.php
 *
 * Все созданные игроки, бои, предметы и побочные эффекты откатываются в finally.
 * Последний отчёт сохраняется в storage/app/live_progression_result.json.
 */

use App\Models\Map;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Item\Application\UseCases\EquipItem;
use App\Modules\Item\Application\UseCases\UnequipItem;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Presentation\Http\ItemController;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Application\UseCases\AllocateStats;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Repositories\MonsterOnLocationRepository;
use App\Services\ItemRequirementService;
use App\Services\Recovery\Strategies\FullHealStrategy;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$basePath = dirname(__DIR__);

require $basePath.'/vendor/autoload.php';

$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

mt_srand(20260811);

$targetLevelExclusive = 27;
$startLocationId = 6;
$gearMode = getenv('LIVE_RUN_GEAR_MODE') ?: 'grant';
if (! in_array($gearMode, ['grant', 'shop'], true)) {
    throw new InvalidArgumentException('LIVE_RUN_GEAR_MODE должен быть grant или shop.');
}

$monsterBands = [
    1 => ['map' => 'Канализация', 'name' => 'Канализационная крыса', 'level' => 1],
    2 => ['map' => 'Канализация', 'name' => 'Слепой крот', 'level' => 2],
    3 => ['map' => 'Канализация', 'name' => 'Помойный таракан', 'level' => 3],
    4 => ['map' => 'Канализация', 'name' => 'Плесневый слизень', 'level' => 4],
    5 => ['map' => 'Канализация', 'name' => 'Сточная жаба-переросток', 'level' => 5],
    6 => ['map' => 'Шепчущий Лес', 'name' => 'Лесная Рысь', 'level' => 6],
    7 => ['map' => 'Шепчущий Лес', 'name' => 'Медведь', 'level' => 7],
    9 => ['map' => 'Шепчущий Лес', 'name' => 'Лесная Рысь', 'level' => 9],
    10 => ['map' => 'Шепчущий Лес', 'name' => 'Кабан', 'level' => 10],
    13 => ['map' => 'Шепчущий Лес', 'name' => 'Разбойник', 'level' => 13],
    16 => ['map' => 'Шепчущий Лес', 'name' => 'Медведь', 'level' => 16],
    17 => ['map' => 'Шепчущий Лес', 'name' => 'Лесная Рысь', 'level' => 17],
    18 => ['map' => 'Шепчущий Лес', 'name' => 'Разбойник', 'level' => 18],
    19 => ['map' => 'Забытый Курган', 'name' => 'Костяной Волк', 'level' => 19],
    20 => ['map' => 'Забытый Курган', 'name' => 'Могильная Оса', 'level' => 20],
    22 => ['map' => 'Забытый Курган', 'name' => 'Могильная Оса', 'level' => 22],
    23 => ['map' => 'Забытый Курган', 'name' => 'Костяной Волк', 'level' => 23],
    24 => ['map' => 'Забытый Курган', 'name' => 'Костяной Волк', 'level' => 24],
    25 => ['map' => 'Забытый Курган', 'name' => 'Могильная Оса', 'level' => 25],
    26 => ['map' => 'Забытый Курган', 'name' => 'Броненосный Земляной Червь', 'level' => 26],
];

$gearPlan = [
    ['name' => 'Тесак Головореза', 'level' => 1, 'replace' => 'weapon'],
    ['name' => 'Кожаный доспех', 'level' => 1, 'replace' => 'armor'],
    ['name' => 'Кожаные сапоги', 'level' => 2, 'replace' => null],
    ['name' => 'Кожаные наручи', 'level' => 4, 'replace' => null],
    ['name' => 'Кожаный шлем', 'level' => 7, 'replace' => null],
    ['name' => 'Полуторный меч', 'level' => 10, 'replace' => 'weapon'],
    ['name' => 'Кожаные поножи', 'level' => 10, 'replace' => null],
    ['name' => 'Кожаные наплечники', 'level' => 13, 'replace' => null],
    ['name' => 'Кожаный щит', 'level' => 16, 'replace' => 'shield'],
    ['name' => 'Кожаная кольчуга', 'level' => 20, 'replace' => null],
    ['name' => 'Кастет «Мамонт»', 'level' => 20, 'replace' => 'weapon'],
    ['name' => 'Нагрудник «Мамонт»', 'level' => 20, 'replace' => 'armor'],
    ['name' => 'Сапоги «Мамонт»', 'level' => 22, 'replace' => null],
    ['name' => 'Рукавицы «Мамонт»', 'level' => 25, 'replace' => null],
];

$services = [
    'register' => app(RegisterPlayerProfile::class),
    'allocate' => app(AllocateStats::class),
    'stats' => app(PlayerStatService::class),
    'battleRepo' => app(BattleRepository::class),
    'monsterRepo' => app(MonsterOnLocationRepository::class),
    'fight' => app(FightOrchestrator::class),
    'cart' => app(ShopCartService::class),
    'purchase' => app(PurchaseCart::class),
    'equip' => app(EquipItem::class),
    'unequip' => app(UnequipItem::class),
    'itemUse' => app(ItemController::class),
    'requirements' => app(ItemRequirementService::class),
    'heal' => app(FullHealStrategy::class),
];

$perLevel = [];
for ($level = 1; $level < $targetLevelExclusive; $level++) {
    $perLevel[$level] = [
        'level' => $level,
        'maps' => [],
        'locations' => [],
        'monsters' => [],
        'fights' => 0,
        'wins' => 0,
        'losses' => 0,
        'rounds' => 0,
        'gold' => 0,
        'experience_net' => 0,
        'healing_events' => 0,
        'hp_restored' => 0,
    ];
}

$allocationTotals = [
    'strength' => 0,
    'agility' => 0,
    'intuition' => 0,
    'endurance' => 0,
];
$allocationWeights = [
    'strength' => 0.55,
    'agility' => 0.10,
    'intuition' => 0.10,
    'endurance' => 0.25,
];
$allocationOrder = ['strength', 'endurance', 'agility', 'intuition'];

$allocations = [];
$gearState = [];
foreach ($gearPlan as $gear) {
    $gearState[$gear['name']] = [
        'name' => $gear['name'],
        'required_level' => $gear['level'],
        'price' => null,
        'acquired_at_level' => null,
        'acquisition' => null,
        'equipped_at_level' => null,
        'first_blocker' => null,
    ];
}

$healEvents = [];
$gearSpent = 0;
$gearCatalogValue = 0;
$gearPurchases = 0;
$gearGrants = 0;
$gearEquips = 0;
$gearUnequips = 0;
$fights = 0;
$wins = 0;
$losses = 0;
$combatRounds = 0;
$technicalBattleRounds = 0;
$goldGross = 0;
$startExp = 0;
$safety = 0;
$result = null;
$adaptiveLevel = 1;
$monsterDowngrade = 0;
$lossStreak = 0;
$movementMoves = 0;
$deathReturnMoves = 0;
$zoneTransitionMoves = 0;
$lastFightWasDeath = false;
$stockedFoodItemId = null;
$stockedFoodUnitPrice = 0;
$stockedFoodUsesActual = 0;

$selectMonster = static function (int $playerLevel, int $downgrade = 0) use ($monsterBands): array {
    static $resolved = [];

    $available = [];
    foreach ($monsterBands as $fromLevel => $band) {
        if ($playerLevel >= $fromLevel) {
            $available[] = $band;
        }
    }
    $selected = $available[max(0, count($available) - 1 - $downgrade)];
    $key = $selected['map'].'|'.$selected['name'].'|'.$selected['level'];
    if (isset($resolved[$key])) {
        return $resolved[$key];
    }

    $map = Map::query()->where('name', $selected['map'])->firstOrFail();
    $monster = Monster::query()
        ->where('name', $selected['name'])
        ->where('lvl', $selected['level'])
        ->whereHas('locations', fn ($query) => $query->where('locations.map_id', $map->id))
        ->orderBy('id')
        ->firstOrFail();
    $location = $monster->locations()
        ->where('locations.map_id', $map->id)
        ->orderBy('locations.id')
        ->firstOrFail();

    return $resolved[$key] = compact('map', 'monster', 'location');
};

$allocateFreeStats = static function (Player $player) use (
    &$allocationTotals,
    $allocationWeights,
    $allocationOrder,
    &$allocations,
    $services,
    $targetLevelExclusive,
): Player {
    $player->refresh();
    if ($player->lvl >= $targetLevelExclusive || $player->free_stats <= 0) {
        return $player;
    }

    $delta = [
        'strength' => 0,
        'agility' => 0,
        'intuition' => 0,
        'intelligence' => 0,
        'wisdom' => 0,
        'endurance' => 0,
    ];

    for ($point = 0; $point < $player->free_stats; $point++) {
        $nextTotal = array_sum($allocationTotals) + 1;
        $bestStat = $allocationOrder[0];
        $bestDeficit = -INF;

        foreach ($allocationOrder as $stat) {
            $deficit = $allocationWeights[$stat] * $nextTotal - $allocationTotals[$stat];
            if ($deficit > $bestDeficit) {
                $bestDeficit = $deficit;
                $bestStat = $stat;
            }
        }

        $allocationTotals[$bestStat]++;
        $delta[$bestStat]++;
    }

    $services['allocate']->execute($player, $delta);
    $player->refresh();
    $sheet = $services['stats']->resolve($player);

    $allocations[$player->lvl] = [
        'level' => (int) $player->lvl,
        'allocated' => array_filter($delta),
        'base_stats' => [
            'strength' => (int) floor($player->strength),
            'agility' => (int) floor($player->agility),
            'intuition' => (int) floor($player->intuition),
            'endurance' => (int) floor($player->endurance),
        ],
        'effective' => [
            'hp' => $sheet->getHpMax(),
            'armor' => $sheet->getArmor(),
            'dodge' => $sheet->getDodge(),
            'critical' => $sheet->getCritical(),
            'damage_left' => [$sheet->getLeftHandMinDmg(), $sheet->getLeftHandMaxDmg()],
        ],
    ];

    return $player;
};

$findOwnedItem = static function (User $user, int $shareItemId): ?Backpack {
    return Backpack::query()
        ->with('item.itemInfo')
        ->where('user_id', $user->id)
        ->whereHas('item', fn ($query) => $query->where('share_item_id', $shareItemId))
        ->orderBy('id')
        ->first();
};

$syncGear = static function (User $user) use (
    $gearPlan,
    &$gearState,
    &$gearSpent,
    &$gearCatalogValue,
    &$gearPurchases,
    &$gearGrants,
    &$gearEquips,
    &$gearUnequips,
    $findOwnedItem,
    $services,
    $gearMode,
): User {
    $user->refresh();
    $player = $user->player;
    $desiredGear = [];

    foreach ($gearPlan as $gear) {
        if ($player->lvl < $gear['level']) {
            continue;
        }

        $shareItem = ShareItem::query()->where('name', $gear['name'])->firstOrFail();
        $shopItem = ShopItem::query()
            ->where('share_item_id', $shareItem->id)
            ->orderBy('price')
            ->firstOrFail();
        $gearState[$gear['name']]['price'] = (int) $shopItem->price;

        $backpack = $findOwnedItem($user, $shareItem->id);
        if (! $backpack && $gearMode === 'grant') {
            $item = Item::query()->create(['share_item_id' => $shareItem->id]);
            Backpack::query()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'count' => 1,
            ]);
            $gearCatalogValue += (int) $shopItem->price;
            $gearGrants++;
            $gearState[$gear['name']]['acquired_at_level'] = (int) $player->lvl;
            $gearState[$gear['name']]['acquisition'] = 'grant';
            $user->refresh();
            $player = $user->player;
            $backpack = $findOwnedItem($user, $shareItem->id);
        } elseif (! $backpack && $user->money >= $shopItem->price) {
            $services['cart']->addItem($user, $shopItem->id, 1);
            $purchase = $services['purchase']->execute($user, $shopItem->structure_id);
            if (! $purchase->ok) {
                throw new RuntimeException('Не удалось купить '.$gear['name'].': '.$purchase->message);
            }

            $gearSpent += (int) $shopItem->price;
            $gearCatalogValue += (int) $shopItem->price;
            $gearPurchases++;
            $gearState[$gear['name']]['acquired_at_level'] = (int) $player->lvl;
            $gearState[$gear['name']]['acquisition'] = 'shop';
            $user->refresh();
            $player = $user->player;
            $backpack = $findOwnedItem($user, $shareItem->id);
        }

        $slotKey = $gear['replace'] ?? 'slot:'.$shareItem->slot->value;
        $desiredGear[$slotKey] = [
            'plan' => $gear,
            'share_item' => $shareItem,
            'backpack' => $backpack,
        ];
    }

    foreach ($desiredGear as $slotKey => $desired) {
        /** @var ShareItem $shareItem */
        $shareItem = $desired['share_item'];
        /** @var Backpack|null $backpack */
        $backpack = $desired['backpack'];
        $gearName = $desired['plan']['name'];
        if (! $backpack) {
            continue;
        }

        $user->unsetRelation('player');
        $user->refresh();
        $player = $user->player;
        $player->unsetRelation('playerEquip');
        $equipment = $player->playerEquip;

        $currentItemIds = match ($slotKey) {
            'weapon' => array_filter([$equipment->hand_left]),
            'shield' => array_filter([$equipment->hand_right]),
            default => array_filter([$equipment->{$shareItem->slot->value}]),
        };

        if (in_array($backpack->item_id, $currentItemIds, true)) {
            $gearState[$gearName]['equipped_at_level'] ??= (int) $player->lvl;

            continue;
        }

        $blocker = $services['requirements']->check($player, $shareItem);
        if ($blocker !== null) {
            $gearState[$gearName]['first_blocker'] ??= $blocker;

            continue;
        }

        foreach ($currentItemIds as $currentItemId) {
            if ((int) $currentItemId === (int) $backpack->item_id) {
                continue;
            }
            $services['unequip']->execute($user, (int) $currentItemId);
            $gearUnequips++;
        }

        if ($slotKey === 'weapon' && $shareItem->is_two_hand) {
            $player->unsetRelation('playerEquip');
            $rightHandId = $player->playerEquip->hand_right;
            if ($rightHandId) {
                $services['unequip']->execute($user, (int) $rightHandId);
                $gearUnequips++;
            }
        }

        $user->unsetRelation('player');
        $user->refresh();
        $equipResult = $services['equip']->execute($user, $backpack->item_id);
        $backpack->refresh();

        if ($equipResult->ok && $backpack->equipped) {
            $gearEquips++;
            $gearState[$gearName]['equipped_at_level'] ??= (int) $player->lvl;
        } elseif (! $equipResult->ok) {
            $gearState[$gearName]['first_blocker'] ??= $equipResult->message;
        }
    }

    return $user->refresh();
};

$shortestMoves = static function (int $from, int $to): ?int {
    static $locations = null;
    static $cache = [];

    $cacheKey = $from.':'.$to;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $locations ??= DB::table('locations')->get(['id', 'north', 'south', 'east', 'west', 'up', 'down'])->keyBy('id');
    $queue = [[$from, 0]];
    $seen = [$from => true];

    while ($queue !== []) {
        [$current, $distance] = array_shift($queue);
        if ($current === $to) {
            return $cache[$cacheKey] = $distance;
        }

        $location = $locations->get($current);
        if (! $location) {
            continue;
        }

        foreach (['north', 'south', 'east', 'west', 'up', 'down'] as $direction) {
            $next = $location->$direction;
            if ($next !== null && ! isset($seen[$next])) {
                $seen[$next] = true;
                $queue[] = [(int) $next, $distance + 1];
            }
        }
    }

    return $cache[$cacheKey] = null;
};

$nearestHeal = static function (Location $location) use ($shortestMoves): ?array {
    static $cache = [];

    if (array_key_exists($location->id, $cache)) {
        return $cache[$location->id];
    }

    $best = null;
    foreach (Structure::query()->where('type', Structure::TYPE_HEAL)->get() as $structure) {
        $outboundMoves = $shortestMoves($location->id, (int) $structure->location_id);
        $returnMoves = $shortestMoves((int) $structure->location_id, $location->id);
        if ($outboundMoves === null || $returnMoves === null) {
            continue;
        }

        $candidate = [
            'structure' => $structure,
            'outbound_moves' => $outboundMoves,
            'return_moves' => $returnMoves,
        ];
        if ($best === null
            || $outboundMoves + $returnMoves < $best['outbound_moves'] + $best['return_moves']) {
            $best = $candidate;
        }
    }

    return $cache[$location->id] = $best;
};

$economicalHealing = static function (array $events): array {
    $shopItems = ShopItem::query()
        ->with('item.effects')
        ->whereHas('item.effects', fn ($query) => $query->where('effect_type', ItemEffectType::HEAL_HP->value))
        ->whereIn('structure_id', Structure::query()->where('location_id', 18)->pluck('id'))
        ->get();

    $totals = ['cost' => 0, 'uses' => 0, 'items' => []];

    foreach ($events as $event) {
        $deficit = $event['deficit'];
        $hpMax = $event['hp_max'];
        if ($deficit <= 0) {
            continue;
        }

        $options = [];
        foreach ($shopItems as $shopItem) {
            $effect = $shopItem->item->effects->firstWhere('effect_type', ItemEffectType::HEAL_HP);
            if (! $effect) {
                continue;
            }
            $heal = $effect->value_type->value === 'percent'
                ? (int) ($hpMax * $effect->value / 100)
                : (int) $effect->value;
            if ($heal > 0) {
                $options[] = [
                    'name' => $shopItem->item->name,
                    'heal' => $heal,
                    'cost' => (int) $shopItem->price,
                ];
            }
        }

        $best = array_fill(0, $deficit + 1, null);
        $best[0] = ['cost' => 0, 'uses' => 0, 'items' => []];
        for ($healed = 0; $healed < $deficit; $healed++) {
            if ($best[$healed] === null) {
                continue;
            }
            foreach ($options as $option) {
                $next = min($deficit, $healed + $option['heal']);
                $candidate = [
                    'cost' => $best[$healed]['cost'] + $option['cost'],
                    'uses' => $best[$healed]['uses'] + 1,
                    'items' => $best[$healed]['items'],
                ];
                $candidate['items'][$option['name']] = ($candidate['items'][$option['name']] ?? 0) + 1;
                $current = $best[$next];
                if ($current === null
                    || $candidate['cost'] < $current['cost']
                    || ($candidate['cost'] === $current['cost'] && $candidate['uses'] < $current['uses'])) {
                    $best[$next] = $candidate;
                }
            }
        }

        $eventBest = $best[$deficit];
        if ($eventBest === null) {
            throw new RuntimeException('Не найден вариант лечения предметами.');
        }
        $totals['cost'] += $eventBest['cost'];
        $totals['uses'] += $eventBest['uses'];
        foreach ($eventBest['items'] as $name => $count) {
            $totals['items'][$name] = ($totals['items'][$name] ?? 0) + $count;
        }
    }

    arsort($totals['items']);

    return $totals;
};

DB::beginTransaction();

try {
    $race = Race::query()->where('name', 'Дварф')->firstOrFail();
    Location::findOrFail($startLocationId);

    $user = new User;
    $user->forceFill([
        'name' => 'LIVE_RUN_20260811',
        'email' => 'live-run-20260811@example.test',
        'password' => Hash::make('not-used'),
        'email_verified_at' => now(),
        'last_online_at' => now(),
        'location_id' => $startLocationId,
        'prev_location_id' => $startLocationId,
        'money' => 0,
        'diamond' => 0,
    ])->save();

    $player = $services['register']->execute($user, $race->id);
    $user->refresh();
    Auth::login($user);

    $startExp = (int) $player->exp;
    $player = $allocateFreeStats($player);
    $user->unsetRelation('player');
    $user->refresh();
    $user = $syncGear($user);

    // Курган имеет односторонний вход и не даёт вернуться к фонтану после каждого
    // боя. Заранее кладём реальный стак самой дешёвой еды и используем штатный
    // ItemController только там, где двусторонний маршрут к лечению отсутствует.
    $stockedFood = ShareItem::query()->where('name', 'Груша')->firstOrFail();
    $stockedFoodShopItem = ShopItem::query()
        ->where('share_item_id', $stockedFood->id)
        ->orderBy('price')
        ->firstOrFail();
    $stockedFoodItem = Item::query()->create(['share_item_id' => $stockedFood->id]);
    Backpack::query()->create([
        'user_id' => $user->id,
        'item_id' => $stockedFoodItem->id,
        'count' => 5000,
    ]);
    $stockedFoodItemId = (int) $stockedFoodItem->id;
    $stockedFoodUnitPrice = (int) $stockedFoodShopItem->price;

    while ($user->player->lvl < $targetLevelExclusive) {
        if (++$safety > 2000) {
            throw new RuntimeException('Safety limit: более 2 000 боёв — прогрессия застряла.');
        }

        $user->refresh();
        Auth::setUser($user);
        $player = $user->player;
        $player = $allocateFreeStats($player);
        $user->unsetRelation('player');
        $user->refresh();
        $user = $syncGear($user);
        Auth::setUser($user);
        $player = $user->player;

        $levelAtStart = min($targetLevelExclusive - 1, (int) $player->lvl);
        if ($adaptiveLevel !== $levelAtStart) {
            $adaptiveLevel = $levelAtStart;
            $monsterDowngrade = 0;
            $lossStreak = 0;
        }
        $selection = $selectMonster((int) $player->lvl, $monsterDowngrade);
        /** @var Map $map */
        $map = $selection['map'];
        /** @var Monster $monster */
        $monster = $selection['monster'];
        /** @var Location $location */
        $location = $selection['location'];
        $perLevel[$levelAtStart]['maps'][$map->name] =
            ($perLevel[$levelAtStart]['maps'][$map->name] ?? 0) + 1;
        $locationLabel = $location->name.' (#'.$location->id.')';
        $perLevel[$levelAtStart]['locations'][$locationLabel] =
            ($perLevel[$levelAtStart]['locations'][$locationLabel] ?? 0) + 1;
        $perLevel[$levelAtStart]['monsters'][$monster->name] =
            ($perLevel[$levelAtStart]['monsters'][$monster->name] ?? 0) + 1;

        $currentLocationId = (int) $user->location_id;
        if ($currentLocationId !== (int) $location->id) {
            $moves = $shortestMoves($currentLocationId, (int) $location->id);
            if ($moves === null) {
                throw new RuntimeException("Нет пути из локации {$currentLocationId} в {$location->id}.");
            }
            $movementMoves += $moves;
            if ($lastFightWasDeath) {
                $deathReturnMoves += $moves;
            } else {
                $zoneTransitionMoves += $moves;
            }
        }

        $user->prev_location_id = $currentLocationId;
        $user->location_id = $location->id;
        $user->save();
        $lastFightWasDeath = false;
        Auth::setUser($user);

        $expBefore = (int) $player->exp;
        $moneyBefore = (int) $user->money;

        $locationMonster = $services['monsterRepo']->createMonsterOnLocation($monster, $location);
        $battle = $services['battleRepo']->createBattle($location);
        $services['battleRepo']->createBattleDetails($battle, $user);
        $services['battleRepo']->createBattleDetails($battle, null, $locationMonster);
        $services['battleRepo']->createBattleRound($battle, '<p>Транзакционный live-run.</p>', $user);

        $roundsThisFight = 0;
        $won = false;
        $dead = false;

        while ($roundsThisFight < 1000) {
            $roundsThisFight++;
            $fightDto = $services['fight']->attack($battle->id, $locationMonster->id, 0);
            if ($fightDto->isPlayerDead()) {
                $dead = true;
                break;
            }

            $battle->refresh();
            if ($battle->status->isFinish()) {
                $won = true;
                break;
            }
        }

        if (! $won && ! $dead) {
            throw new RuntimeException('Бой не завершился за 1000 раундов.');
        }

        $battle->refresh();
        $technicalBattleRounds += (int) $battle->rounds;
        $combatRounds += $roundsThisFight;
        $fights++;

        $user->unsetRelation('player');
        $user->refresh();
        Auth::setUser($user);
        $player = $user->player;
        $moneyAfter = (int) $user->money;
        $fightGold = max(0, $moneyAfter - $moneyBefore);
        $goldGross += $fightGold;

        $perLevel[$levelAtStart]['fights']++;
        $perLevel[$levelAtStart]['rounds'] += $roundsThisFight;
        $perLevel[$levelAtStart]['gold'] += $fightGold;
        $perLevel[$levelAtStart]['experience_net'] += (int) $player->exp - $expBefore;

        if ($won) {
            $wins++;
            $perLevel[$levelAtStart]['wins']++;
            $lossStreak = 0;

            $sheet = $services['stats']->resolve($player);
            $deficit = max(0, $sheet->getHpMax() - (int) $player->hp_now);
            if ($deficit > 0) {
                $healSite = $nearestHeal($location);
                /** @var Structure|null $healStructure */
                $healStructure = $healSite['structure'] ?? null;
                $healEvents[] = [
                    'level' => (int) $player->lvl,
                    'deficit' => $deficit,
                    'hp_max' => $sheet->getHpMax(),
                    'fight_location_id' => $location->id,
                    'recovery_mode' => $healStructure ? 'fountain' : 'stocked_item',
                    'heal_structure_id' => $healStructure?->id,
                    'heal_location_id' => $healStructure?->location_id,
                    'outbound_moves' => $healSite['outbound_moves'] ?? null,
                    'return_moves' => $healSite['return_moves'] ?? null,
                ];
                $perLevel[$levelAtStart]['healing_events']++;
                $perLevel[$levelAtStart]['hp_restored'] += $deficit;
                if ($healStructure) {
                    $services['heal']->recover($player, $healStructure);
                } else {
                    while ((int) $player->hp_now < $sheet->getHpMax()) {
                        $response = $services['itemUse']->useItem($stockedFoodItemId);
                        if ($response->getStatusCode() >= 400) {
                            throw new RuntimeException('Не удалось использовать запасённую еду: '.$response->getContent());
                        }
                        $stockedFoodUsesActual++;
                        $player->refresh();
                    }
                }
            }
        } else {
            $losses++;
            $perLevel[$levelAtStart]['losses']++;
            $lossStreak++;
            if ($lossStreak >= 3) {
                $monsterDowngrade++;
                $lossStreak = 0;
            }
            $battle->status = BattleStatus::FINISH;
            $battle->save();
            $lastFightWasDeath = true;
        }

        if ($fights % 10 === 0) {
            $skillProgress = PlayerSkill::query()
                ->where('player_id', $player->id)
                ->where('skill_id', 3)
                ->first();
            fwrite(STDERR, sprintf(
                "progress fights=%d level=%d map=%s location=%d monster=%s downgrade=%d exp=%d/%d wins=%d losses=%d rounds=%d money=%d skill=%s\n",
                $fights,
                $player->lvl,
                $map->name,
                $location->id,
                $monster->name,
                $monsterDowngrade,
                $player->exp,
                $player->exp_up,
                $wins,
                $losses,
                $combatRounds,
                $user->money,
                $skillProgress?->lvl ?? '-',
            ));
        }

        $user->unsetRelation('player');
        $user->refresh();
        $player = $allocateFreeStats($user->player);
        $user->unsetRelation('player');
        $user->refresh();
        $user = $syncGear($user);
    }

    $user->refresh();
    $player = $user->player;
    $sheet = $services['stats']->resolve($player);
    $skill = PlayerSkill::query()->where('player_id', $player->id)->where('skill_id', 3)->first();
    $healing = $economicalHealing($healEvents);

    foreach ($perLevel as &$row) {
        $row['winrate'] = $row['fights'] > 0
            ? round(100 * $row['wins'] / $row['fights'], 2)
            : null;
        $row['avg_rounds'] = $row['fights'] > 0
            ? round($row['rounds'] / $row['fights'], 2)
            : null;
    }
    unset($row);

    $combatSeconds = $combatRounds + $fights;
    $combatAndTravelSeconds = $combatSeconds + $movementMoves;
    $fountainEvents = array_values(array_filter(
        $healEvents,
        static fn (array $event): bool => $event['recovery_mode'] === 'fountain',
    ));
    $fountainUnavailableEvents = count($healEvents) - count($fountainEvents);
    $fountainRecoveryReachableSeconds = array_sum(array_map(
        static fn (array $event): int => $event['outbound_moves'] + $event['return_moves'] + 1,
        $fountainEvents,
    ));
    $naturalRegenSeconds = array_sum(array_map(
        static fn (array $event): int => (int) ceil(900 * $event['deficit'] / $event['hp_max']),
        $healEvents,
    ));
    $healingRoutes = [];
    foreach ($fountainEvents as $event) {
        $routeKey = $event['fight_location_id'].'>'.$event['heal_location_id'];
        if (! isset($healingRoutes[$routeKey])) {
            $fightLocation = Location::findOrFail($event['fight_location_id']);
            $healStructure = Structure::findOrFail($event['heal_structure_id']);
            $healLocation = Location::findOrFail($event['heal_location_id']);
            $healingRoutes[$routeKey] = [
                'fight_location' => ['id' => $fightLocation->id, 'name' => $fightLocation->name],
                'heal_structure' => ['id' => $healStructure->id, 'name' => $healStructure->name],
                'heal_location' => ['id' => $healLocation->id, 'name' => $healLocation->name],
                'moves_outbound' => $event['outbound_moves'],
                'moves_return' => $event['return_moves'],
                'uses' => 0,
            ];
        }
        $healingRoutes[$routeKey]['uses']++;
    }

    $result = [
        'scenario' => [
            'race' => $race->name,
            'build' => 'танк: 55% сила / 10% ловкость / 10% интуиция / 25% выносливость',
            'gear_mode' => $gearMode,
            'farm_strategy' => 'Канализация 1-5, Шепчущий Лес 6-18, Забытый Курган 19-26; после 3 смертей подряд — предыдущий моб до следующего уровня',
            'rng_seed' => 20260811,
            'from_level' => 1,
            'through_level' => 26,
            'stop_level' => (int) $player->lvl,
            'start_location_id' => $startLocationId,
            'target_maps' => ['Канализация', 'Шепчущий Лес', 'Забытый Курган'],
            'monster_bands' => array_values($monsterBands),
        ],
        'summary' => [
            'fights' => $fights,
            'wins' => $wins,
            'losses' => $losses,
            'winrate' => round(100 * $wins / max(1, $fights), 2),
            'combat_rounds' => $combatRounds,
            'technical_battle_rounds' => $technicalBattleRounds,
            'avg_rounds' => round($combatRounds / max(1, $fights), 2),
            'gold_looted_gross' => $goldGross,
            'gear_spent' => $gearSpent,
            'gear_catalog_value' => $gearCatalogValue,
            'money_after_gear' => (int) $user->money,
            'experience_start' => $startExp,
            'experience_end' => (int) $player->exp,
            'deaths' => (int) $player->death,
            'victories_model' => (int) $player->victory,
            'heal_events_after_wins' => count($healEvents),
            'hp_restored_total' => array_sum(array_column($healEvents, 'deficit')),
            'actual_recovery_modes' => array_count_values(array_column($healEvents, 'recovery_mode')),
            'actual_stocked_food_name' => $stockedFood->name,
            'actual_stocked_food_uses' => $stockedFoodUsesActual,
            'actual_stocked_food_cost' => $stockedFoodUsesActual * $stockedFoodUnitPrice,
            'weapon_skill_level' => $skill?->lvl,
            'weapon_skill_exp' => $skill?->exp,
        ],
        'time_seconds' => [
            'combat_plus_start_clicks' => $combatSeconds,
            'all_map_movement' => $movementMoves,
            'combat_start_clicks_and_map_movement' => $combatAndTravelSeconds,
            'stocked_economical_food' => $combatAndTravelSeconds + $healing['uses'],
            'mixed_actual_recovery' => $combatAndTravelSeconds + $fountainRecoveryReachableSeconds + $stockedFoodUsesActual,
            'fountain_every_time' => $fountainUnavailableEvents === 0
                ? $combatAndTravelSeconds + $fountainRecoveryReachableSeconds
                : null,
            'natural_regeneration' => $combatAndTravelSeconds + $naturalRegenSeconds,
            'death_return_only' => $deathReturnMoves,
            'zone_transitions_only' => $zoneTransitionMoves,
            'fountain_unreachable_events' => $fountainUnavailableEvents,
            'fountain_recovery_reachable_only' => $fountainRecoveryReachableSeconds,
            'fountain_recovery_only' => $fountainUnavailableEvents === 0
                ? $fountainRecoveryReachableSeconds
                : null,
            'natural_regeneration_only' => $naturalRegenSeconds,
            'note' => '1 секунда на старт боя/раунд/предмет/переход/фонтан; маршруты считаются по направленным связям locations; null у fountain_every_time означает, что из части боевых клеток нет двустороннего пути к фонтану',
        ],
        'healing_routes' => array_values($healingRoutes),
        'healing_items_economical' => $healing,
        'management_actions' => [
            'stat_allocations' => count($allocations),
            'gear_purchases' => $gearPurchases,
            'gear_grants' => $gearGrants,
            'gear_equips' => $gearEquips,
            'gear_unequips' => $gearUnequips,
        ],
        'final_stats' => [
            'level' => (int) $player->lvl,
            'strength' => $sheet->getStrength(),
            'agility' => $sheet->getAgility(),
            'intuition' => $sheet->getInt(),
            'endurance' => $sheet->getEndurance(),
            'hp' => $sheet->getHpMax(),
            'armor' => $sheet->getArmor(),
            'dodge' => $sheet->getDodge(),
            'critical' => $sheet->getCritical(),
            'damage_left' => [$sheet->getLeftHandMinDmg(), $sheet->getLeftHandMaxDmg()],
            'block' => [$sheet->getBlockChance(), $sheet->getBlockFlat(), $sheet->getBlockPercent()],
        ],
        'allocations' => array_values($allocations),
        'gear' => array_values($gearState),
        'per_level' => array_values($perLevel),
    ];
} finally {
    DB::rollBack();
}

$resultJson = json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
);
file_put_contents($basePath.'/storage/app/live_progression_result.json', $resultJson.PHP_EOL);
echo $resultJson.PHP_EOL;
