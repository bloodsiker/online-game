<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Results;

final readonly class UpgradeTransferResult
{
    public function __construct(
        public bool $success,
        public int $level,
        public int $chance,
    ) {}
}
