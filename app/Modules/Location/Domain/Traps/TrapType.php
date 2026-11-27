<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Traps;

use App\Modules\Location\Infrastructure\Persistence\Models\LocationTrap;
use App\Modules\User\Infrastructure\Persistence\Models\User;

/**
 * Тип ловушки на локации. Новый тип — новый класс + строка в TrapService::TYPES;
 * параметры конкретной ловушки лежат в location_traps.config.
 */
interface TrapType
{
    /** Название типа для админки. */
    public function label(): string;

    /**
     * Описание полей config для формы админки.
     * Каждое поле: key, label, kind (number|effect), default, hint (необязательно), step (для number).
     *
     * @return list<array<string, mixed>>
     */
    public function configFields(): array;

    public function resolve(User $user, LocationTrap $trap): TrapOutcome;
}
