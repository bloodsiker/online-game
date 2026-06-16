<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence;

use App\Modules\Dungeon\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
