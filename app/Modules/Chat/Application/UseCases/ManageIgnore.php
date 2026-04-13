<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Enums\PlayerRelationshipType;
use App\Models\Player\PlayerRelationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ManageIgnore
{
    public function add(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (! $targetUser?->player_id) {
            return;
        }

        PlayerRelationship::firstOrCreate([
            'player_id' => $user->player_id,
            'target_id' => $targetUser->player_id,
            'type'      => PlayerRelationshipType::IGNORE,
        ]);
    }

    public function remove(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (! $targetUser?->player_id) {
            return;
        }

        PlayerRelationship::where('player_id', $user->player_id)
            ->where('target_id', $targetUser->player_id)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->delete();
    }

    public function list(User $user): Collection
    {
        return PlayerRelationship::where('player_id', $user->player_id)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->with('target.user')
            ->get();
    }
}