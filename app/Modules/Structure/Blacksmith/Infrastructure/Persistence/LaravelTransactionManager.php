<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Infrastructure\Persistence;

use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
