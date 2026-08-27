<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\PublicClanPageDTO;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Clan\Domain\Models\ClanMember;
use Carbon\CarbonImmutable;

class GetPublicClanPage
{
    /** @var array<int, string> */
    private const MODES = ['information', 'members', 'history'];

    public function execute(Clan $clan, ?string $requestedMode): PublicClanPageDTO
    {
        if ($requestedMode === 'news') {
            $requestedMode = 'information';
        }

        $mode = in_array($requestedMode, self::MODES, true) ? $requestedMode : 'information';

        $clan->loadMissing('owner.player');

        $onlineThreshold = CarbonImmutable::now()->subMinutes(10);

        $members = $clan->members()
            ->with(['user.player', 'role'])
            ->orderByDesc('role_id')
            ->orderBy('id')
            ->get()
            ->map(static fn (ClanMember $member): array => [
                'user' => $member->user,
                'role' => $member->role,
                'is_online' => $member->user->last_online_at?->greaterThan($onlineThreshold) ?? false,
            ]);

        $levelRank = 1 + Clan::query()
            ->where('lvl', '>', $clan->lvl)
            ->count();

        $experienceRank = 1 + Clan::query()
            ->where(function ($query) use ($clan): void {
                $query->where('experience', '>', $clan->experience)
                    ->orWhere(function ($query) use ($clan): void {
                        $query->where('experience', $clan->experience)
                            ->where('id', '<', $clan->id);
                    });
            })
            ->count();

        $logs = ClanLog::query()
            ->where('clan_id', $clan->id)
            ->with('user.player')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return new PublicClanPageDTO(
            clan: $clan,
            mode: $mode,
            membersCount: $members->count(),
            levelRank: $levelRank,
            experienceRank: $experienceRank,
            members: $members,
            logs: $logs,
        );
    }
}
