<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Services;

class DepositTerms
{
    /** @var array<int, array{label: string, percent: float, min: int, max: int}> срок в днях => условия */
    private const TERMS = [
        14 => ['label' => 'На 2 недели', 'percent' => 0.04, 'min' => 10000, 'max' => 1000000],
        30 => ['label' => 'На 30 дней', 'percent' => 0.06, 'min' => 10000, 'max' => 5000000],
        60 => ['label' => 'На 60 дней', 'percent' => 0.08, 'min' => 10000, 'max' => 7500000],
        90 => ['label' => 'На 90 дней', 'percent' => 0.1, 'min' => 10000, 'max' => 10000000],
    ];

    /** @return array<int, array{label: string, percent: float, min: int, max: int}> */
    public function all(): array
    {
        return self::TERMS;
    }

    /** @return array{label: string, percent: float, min: int, max: int}|null */
    public function termFor(int $days): ?array
    {
        return self::TERMS[$days] ?? null;
    }

    public function labelFor(int $days): string
    {
        return self::TERMS[$days]['label'] ?? sprintf('На %d дней', $days);
    }
}