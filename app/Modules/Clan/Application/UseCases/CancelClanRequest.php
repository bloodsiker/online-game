<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class CancelClanRequest
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, ClanJoinRequest $joinRequest): void
    {
        $membership = $user->clanMembership;

        if ($membership === null || $membership->clan_id !== $joinRequest->clan_id) {
            throw new RuntimeException('Вы не можете отменить заявку');
        }

        $this->clanService->inviteCancel($user, $joinRequest);
    }
}
