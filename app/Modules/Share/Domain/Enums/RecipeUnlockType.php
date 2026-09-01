<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum RecipeUnlockType: string
{
    case SINGLE_USE = 'single_use';
    case LEARNABLE = 'learnable';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_USE => 'Одноразовый рецепт',
            self::LEARNABLE => 'Изучаемый рецепт',
        };
    }
}
