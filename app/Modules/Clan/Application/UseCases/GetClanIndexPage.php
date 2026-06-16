<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanIndexPageDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanIndexPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanIndexPageDTO
    {
        $context = $this->resolveClanContext->optional($user);

        if ($context->clan === null) {
            return new ClanIndexPageDTO(false, collect(), false);
        }

        return new ClanIndexPageDTO(
            inClan: true,
            activeQuests: $context->clan->activeQuestProgress,
            isLeader: (int) $context->clan->owner_id === $user->id,
        );
    }
}
