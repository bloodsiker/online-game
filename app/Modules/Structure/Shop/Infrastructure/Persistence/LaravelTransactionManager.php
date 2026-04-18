<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence;

use App\Modules\Structure\Shop\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
