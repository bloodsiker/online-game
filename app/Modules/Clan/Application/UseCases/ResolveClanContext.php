<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanContextDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class ResolveClanContext
{
    public function optional(User $user): ClanContextDTO
    {
        $membership = $user->clanMembership;

        return new ClanContextDTO(
            user: $user,
            membership: $membership,
            clan: $membership?->clan,
        );
    }

    public function require(User $user): ClanContextDTO
    {
        $context = $this->optional($user);

        if ($context->membership === null || $context->clan === null) {
            throw new RuntimeException('Вы не состоите в клане.');
        }

        return $context;
    }

    /** Must be called inside the treasury transaction: clan → user → membership → role. */
    public function lockForTreasury(User $user, int $clanId): ClanContextDTO
    {
        $clan = Clan::without('owner')->whereKey($clanId)->lockForUpdate()->firstOrFail();
        $lockedUser = User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
        $membership = ClanMember::query()->where('user_id', $lockedUser->id)->where('clan_id', $clanId)
            ->lockForUpdate()->first();
        if ($membership === null) {
            throw new RuntimeException('Вы не состоите в клане.');
        }
        $role = ClanRole::query()->whereKey($membership->role_id)->where('clan_id', $clanId)->lockForUpdate()->first();
        if ($role === null) {
            throw new RuntimeException('Звание в клане недоступно.');
        }
        $membership->setRelation('role', $role);
        $membership->setRelation('clan', $clan);

        return new ClanContextDTO(user: $lockedUser, membership: $membership, clan: $clan);
    }

    public function requirePermission(User $user, ClanPermission $permission, string $message): ClanContextDTO
    {
        $context = $this->require($user);

        if (! $context->membership->role->hasPermission($permission)) {
            throw new RuntimeException($message);
        }

        return $context;
    }
}
