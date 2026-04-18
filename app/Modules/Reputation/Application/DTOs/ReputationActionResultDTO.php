<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\DTOs;

final readonly class ReputationActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message = '',
        public string $messageType = 'error',
    ) {}
}
