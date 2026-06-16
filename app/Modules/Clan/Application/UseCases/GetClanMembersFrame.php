<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanFramePageDTO;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;

class GetClanMembersFrame
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanFramePageDTO
    {
        $context = $this->resolveClanContext->optional($user);

        if ($context->membership === null || $context->clan === null) {
            return new ClanFramePageDTO(collect(), null, null);
        }

        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        $members = ClanMember::where('clan_id', $context->membership->clan_id)
            ->with(['user.player', 'role'])
            ->get()
            ->sortByDesc(fn ($member) => $member->user->last_online_at);

        return new ClanFramePageDTO($members, $context->clan, $tenMinutesAgo);
    }
}
