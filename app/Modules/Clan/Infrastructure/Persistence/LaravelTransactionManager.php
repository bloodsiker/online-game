<?php

declare(strict_types=1);

namespace App\Modules\Clan\Infrastructure\Persistence;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
