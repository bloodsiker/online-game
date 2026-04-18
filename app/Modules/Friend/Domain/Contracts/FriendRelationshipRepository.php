<?php

declare(strict_types=1);

namespace App\Modules\Friend\Domain\Contracts;

use App\Enums\PlayerRelationshipType;
use App\Modules\Friend\Infrastructure\Persistence\Models\PlayerRelationship;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Collection;

interface FriendRelationshipRepository
{
    /**
     * @return Collection<int, PlayerRelationship>
     */
    public function getAcceptedFriends(int $playerId): Collection;

    /**
     * @return Collection<int, PlayerRelationship>
     */
    public function getPendingOutgoingFriends(int $playerId): Collection;

    /**
     * @return Collection<int, PlayerRelationship>
     */
    public function getPendingIncomingFriends(int $playerId): Collection;

    /**
     * @return Collection<int, PlayerRelationship>
     */
    public function getEnemies(int $playerId): Collection;

    /**
     * @return Collection<int, PlayerRelationship>
     */
    public function getIgnores(int $playerId): Collection;

    public function findTargetPlayerByName(string $name): ?Player;

    public function existsRelationship(int $playerId, int $targetId, PlayerRelationshipType $type): bool;

    public function createRelationship(int $playerId, int $targetId, PlayerRelationshipType $type, ?string $status = null): PlayerRelationship;

    public function firstOrCreateRelationship(int $playerId, int $targetId, PlayerRelationshipType $type, ?string $status = null): PlayerRelationship;

    public function findRelationshipById(int $id): ?PlayerRelationship;

    public function findRelationship(int $playerId, int $targetId, PlayerRelationshipType $type): ?PlayerRelationship;

    public function save(PlayerRelationship $relationship): void;

    public function delete(PlayerRelationship $relationship): void;

    /**
     * @return list<int>
     */
    public function getIgnoredUserIdsByPlayerId(int $playerId): array;

    public function isIgnoring(int $playerId, int $targetId): bool;
}
