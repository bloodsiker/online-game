<?php

declare(strict_types=1);

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Application\Services\Combat\MonsterAttackService;
use App\Modules\Interface\Application\UseCases\HeartbeatPlayer;
use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\Support\BattleTestDoubles;
use Tests\Support\MagicStateTestDoubles;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('MAGIC_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_magic_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Magic race worker refuses non-isolated database.');
}
[$operation, $userId, $argument, $extra, $barrier, $workerId] = array_slice($argv, 1);
Carbon::setTestNow('2026-11-03 12:00:20');
MagicStateTestDoubles::bind();
$user = User::without('player')->findOrFail((int) $userId);
$user->load('player.user');
Auth::setUser($user);
readonly class MagicRaceCounterattack extends MonsterAttackService
{
    public function __construct() {}

    public function execute(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        $player->hp_now = max(0, $player->hp_now - 10);
        $player->save();
    }
}

if ($operation === 'fight') {
    BattleTestDoubles::bind();
    MagicStateTestDoubles::bind();
    app()->instance(MonsterAttackService::class, new MagicRaceCounterattack);
}
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Magic race barrier timed out.');
    }
    usleep(10000);
}
$result = match ($operation) {
    'cast' => app(UseMagicSkill::class)->execute($user, (int) $argument, $extra === '' ? null : (int) $extra)->status === 'success',
    'guard' => app(MagicCastGuard::class)->tryConsume($user->player, MagicSkill::findOrFail((int) $argument))->ok,
    'combat_guard' => DB::transaction(function () use ($user, $argument): bool {
        $player = Player::whereKey($user->player_id)->lockForUpdate()->firstOrFail();

        return app(MagicCastGuard::class)->tryConsume($player, MagicSkill::findOrFail((int) $argument))->ok;
    }),
    'heartbeat' => app(HeartbeatPlayer::class)->execute($user, touchOnline: false),
    'recalculate' => Artisan::call('player:recalc-hp') === 0,
    'fight' => app(FightOrchestrator::class)->attack((int) $argument, 1, 0),
    default => throw new LogicException('Unknown magic race operation.'),
};
$ok = is_bool($result) ? $result : true;
// SQL errors/deadlocks are deliberately unhandled: they must fail the test.
echo json_encode(['ok' => $ok], JSON_THROW_ON_ERROR);
