<?php

declare(strict_types=1);

namespace App\Modules\Referral\Domain\Contracts;

interface TransactionManager
{
    public function run(callable $callback): mixed;
}
