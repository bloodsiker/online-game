<?php

declare(strict_types=1);

namespace App\Modules\Battle\Domain\Contracts;

interface RandomizerInterface
{
    /** Случайное целое в диапазоне [min, max] включительно */
    public function between(int $min, int $max): int;

    /** Срабатывает ли событие с шансом $percent (0..100, дробные поддерживаются) */
    public function chance(float $percent): bool;
}
