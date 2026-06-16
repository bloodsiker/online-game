<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanInformationPageDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanInformationPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanInformationPageDTO
    {
        $context = $this->resolveClanContext->require($user);

        return new ClanInformationPageDTO(
            clan: $context->clan,
            membership: $context->membership,
            canChangeNews: $context->membership->role->hasPermission(ClanPermission::CHANGE_NEWS),
        );
    }
}
