<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

final readonly class PlayerHeartbeatDTO
{
    /** @param list<HeroEffectDTO> $effects */
    public function __construct(
        public int $serverTime,
        public string $lastOnlineAt,
        public int $effectDamage,
        public array $hp,
        public array $mp,
        public array $effects,
        public bool $dead,
        public ?string $deathMessage,
    ) {}

    public function toArray(): array
    {
        return [
            'server_time' => $this->serverTime,
            'last_online_at' => $this->lastOnlineAt,
            'effect_damage' => $this->effectDamage,
            'hp' => $this->hp,
            'mp' => $this->mp,
            'dead' => $this->dead,
            'death_message' => $this->deathMessage,
            'effects' => array_map(static fn (HeroEffectDTO $effect): array => [
                'id' => $effect->id,
                'name' => $effect->name,
                'duration' => $effect->duration,
                'is_curse' => $effect->isCurse,
                'image' => $effect->image,
                'description' => $effect->description,
            ], $this->effects),
        ];
    }
}
