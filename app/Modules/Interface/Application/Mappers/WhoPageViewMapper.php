<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Mappers;

use App\Modules\Interface\Application\DTOs\WhoPageDTO;
use App\Modules\Interface\Application\DTOs\WhoUserDTO;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WhoPageViewMapper
{
    public function map(Collection $locationUsers, Collection $onlineUsers, Carbon $threshold): WhoPageDTO
    {
        $mappedLocationUsers = $locationUsers->map(
            fn ($user) => $this->mapUser($user, $threshold)
        )->all();

        $mappedOnlineUsers = $onlineUsers->map(
            fn ($user) => $this->mapUser($user, $threshold, includeStatuses: true)
        )->all();

        $countOnlineLocation = collect($mappedLocationUsers)->where('isOnline', true)->count();

        return new WhoPageDTO(
            onlineOnLocation: $mappedLocationUsers,
            onlineInGame: $mappedOnlineUsers,
            countOnlineLocation: $countOnlineLocation,
            countOnlineInGame: count($mappedOnlineUsers),
            tenMinutesAgo: $threshold,
        );
    }

    private function mapUser(mixed $user, Carbon $threshold, bool $includeStatuses = false): WhoUserDTO
    {
        $clan = $user->clanMembership?->clan;
        $mute = $user->activeChatMute;
        $detention = $includeStatuses ? $user->activeDetention : null;

        return new WhoUserDTO(
            id: $user->id,
            name: $user->name,
            lvl: (int) ($user->player?->lvl ?? 0),
            time: $user->last_online_at?->format('H:i') ?? '--:--',
            isOnline: $user->last_online_at?->gt($threshold) ?? false,
            clanName: $clan?->name,
            clanIcon: $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
            clanId: $clan?->id,
            chatMuteTitle: $mute?->isActive() ? $mute->tooltip() : null,
            chatMuteExpiresAt: $mute?->expires_at?->toIso8601String(),
            isDetained: $detention?->isActive() === true,
            detentionReleaseAt: $detention?->release_at?->toIso8601String(),
            injuryExpiresAt: $includeStatuses ? $user->player?->latestActiveInjury?->expires_at?->toIso8601String() : null,
        );
    }
}
