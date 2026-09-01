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
        public ?string $image = null,
        public ?string $description = null,
    ) {}

    /** @return array{id: string, name: string, duration: int, is_curse: bool, image: ?string, description: ?string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'is_curse' => $this->isCurse,
            'image' => $this->image,
            'description' => $this->description,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
