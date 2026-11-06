<?php

declare(strict_types=1);

use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Application\UseCases\RunAway;
use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Domain\Services\PlayerMovementService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\BattleTestDoubles;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('BATTLE_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_battle_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Battle race worker refuses non-isolated database.');
}
BattleTestDoubles::bind();
[$operation, $userId, $battleId, $extra, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
$staleMonster = $operation === 'regen' ? MonsterOnLocation::findOrFail(1) : null;
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Battle race barrier timed out.');
    }
    usleep(10000);
}
try {
    Auth::setUser($user);
    $result = match ($operation) {
        'entry' => app(BattleService::class)->attackMonster(Location::findOrFail(1), 1),
        'auto' => app(BattleService::class)->battleOnLocation(Location::findOrFail(1)),
        'hit' => app(FightOrchestrator::class)->attack((int) $battleId, 1, 0),
        'move' => (new ReflectionMethod(PlayerMovementService::class, 'applyMove'))->invoke(app(PlayerMovementService::class), $user, 2),
        'escape' => app(RunAway::class)->execute($user, (int) $battleId),
        'wave' => app(DungeonCoordinator::class)->tryAdvanceSurvivalWave(DungeonSession::findOrFail((int) ($extra ?: 1)), Location::findOrFail(1)),
        'expire' => app(DungeonCoordinator::class)->expireSessionIfNeeded($user),
        'regen' => $staleMonster->regenerate(),
    };
    echo json_encode(['ok' => true]);
} catch (HttpException|ModelNotFoundException $exception) {
    echo json_encode(['ok' => false]);
}
