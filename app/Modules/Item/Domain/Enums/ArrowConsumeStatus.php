<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Enums;

/** Результат попытки взять стрелу из колчана под выстрел из лука. */
enum ArrowConsumeStatus: string
{
    case CONSUMED = 'consumed';
    case NO_QUIVER = 'no_quiver';
    case EMPTY = 'empty';
}
