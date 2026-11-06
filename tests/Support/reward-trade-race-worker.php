<?php

declare(strict_types=1);

use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use App\Modules\Referral\Application\Listeners\GrantReferralReward;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesRewardTradeFixture;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('REWARD_TRADE_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_reward_trade_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Reward/trade worker refuses non-isolated database.');
}
[$operation, $userId, $uuid, $barrier, $workerId] = array_slice($argv, 1);
Carbon::setTestNow('2026-11-03 12:01:00');
$fixture = new class
{
    use CreatesRewardTradeFixture;

    public function bind(): void
    {
        $this->bindRewardTradeDoubles();
    }
};
$fixture->bind();
$user = User::findOrFail((int) $userId);
$player = Player::findOrFail((int) $userId);
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Reward/trade barrier timed out.');
    }
    usleep(10000);
}
try {
    $service = app(PlayerTradeService::class);
    match ($operation) {
        'reward' => app(GrantReferralReward::class)->handle(new PlayerLeveledUp($player)),
        'confirm' => $service->confirm($user, $uuid),
        'cancel' => $service->cancel($user, $uuid),
        'page' => $service->page($user, $uuid),
        'offer' => $service->saveOffer($user, $uuid, [1 => 2], 30),
        default => throw new LogicException('Unknown reward/trade operation.'),
    };
    $ok = true;
} catch (QueryException $exception) {
    throw $exception;
} catch (RuntimeException $exception) {
    if ($operation === 'reward') {
        throw $exception;
    }
    $ok = false;
}
echo json_encode(['ok' => $ok], JSON_THROW_ON_ERROR);
