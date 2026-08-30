<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

/**
 * Семейство инструмента добычи. Инструмент (type=tool) подходит для ресурса
 * (type=resource), если их семейства совпадают — конкретный tier инструмента
 * при этом только ускоряет добычу (см. GatheringService), а не открывает
 * доступ к ресурсу.
 */
enum GatheringToolFamily: string
{
    case SICKLE = 'sickle';
    case ROD = 'rod';
    case PICKAXE = 'pickaxe';
    case AXE = 'axe';

    public function label(): string
    {
        return match ($this) {
            self::SICKLE => 'Серп',
            self::ROD => 'Удочка',
            self::PICKAXE => 'Кирка',
            self::AXE => 'Топор',
        };
    }
}
