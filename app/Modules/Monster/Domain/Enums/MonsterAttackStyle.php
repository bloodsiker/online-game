<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Enums;

/** Стиль физической атаки монстра — определяет фразы в логе боя (см. BattlePhraseBook). */
enum MonsterAttackStyle: string
{
    case STRIKE = 'strike';
    case CLAWS = 'claws';
    case BITE = 'bite';
    case WEAPON = 'weapon';
    case CRUSH = 'crush';
    case STING = 'sting';
    case GHOST = 'ghost';
    case VINES = 'vines';
    case SLIME = 'slime';
    case FIRE = 'fire';

    public function label(): string
    {
        return match ($this) {
            self::STRIKE => 'Обычный удар',
            self::CLAWS => 'Когти',
            self::BITE => 'Укус',
            self::WEAPON => 'Оружие',
            self::CRUSH => 'Сокрушение',
            self::STING => 'Жало / яд',
            self::GHOST => 'Призрачный',
            self::VINES => 'Лозы / корни',
            self::SLIME => 'Слизь / кислота',
            self::FIRE => 'Пламя / дыхание',
        };
    }
}
