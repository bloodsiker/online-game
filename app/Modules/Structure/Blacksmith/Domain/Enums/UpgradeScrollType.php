<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Enums;

enum UpgradeScrollType: string
{
    case BASE       = 'base';       // основной свиток заточки (обязателен)
    case PROTECTION = 'protection'; // предотвращает уничтожение предмета
    case STABILIZER = 'stabilizer'; // предотвращает понижение уровня при неудаче
    case LUCKY      = 'lucky';      // +15% к шансу успеха

    public function label(): string
    {
        return match ($this) {
            self::BASE       => 'Свиток заточки',
            self::PROTECTION => 'Свиток защиты',
            self::STABILIZER => 'Свиток стабилизации',
            self::LUCKY      => 'Амулет удачи',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BASE       => 'Необходим для заточки предмета',
            self::PROTECTION => 'Предотвращает уничтожение предмета при неудаче',
            self::STABILIZER => 'Предотвращает понижение уровня заточки при неудаче',
            self::LUCKY      => '+15% к шансу успешной заточки',
        };
    }
}