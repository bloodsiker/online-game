<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

use Carbon\Carbon;

final readonly class WhoPageDTO
{
    /**
     * @param array<WhoUserDTO> $onlineOnLocation
     * @param array<WhoUserDTO> $onlineInGame
     */
    public function __construct(
        public array $onlineOnLocation,
        public array $onlineInGame,
        public int $countOnlineLocation,
        public int $countOnlineInGame,
        public Carbon $tenMinutesAgo,
    ) {}
}
