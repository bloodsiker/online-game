<?php

namespace App\Services\Recovery\Dto;

use App\Models\Player\Player;

class RecoveryResultDto {
    public function __construct(
        public Player $player,
        public int $hpHealed,
        public int $mpHealed,
        public array $buffs
    ) {}
}
