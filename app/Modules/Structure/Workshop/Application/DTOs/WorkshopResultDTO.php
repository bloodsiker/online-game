<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Application\DTOs;

final readonly class WorkshopResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
        public int $httpCode = 200,
    ) {}

    public function toArray(): array
    {
        return ['ok' => $this->ok, 'message' => $this->message];
    }
}
