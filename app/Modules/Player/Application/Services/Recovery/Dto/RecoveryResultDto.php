<?php

namespace App\Modules\Player\Application\Services\Recovery\Dto;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class RecoveryResultDto
{
    public function __construct(
        public Player $player,
        public int $hpHealed,
        public int $mpHealed,
        public array $buffs
    ) {}
}
