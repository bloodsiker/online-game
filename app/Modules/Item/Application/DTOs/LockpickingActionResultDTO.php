<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class LockpickingActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
        public int $httpCode = 200,
        public array $data = [],
    ) {}

    public function toArray(): array
    {
        return ['ok' => $this->ok, 'message' => $this->message, ...$this->data];
    }
}
