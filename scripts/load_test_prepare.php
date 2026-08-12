<?php

declare(strict_types=1);

/**
 * Creates isolated HTTP load-test users and real monster instances.
 * Refuses to run outside the dedicated game_load_test database.
 *
 * docker compose -f ../docker-compose.yaml -f docker-compose.load-test.yaml \
 *   exec -T php-www php scripts/load_test_prepare.php --users=50 --monsters-per-user=10
 */

use App\Models\Experience;
use App\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Repositories\MonsterOnLocationRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$basePath = dirname(__DIR__);

require $basePath.'/vendor/autoload.php';

$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['users::', 'monsters-per-user::', 'output::']);
$userCount = max(1, min(200, (int) ($options['users'] ?? 50)));
$monstersPerUser = max(1, min(100, (int) ($options['monsters-per-user'] ?? 10)));
$output = (string) ($options['output'] ?? $basePath.'/storage/app/load-test/fixture.json');
$database = (string) DB::scalar('select database()');

if ($database !== 'game_load_test') {
    throw new RuntimeException("Refusing to prepare load data in database '{$database}'. Expected game_load_test.");
}

$runId = now()->format('Ymd_His');
$password = 'load-test-only';
$race = Race::query()->where('name', 'Дварф')->firstOrFail();
$fountain = Structure::query()->where('type', Structure::TYPE_HEAL)->orderBy('id')->firstOrFail();
$register = app(RegisterPlayerProfile::class);
$monsterRepository = app(MonsterOnLocationRepository::class);
$statService = app(PlayerStatService::class);

$scenarioDefinitions = [
    [
        'map' => 'Канализация',
        'monster' => 'Канализационная крыса',
        'monster_level' => 1,
        'player_level' => 1,
        'stats' => ['strength' => 4, 'agility' => 2, 'intuition' => 2, 'endurance' => 3],
        'damage' => [3, 5],
    ],
    [
        'map' => 'Шепчущий Лес',
        'monster' => 'Лесная Рысь',
        'monster_level' => 6,
        'player_level' => 6,
        'stats' => ['strength' => 18, 'agility' => 6, 'intuition' => 6, 'endurance' => 10],
        'damage' => [8, 12],
    ],
    [
        'map' => 'Забытый Курган',
        'monster' => 'Костяной Волк',
        'monster_level' => 19,
        'player_level' => 19,
        'stats' => ['strength' => 55, 'agility' => 15, 'intuition' => 15, 'endurance' => 25],
        'damage' => [25, 38],
    ],
];

$scenarios = array_map(static function (array $definition): array {
    $map = Map::query()->where('name', $definition['map'])->firstOrFail();
    $monster = Monster::query()
        ->where('name', $definition['monster'])
        ->where('lvl', $definition['monster_level'])
        ->whereHas('locations', fn ($query) => $query->where('locations.map_id', $map->id))
        ->orderBy('id')
        ->firstOrFail();
    $location = $monster->locations()
        ->where('locations.map_id', $map->id)
        ->orderBy('locations.id')
        ->firstOrFail();

    return array_merge($definition, compact('map', 'monster', 'location'));
}, $scenarioDefinitions);

$accounts = DB::transaction(function () use (
    $userCount,
    $monstersPerUser,
    $runId,
    $password,
    $race,
    $register,
    $monsterRepository,
    $statService,
    $scenarios,
): array {
    $accounts = [];

    for ($index = 1; $index <= $userCount; $index++) {
        $scenario = $scenarios[($index - 1) % count($scenarios)];
        $email = sprintf('load-test-%s-%03d@example.test', $runId, $index);

        $user = new User;
        $user->forceFill([
            'name' => sprintf('LOAD_TEST_%s_%03d', $runId, $index),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'last_online_at' => now(),
            'location_id' => $scenario['location']->id,
            'prev_location_id' => $scenario['location']->id,
            'money' => 0,
            'diamond' => 0,
        ])->save();

        $player = $register->execute($user, $race->id);
        $experience = Experience::query()->where('lvl', $scenario['player_level'])->firstOrFail();
        $player->forceFill([
            'lvl' => $scenario['player_level'],
            'exp' => (int) $experience->exp,
            'exp_up' => (int) $experience->exp + (int) $experience->exp_diff,
            'exp_diff' => (int) $experience->exp_diff,
            'strength' => $scenario['stats']['strength'],
            'agility' => $scenario['stats']['agility'],
            'intuition' => $scenario['stats']['intuition'],
            'endurance' => $scenario['stats']['endurance'],
            'min_dmg' => $scenario['damage'][0],
            'max_dmg' => $scenario['damage'][1],
            'free_stats' => 0,
        ])->save();

        $sheet = $statService->resolve($player->fresh());
        $player->forceFill([
            'hp_now' => $sheet->getHpMax(),
            'hp_max' => $sheet->getHpMax(),
            'mp_now' => $sheet->getMpMax(),
            'mp_max' => $sheet->getMpMax(),
        ])->save();

        $monsterIds = [];
        for ($monsterIndex = 0; $monsterIndex < $monstersPerUser; $monsterIndex++) {
            $monsterIds[] = $monsterRepository
                ->createMonsterOnLocation($scenario['monster'], $scenario['location'])
                ->id;
        }

        $accounts[] = [
            'user_id' => $user->id,
            'email' => $email,
            'password' => $password,
            'map' => $scenario['map']->name,
            'location_id' => $scenario['location']->id,
            'location' => $scenario['location']->name,
            'monster' => $scenario['monster']->name,
            'monster_level' => (int) $scenario['monster']->lvl,
            'player_level' => (int) $player->lvl,
            'monster_instance_ids' => $monsterIds,
        ];
    }

    return $accounts;
});

$fixture = [
    'created_at' => now()->toIso8601String(),
    'database' => $database,
    'run_id' => $runId,
    'fountain_structure_id' => $fountain->id,
    'users' => count($accounts),
    'monsters_per_user' => $monstersPerUser,
    'accounts' => $accounts,
];

$directory = dirname($output);
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    throw new RuntimeException("Unable to create output directory: {$directory}");
}

file_put_contents(
    $output,
    json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
);

echo json_encode([
    'database' => $database,
    'run_id' => $runId,
    'users' => count($accounts),
    'monsters' => count($accounts) * $monstersPerUser,
    'maps' => array_values(array_unique(array_column($accounts, 'map'))),
    'fixture' => $output,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
