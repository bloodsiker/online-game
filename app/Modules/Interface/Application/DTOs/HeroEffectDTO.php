<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

final readonly class HeroEffectDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public int $duration,
        public int $totalDuration,
        public bool $isCurse,
        public ?string $image,
        public ?string $description,
    ) {}

    /** @return array{id: string, name: string, duration: int, total_duration: int, is_curse: bool, image: ?string, description: ?string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'total_duration' => $this->totalDuration,
            'is_curse' => $this->isCurse,
            'image' => $this->image,
            'description' => $this->description,
        ];
    }
}
