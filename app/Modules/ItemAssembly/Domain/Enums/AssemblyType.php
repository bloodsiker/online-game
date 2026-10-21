<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Domain\Enums;

enum AssemblyType: string
{
    case ARTIFACT = 'artifact';
    case EQUIPMENT = 'equipment';
    case CONSUMABLE = 'consumable';
    case MISC = 'misc';

    public function label(): string
    {
        return match ($this) {
            self::ARTIFACT => __('Артефакты'),
            self::EQUIPMENT => __('Снаряжение'),
            self::CONSUMABLE => __('Расходные предметы'),
            self::MISC => __('Прочее'),
        };
    }
}
