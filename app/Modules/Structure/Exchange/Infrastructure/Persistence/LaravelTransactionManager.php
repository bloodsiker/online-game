<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Infrastructure\Persistence;

use App\Modules\Structure\Exchange\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
