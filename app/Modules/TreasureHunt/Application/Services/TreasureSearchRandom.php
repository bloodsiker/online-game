<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Services;

class TreasureSearchRandom
{
    public function succeeds(float $chance): bool
    {
        return random_int(1, 100000) <= (int) round($chance * 1000);
    }

    public function location(array $ids): int
    {
        return (int) $ids[random_int(0, count($ids) - 1)];
    }

    public function territory(array $ids): int
    {
        return (int) $ids[random_int(0, count($ids) - 1)];
    }
}
