<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('WALLET_CONSTRAINT_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_wallet_constraint_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Wallet constraint worker refuses non-isolated database.');
}
[$column, $delta, $barrier, $workerId] = array_slice($argv, 1);
if (! in_array($column, ['money', 'diamond'], true)) {
    throw new RuntimeException('Invalid wallet column.');
}
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Wallet constraint barrier timed out.');
    }
    usleep(10000);
}
try {
    DB::transaction(function () use ($column, $delta): void {
        // Deliberately omit application guards: test the final DB backstop itself.
        $other = $column === 'money' ? 'diamond' : 'money';
        DB::table('users')->where('id', 1)->increment($other, 1);
        DB::table('users')->where('id', 1)->increment($column, (int) $delta);
        DB::table('wallet_test_receipts')->insert(['user_id' => 1, 'amount' => (int) $delta]);
    });
    $ok = true;
} catch (QueryException $exception) {
    if (($exception->errorInfo[1] ?? null) !== 3819
        || ! str_contains($exception->getMessage(), 'users_'.$column.'_wallet_range')) {
        throw $exception;
    }
    $ok = false;
}
echo json_encode(['ok' => $ok], JSON_THROW_ON_ERROR);
