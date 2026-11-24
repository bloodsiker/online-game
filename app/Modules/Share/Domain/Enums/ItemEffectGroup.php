<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

/**
 * Группа эффектов предмета. В бою использование любого предмета группы
 * ставит откат на все остальные предметы этой же группы.
 */
enum ItemEffectGroup: string
{
    case RECOVERY_POTION = 'recovery_potion';
    case RECOVERY_FOOD = 'recovery_food';

    public function label(): string
    {
        return match ($this) {
            self::RECOVERY_POTION => __('Восстановление: зелья'),
            self::RECOVERY_FOOD => __('Восстановление: еда'),
        };
    }

    /** Цвет названия группы в описании предмета. */
    public function color(): string
    {
        return match ($this) {
            self::RECOVERY_POTION => '#1F6FC5',
            self::RECOVERY_FOOD => '#2E8B1F',
        };
    }
}
