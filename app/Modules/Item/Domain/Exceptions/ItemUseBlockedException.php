<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Exceptions;

use Carbon\CarbonInterface;
use DomainException;

class ItemUseBlockedException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly ?CarbonInterface $availableAt = null,
    ) {
        parent::__construct($message);
    }
}
