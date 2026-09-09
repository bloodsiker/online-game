<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Modules\Player\Domain\Services\PlayerInjuryService;

final readonly class PruneExpiredPlayerInjuries
{
    public function __construct(private PlayerInjuryService $injuryService) {}

    public function execute(): int
    {
        return $this->injuryService->pruneExpired();
    }
}
