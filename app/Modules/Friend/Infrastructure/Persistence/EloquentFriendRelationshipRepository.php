<?php

declare(strict_types=1);

namespace App\Modules\Friend\Infrastructure\Persistence;

use App\Enums\PlayerRelationshipType;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Friend\Infrastructure\Persistence\Models\PlayerRelationship;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Collection;

class EloquentFriendRelationshipRepository implements FriendRelationshipRepository
{
    public function getAcceptedFriends(int $playerId): Collection
    {
        return $this->baseTargetQuery($playerId, PlayerRelationshipType::FRIEND)
            ->where('status', 'accepted')
            ->get();
    }

    public function getPendingOutgoingFriends(int $playerId): Collection
    {
        return $this->baseTargetQuery($playerId, PlayerRelationshipType::FRIEND)
            ->where('status', 'pending')
            ->get();
    }

    public function getPendingIncomingFriends(int $playerId): Collection
    {
        return PlayerRelationship::where('target_id', $playerId)
            ->where('type', PlayerRelationshipType::FRIEND)
            ->where('status', 'pending')
            ->with(['player.user.clanMembership.clan'])
            ->get();
    }

    public function getEnemies(int $playerId): Collection
    {
        return $this->baseTargetQuery($playerId, PlayerRelationshipType::ENEMY)->get();
    }

    public function getIgnores(int $playerId): Collection
    {
        return $this->baseTargetQuery($playerId, PlayerRelationshipType::IGNORE)->get();
    }

    public function findTargetPlayerByName(string $name): ?Player
    {
        return Player::whereHas('user', fn ($query) => $query->where('name', $name))->first();
    }

    public function existsRelationship(int $playerId, int $targetId, PlayerRelationshipType $type): bool
    {
        return PlayerRelationship::where('player_id', $playerId)
            ->where('target_id', $targetId)
            ->where('type', $type)
            ->exists();
    }

    public function createRelationship(int $playerId, int $targetId, PlayerRelationshipType $type, ?string $status = null): PlayerRelationship
    {
        return PlayerRelationship::create([
            'player_id' => $playerId,
            'target_id' => $targetId,
            'type' => $type,
            'status' => $status,
        ]);
    }

    public function firstOrCreateRelationship(int $playerId, int $targetId, PlayerRelationshipType $type, ?string $status = null): PlayerRelationship
    {
        return PlayerRelationship::firstOrCreate([
            'player_id' => $playerId,
            'target_id' => $targetId,
            'type' => $type,
        ], [
            'status' => $status,
        ]);
    }

    public function findRelationshipById(int $id): ?PlayerRelationship
    {
        return PlayerRelationship::find($id);
    }

    public function findRelationship(int $playerId, int $targetId, PlayerRelationshipType $type): ?PlayerRelationship
    {
        return PlayerRelationship::where('player_id', $playerId)
            ->where('target_id', $targetId)
            ->where('type', $type)
            ->first();
    }

    public function save(PlayerRelationship $relationship): void
    {
        $relationship->save();
    }

    public function delete(PlayerRelationship $relationship): void
    {
        $relationship->delete();
    }

    public function getIgnoredUserIdsByPlayerId(int $playerId): array
    {
        return PlayerRelationship::where('player_relationships.player_id', $playerId)
            ->where('player_relationships.type', PlayerRelationshipType::IGNORE)
            ->join('users', 'player_relationships.target_id', '=', 'users.player_id')
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function isIgnoring(int $playerId, int $targetId): bool
    {
        return PlayerRelationship::where('player_id', $playerId)
            ->where('target_id', $targetId)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->exists();
    }

    private function baseTargetQuery(int $playerId, PlayerRelationshipType $type)
    {
        return PlayerRelationship::where('player_id', $playerId)
            ->where('type', $type)
            ->with(['target.user.clanMembership.clan']);
    }
}
