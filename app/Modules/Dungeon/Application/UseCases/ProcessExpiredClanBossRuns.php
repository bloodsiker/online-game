<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\ClanBossService;
use Carbon\CarbonInterface;

final readonly class ProcessExpiredClanBossRuns
{
    public function __construct(private ClanBossService $clanBossService) {}

    public function execute(CarbonInterface $now): int
    {
        return $this->clanBossService->expireOverdue($now);
    }
}
