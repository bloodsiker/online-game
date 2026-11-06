<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Enums;

enum UpgradeScrollType: string
{
    case BASE = 'base';       // основной свиток заточки оружия (обязателен)
    case BASE_ARMOR = 'base_armor'; // основной свиток заточки брони и щитов (обязателен)
    case PROTECTION = 'protection'; // предотвращает уничтожение предмета
    case STABILIZER = 'stabilizer'; // предотвращает понижение уровня при неудаче
    case LUCKY = 'lucky';      // +15% к шансу успеха

    /** @return list<self> */
    public static function baseTypes(): array
    {
        return [self::BASE, self::BASE_ARMOR];
    }

    public function isBase(): bool
    {
        return in_array($this, self::baseTypes(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::BASE => 'Свиток заточки оружия',
            self::BASE_ARMOR => 'Свиток заточки брони',
            self::PROTECTION => 'Свиток защиты',
            self::STABILIZER => 'Свиток стабилизации',
            self::LUCKY => 'Амулет удачи',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BASE => 'Необходим для заточки оружия',
            self::BASE_ARMOR => 'Необходим для заточки брони и щитов',
            self::PROTECTION => 'Предотвращает уничтожение предмета при неудаче',
            self::STABILIZER => 'Предотвращает понижение уровня заточки при неудаче',
            self::LUCKY => '+15% к шансу успешной заточки',
        };
    }
}
