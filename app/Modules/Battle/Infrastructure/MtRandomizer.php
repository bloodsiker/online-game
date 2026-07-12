<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure;

use App\Modules\Battle\Domain\Contracts\RandomizerInterface;

class MtRandomizer implements RandomizerInterface
{
    public function between(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public function chance(float $percent): bool
    {
        if ($percent <= 0) {
            return false;
        }

        if ($percent >= 100) {
            return true;
        }

        // 10000 исходов — честные дробные проценты (0.01% шаг)
        return mt_rand(1, 10000) <= (int) round($percent * 100);
    }
}
