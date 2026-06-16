<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanContextDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
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

    public function requirePermission(User $user, ClanPermission $permission, string $message): ClanContextDTO
    {
        $context = $this->require($user);

        if (! $context->membership->role->hasPermission($permission)) {
            throw new RuntimeException($message);
        }

        return $context;
    }
}
