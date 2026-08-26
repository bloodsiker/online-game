<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final readonly class HeroPageDTO
{
    /**
     * @param  array<HeroEffectDTO>  $activeEffects
     */
    public function __construct(
        public Player $player,
        public StatSheet $playerDecorator,
        public array $activeEffects,
    ) {}
}
