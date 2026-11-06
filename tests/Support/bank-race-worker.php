<?php

declare(strict_types=1);

use App\Modules\Structure\Bank\Application\UseCases\ClaimDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Deposit;
use App\Modules\Structure\Bank\Application\UseCases\OpenDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Transfer;
use App\Modules\Structure\Bank\Application\UseCases\Withdraw;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('BANK_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_bank_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Bank race worker refuses non-isolated database.');
}
[$operation, $userId, $amount, $extra, $barrier, $workerId] = array_slice($argv, 1);
// Both workers load the same pre-operation snapshot, reproducing stale request models.
$user = User::without('player')->findOrFail((int) $userId);
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Bank race barrier timed out.');
    }
    usleep(10000);
}
$result = match ($operation) {
    'deposit' => app(Deposit::class)->execute($user, (int) $amount),
    'withdraw' => app(Withdraw::class)->execute($user, (int) $amount),
    'claim' => app(ClaimDeposit::class)->execute($user, (int) $extra),
    'open' => app(OpenDeposit::class)->execute($user, (int) $amount, (int) $extra),
    'transfer' => app(Transfer::class)->execute($user, $extra, (int) $amount),
    default => throw new RuntimeException('Unknown bank race operation.'),
};
echo json_encode(['ok' => $result->ok, 'message' => $result->message], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
