<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\DTOs;

/** Связи игрока для раскраски других игроков на карте (maps/partials/live-players). */
final readonly class MapRelationsDTO
{
    /**
     * @param  list<int>  $friendUserIds
     * @param  list<int>  $enemyUserIds
     */
    public function __construct(
        public ?int $clanId,
        public array $friendUserIds,
        public array $enemyUserIds,
    ) {}

    public static function empty(): self
    {
        return new self(clanId: null, friendUserIds: [], enemyUserIds: []);
    }

    /** @return array{clan_id: ?int, friends: list<int>, enemies: list<int>} */
    public function toArray(): array
    {
        return ['clan_id' => $this->clanId, 'friends' => $this->friendUserIds, 'enemies' => $this->enemyUserIds];
    }
}
