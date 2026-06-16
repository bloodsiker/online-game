<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanMemberPageDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;

class GetClanMemberPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanMemberPageDTO
    {
        $context = $this->resolveClanContext->require($user);

        $clan = $context->membership->clan()
            ->with(['members.user.currentLocation', 'members.role', 'roles'])
            ->firstOrFail();

        $onlineThreshold = Carbon::now()->subMinutes(10);

        $memberRows = $clan->members->map(fn ($member) => [
            'type' => 'member',
            'user' => $member->user,
            'role' => $member->role,
            'membership' => $member,
            'is_online' => $member->user->last_online_at && $member->user->last_online_at > $onlineThreshold,
        ]);

        $requestRows = ClanJoinRequest::where('clan_id', $clan->id)
            ->with('user')
            ->get()
            ->map(fn ($request) => [
                'type' => 'request',
                'id' => $request->id,
                'user' => $request->user,
                'status' => $request->status,
                'is_online' => false,
            ]);

        $rows = $memberRows->concat($requestRows);

        return new ClanMemberPageDTO(
            clan: $clan,
            membership: $context->membership,
            rows: $rows,
            allRoles: $clan->roles,
            leaderRole: $clan->roles->firstWhere('is_leader', true),
            onlineCount: $memberRows->filter(fn ($row) => $row['is_online'])->count(),
            canKick: $context->membership->role->hasPermission(ClanPermission::KICK),
            canInvite: $context->membership->role->hasPermission(ClanPermission::INVITE),
        );
    }
}
