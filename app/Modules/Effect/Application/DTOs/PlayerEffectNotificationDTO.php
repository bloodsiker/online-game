<?php

declare(strict_types=1);

namespace App\Modules\Effect\Application\DTOs;

use JsonSerializable;

final readonly class PlayerEffectNotificationDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $duration,
        public bool $isCurse,
    ) {}

    /** @return array{id: string, name: string, duration: int, is_curse: bool} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'is_curse' => $this->isCurse,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
