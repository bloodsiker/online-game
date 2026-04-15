<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Models\Player\Player;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerStatService;

class GetCharacter
{
    public function __construct(
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(Player $player): StatSheet
    {
        return $this->statService->resolve($player);
    }
}