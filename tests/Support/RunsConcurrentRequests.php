<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

trait RunsConcurrentRequests
{
    /** Two independent PHP requests, forced to overlap while waiting on the same locked users. */
    private function runConcurrentRequests(string $worker, string $database, string $databaseVariable, array $first, ?array $second = null, ?callable $holdLocks = null): array
    {
        $barrier = sys_get_temp_dir().'/request-race-'.bin2hex(random_bytes(8));
        mkdir($barrier, 0700);
        $processes = [];
        DB::beginTransaction();
        try {
            if ($holdLocks !== null) {
                $holdLocks();
            } else {
                DB::table('users')->orderBy('id')->lockForUpdate()->get();
            }
            foreach ([$first, $second ?? $first] as $index => $arguments) {
                $process = new Process([
                    PHP_BINARY, base_path($worker),
                    ...array_map('strval', $arguments), $barrier, (string) $index,
                ], base_path(), [
                    'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => $database,
                    $databaseVariable => $database,
                    'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'BROADCAST_CONNECTION' => 'null',
                ]);
                $process->setTimeout(15);
                $process->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 10;
            while (! is_file($barrier.'/ready-0') || ! is_file($barrier.'/ready-1')) {
                if (microtime(true) >= $deadline) {
                    $this->fail('Workers failed to reach the barrier.');
                }
                foreach ($processes as $process) {
                    if (! $process->isRunning()) {
                        $this->fail($process->getErrorOutput().$process->getOutput());
                    }
                }
                usleep(10000);
            }
            file_put_contents($barrier.'/start', 'start');
            usleep(200000);
            // Neither request may complete while the parent holds their user locks.
            foreach ($processes as $process) {
                $this->assertTrue($process->isRunning(), 'A request bypassed the held locks. '.$process->getErrorOutput());
            }
            DB::commit();
            $results = [];
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
                $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach (glob($barrier.'/*') as $file) {
                unlink($file);
            }
            rmdir($barrier);
        }
    }
}
