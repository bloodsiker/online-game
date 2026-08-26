<?php

declare(strict_types=1);

/**
 * Повторное профилирование SQL: переход между локациями и обычный раунд боя.
 * Методика DATABASE_SQL_PROFILE.md: 5 прогонов сценария внутри транзакции
 * с откатом, счётчик запросов через DB::listen, запуск в контейнере:
 *
 *   docker exec onlinegame-php-www-1 php scripts/reprofile_sql.php
 */

use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Location\Application\UseCases\MoveToLocation;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Repositories\MonsterOnLocationRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$basePath = dirname(__DIR__);

require $basePath.'/vendor/autoload.php';

$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

const RUNS = 5;

// ── Счётчик запросов ────────────────────────────────────────────────────────
$counter = ['n' => 0, 'ms' => 0.0];
$fingerprints = [];

DB::listen(function ($query) use (&$counter, &$fingerprints) {
    $counter['n']++;
    $counter['ms'] += $query->time;
    $fp = preg_replace('/\b\d+\b/', '?', $query->sql);
    $fingerprints[$fp] = ($fingerprints[$fp] ?? 0) + 1;
});

function resetCounters(array &$counter, array &$fingerprints): void
{
    $counter['n'] = 0;
    $counter['ms'] = 0.0;
    $fingerprints = [];
}

// ── Фикстура: игрок и направления ───────────────────────────────────────────
$user = User::with('player')->findOrFail(1);
Auth::setUser($user);
$player = $user->player;

/** @var Location $current */
$current = Location::query()->findOrFail((int) $user->location_id);
$direction = null;
foreach (['north', 'south', 'west', 'east'] as $dir) {
    if (! empty($current->{$dir})) {
        $direction = $dir;
        break;
    }
}
if ($direction === null) {
    throw new RuntimeException('У текущей локации нет соседей для тестового перехода.');
}

// Монстр с большим запасом HP, чтобы одиночный раунд его не убил
$spawn = \App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation::query()
    ->with(['monster', 'location'])
    ->where('active', 1)
    ->orderByDesc('hp_max')
    ->first();
if ($spawn === null) {
    throw new RuntimeException('Не найден активный монстр для боя.');
}
$monster = $spawn->monster;
$battleLocation = $spawn->location;

$moveTo = app(MoveToLocation::class);
$fight = app(FightOrchestrator::class);
$monsterRepo = app(MonsterOnLocationRepository::class);
$battleRepo = app(BattleRepository::class);

// ── Сценарий: переход между локациями (+ полная отрисовка страницы) ────────
$runMovement = function () use ($moveTo, $user, $direction): int {
    $page = $moveTo->execute($user, $direction);

    return strlen(view('location::index', ['page' => $page])->render());
};

// ── Сценарий: один обычный раунд боя ────────────────────────────────────────
$runRound = function () use ($fight, $battleRepo, $monsterRepo, $user, $monster, $battleLocation): void {
    $locationMonster = $monsterRepo->createMonsterOnLocation($monster, $battleLocation);
    $battle = $battleRepo->createBattle($battleLocation);
    $battleRepo->createBattleDetails($battle, $user);
    $battleRepo->createBattleDetails($battle, null, $locationMonster);
    $battleRepo->createBattleRound($battle, '<p>reprofile</p>', $user);

    $fight->attack($battle->id, $locationMonster->id, 0);
};

// ── Сценарий: бой до убийства монстра (дроп + квесты) ──────────────────────
$weakSpawn = \App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation::query()
    ->with(['monster', 'location'])
    ->where('active', 1)
    ->where('hp_max', '>', 0)
    ->orderBy('hp_max')
    ->first();
$killMonster = $weakSpawn?->monster ?? $monster;
$killLocation = $weakSpawn?->location ?? $battleLocation;

$runKill = function () use ($fight, $battleRepo, $monsterRepo, $user, $killMonster, $killLocation): void {
    // Полное здоровье перед сценарием, чтобы не погибнуть
    $user->player->forceFill(['hp_now' => $user->player->hp_max])->save();

    $locationMonster = $monsterRepo->createMonsterOnLocation($killMonster, $killLocation);
    $battle = $battleRepo->createBattle($killLocation);
    $battleRepo->createBattleDetails($battle, $user);
    $battleRepo->createBattleDetails($battle, null, $locationMonster);
    $battleRepo->createBattleRound($battle, '<p>reprofile</p>', $user);

    for ($i = 0; $i < 200; $i++) {
        $dto = $fight->attack($battle->id, $locationMonster->id, 0);
        if ($dto->isPlayerDead()) {
            throw new RuntimeException('Игрок погиб во время замера убийства.');
        }
        $battle->refresh();
        if ($battle->status->isFinish()) {
            return;
        }
    }
    throw new RuntimeException('Монстр не умер за 200 раундов.');
};

$measure = function (callable $scenario) use (&$counter, &$fingerprints): array {
    resetCounters($counter, $fingerprints);
    DB::beginTransaction();
    try {
        $t0 = hrtime(true);
        $bytes = $scenario();
        $elapsedMs = (hrtime(true) - $t0) / 1e6;

        return [
            'sql' => $counter['n'],
            'sqlMs' => round($counter['ms'], 1),
            'totalMs' => round($elapsedMs, 1),
            'outputBytes' => $bytes ?? 0,
            'fingerprints' => $fingerprints,
        ];
    } finally {
        DB::rollBack();
        if (DB::transactionLevel() > 0) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
    }
};

echo "Игрок #{$player->id}, локация {$current->id}, направление '{$direction}', монстр #{$monster->id} (hp={$monster->hp_max})\n\n";

foreach (['ПЕРЕХОД МЕЖДУ ЛОКАЦИЯМИ' => $runMovement, 'ОБЫЧНЫЙ РАУНД БОЯ' => $runRound, 'БОЙ ДО УБИЙСТВА (дроп+квесты)' => $runKill] as $title => $scenario) {
    echo "=== {$title} ===\n";
    $results = [];
    $lastFingerprints = [];
    for ($i = 1; $i <= RUNS; $i++) {
        $r = $measure($scenario);
        $results[] = $r;
        $lastFingerprints = $r['fingerprints'];
        echo sprintf(
            "  прогон %d: SQL=%d (%s мс SQL, %s мс всего)\n",
            $i,
            $r['sql'],
            $r['sqlMs'],
            $r['totalMs'],
        );
    }

    $sqls = array_column($results, 'sql');
    sort($sqls);
    $median = $sqls[intdiv(count($sqls), 2)];
    echo sprintf("  медиана SQL: %d\n", $median);

    arsort($lastFingerprints);
    echo "  повторяющиеся шаблоны последнего прогона:\n";
    foreach ($lastFingerprints as $fp => $cnt) {
        if ($cnt < 2) {
            continue;
        }
        echo sprintf("    %3d x %s\n", $cnt, substr($fp, 0, 110));
    }
    echo "\n";
}
