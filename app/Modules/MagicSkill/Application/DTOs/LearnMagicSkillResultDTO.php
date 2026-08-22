<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\DTOs;

class LearnMagicSkillResultDTO
{
    public function __construct(
        public readonly bool $ok,
        public readonly string $message,
        public readonly int $httpCode = 200,
    ) {}

    public function toArray(): array
    {
        return ['ok' => $this->ok, 'message' => $this->message];
    }
}
