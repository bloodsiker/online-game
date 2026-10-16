<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanDirectoryPageDTO;
use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class GetClanDirectoryPage
{
    public function execute(User $user): ClanDirectoryPageDTO
    {
        $clans = Clan::query()
            ->with(['owner.player'])
            ->withCount('members')
            ->orderByDesc('lvl')
            ->orderByDesc('experience')
            ->orderBy('name')
            ->paginate(30);

        $applications = ClanJoinRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [ClanJoinRequestStatus::REQUEST, ClanJoinRequestStatus::INVITE])
            ->get()
            ->keyBy('clan_id');

        return new ClanDirectoryPageDTO($clans, $applications);
    }
}
