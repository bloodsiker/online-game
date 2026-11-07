<?php

declare(strict_types=1);

namespace App\Modules\Chat\Infrastructure\Persistence;

use App\Modules\Chat\Domain\Contracts\ChatParticipantReader;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EloquentChatParticipantReader implements ChatParticipantReader
{
    public function verified(int $userId): ?User
    {
        return User::query()->withOnly([
            'player' => static fn (BelongsTo $relation): BelongsTo => $relation->withOnly([])->select(['id', 'lvl']),
            'currentLocation' => static fn (BelongsTo $relation): BelongsTo => $relation->select(['id', 'map_id']),
            'clanMembership.clan' => static fn (BelongsTo $relation): BelongsTo => $relation->withOnly([]),
        ])->whereKey($userId)->whereNotNull('email_verified_at')->first();
    }

    public function byNickname(string $nickname): ?User
    {
        return User::query()->without('player')->where('name', $nickname)->first();
    }
}
