<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\Services;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class MagicCastTransaction
{
    public function run(User $actor, ?int $targetPlayerId, callable $cast): MagicSkillActionResultDTO
    {
        // Discovery happens before the transaction: no stale RR snapshot while waiting for users.
        $targetOwnerId = $targetPlayerId ? Player::whereKey($targetPlayerId)->value('user_id') : null;
        if ($targetPlayerId && $targetOwnerId === null) {
            return $this->missingTarget();
        }
        $userIds = collect([$actor->id, $targetOwnerId])->filter()->unique()->sort();

        return DB::transaction(function () use ($actor, $targetPlayerId, $targetOwnerId, $userIds, $cast): MagicSkillActionResultDTO {
            $users = $userIds->mapWithKeys(fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->first()]);
            $user = $users->get($actor->id);
            $targetUser = $users->get($targetOwnerId ?? $actor->id);
            if ($user?->player_id === null || $targetUser === null
                || ($targetPlayerId && (int) $targetUser->player_id !== $targetPlayerId)) {
                return $this->missingTarget();
            }
            $players = collect([$user->player_id, $targetUser->player_id])->unique()->sort()->mapWithKeys(
                fn ($id): array => [$id => Player::whereKey($id)->lockForUpdate()->first()],
            );
            $caster = $players->get($user->player_id);
            $target = $players->get($targetUser->player_id);
            if ($caster === null || $target === null || (int) $caster->user_id !== (int) $user->id || (int) $target->user_id !== (int) $targetUser->id) {
                return $this->missingTarget();
            }
            if ($target->id !== $caster->id && ((int) $targetUser->location_id !== (int) $user->location_id
                || $targetUser->last_online_at === null
                || $targetUser->last_online_at->lt(now()->subMinutes((int) config('game.online_timeout_minutes', 30))))) {
                return $this->missingTarget();
            }
            $caster->setRelation('user', $user);
            $target->setRelation('user', $targetUser);
            $user->setRelation('player', $caster);
            $targetUser->setRelation('player', $target);

            return $cast($caster, $target);
        }, attempts: 3);
    }

    private function missingTarget(): MagicSkillActionResultDTO
    {
        return new MagicSkillActionResultDTO('error', 'Цель не найдена', httpCode: 404);
    }
}
