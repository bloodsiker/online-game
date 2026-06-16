<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanRolePageDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanRolePage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanRolePageDTO
    {
        $context = $this->resolveClanContext->require($user);
        $clan = $context->membership->clan()->with('roles')->firstOrFail();

        return new ClanRolePageDTO(
            clan: $clan,
            membership: $context->membership,
            roles: $clan->roles,
            permissions: ClanPermission::cases(),
            canChangePerms: $context->membership->role->hasPermission(ClanPermission::CHANGE_PERMS),
        );
    }
}
