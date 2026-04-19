<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final readonly class LocationFightDTO
{
    public function __construct(
        public Battle $battle,
        public ?BattleDetail $randomAttackedMonster,
        public Player $player,
        public StatSheet $playerDecorator,
    ) {}
}
