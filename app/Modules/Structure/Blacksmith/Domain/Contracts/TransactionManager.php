<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Contracts;

interface TransactionManager
{
    public function run(callable $callback): mixed;
}
