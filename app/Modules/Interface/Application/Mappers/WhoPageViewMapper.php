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
            fn ($user) => $this->mapUser($user, $threshold)
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

    private function mapUser(mixed $user, Carbon $threshold): WhoUserDTO
    {
        $clan = $user->clanMembership?->clan;

        return new WhoUserDTO(
            id: $user->id,
            name: $user->name,
            lvl: (int) ($user->player?->lvl ?? 0),
            time: $user->last_online_at?->format('H:i') ?? '--:--',
            isOnline: $user->last_online_at?->gt($threshold) ?? false,
            clanName: $clan?->name,
            clanIcon: $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
        );
    }
}
