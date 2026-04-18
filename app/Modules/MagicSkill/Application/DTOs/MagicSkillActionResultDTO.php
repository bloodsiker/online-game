<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\DTOs;

class MagicSkillActionResultDTO
{
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly ?array $hp = null,
        public readonly ?array $mp = null,
        public readonly ?int $cooldownUntil = null,
        public readonly array $blessings = [],
        public readonly int $httpCode = 200,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'message' => $this->message,
            'hp' => $this->hp,
            'mp' => $this->mp,
            'cooldown_until' => $this->cooldownUntil,
            'blessings' => $this->blessings,
        ], static fn ($value) => $value !== null);
    }
}
