<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Friend\Domain\Enums\PlayerRelationshipType;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ManageIgnore
{
    public function __construct(private readonly FriendRelationshipRepository $friendRelationshipRepository) {}

    public function add(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (! $targetUser?->player_id) {
            return;
        }

        $this->friendRelationshipRepository->firstOrCreateRelationship(
            (int) $user->player_id,
            (int) $targetUser->player_id,
            PlayerRelationshipType::IGNORE,
        );
    }

    public function remove(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (! $targetUser?->player_id) {
            return;
        }

        $relationship = $this->friendRelationshipRepository->findRelationship(
            (int) $user->player_id,
            (int) $targetUser->player_id,
            PlayerRelationshipType::IGNORE,
        );

        if ($relationship !== null) {
            $this->friendRelationshipRepository->delete($relationship);
        }
    }

    public function list(User $user): Collection
    {
        return $this->friendRelationshipRepository->getIgnores((int) $user->player_id);
    }
}
