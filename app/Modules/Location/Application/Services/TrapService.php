<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\Services;

use App\Modules\Location\Domain\Traps\CrossingTrap;
use App\Modules\Location\Domain\Traps\TrapOutcome;
use App\Modules\Location\Domain\Traps\TrapType;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationTrap;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class TrapService
{
    /** Ключ `location_traps.type` => класс обработчика. Новый тип ловушки регистрируется здесь. */
    private const TYPES = [
        'crossing' => CrossingTrap::class,
    ];

    /** @return array<string, TrapType> ключ типа => обработчик (для админки) */
    public function types(): array
    {
        return array_map(static fn (string $class): TrapType => app($class), self::TYPES);
    }

    /**
     * Проверяет ловушки локации при выходе в направление ($direction — north|south|east|west).
     */
    public function onLeave(User $user, int $locationId, string $direction): TrapOutcome
    {
        $traps = LocationTrap::query()
            ->where('location_id', $locationId)
            ->where('is_active', true)
            ->where(static fn ($query) => $query->whereNull('direction')->orWhere('direction', $direction))
            ->get();

        $messages = [];
        foreach ($traps as $trap) {
            $class = self::TYPES[$trap->type] ?? null;
            if ($class === null) {
                continue;
            }

            /** @var TrapType $handler */
            $handler = app($class);
            $outcome = $handler->resolve($user, $trap);
            $messages = [...$messages, ...$outcome->messages];

            if ($outcome->died) {
                return new TrapOutcome($messages, died: true);
            }
        }

        return new TrapOutcome($messages);
    }
}
