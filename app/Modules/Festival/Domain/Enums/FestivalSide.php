<?php

declare(strict_types=1);

namespace App\Modules\Festival\Domain\Enums;

enum FestivalSide: string
{
    case Light = 'light';
    case Dark = 'dark';

    public function label(): string
    {
        return __('festival.side_'.$this->value);
    }
}
