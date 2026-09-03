<?php

use App\Modules\Party\Infrastructure\Persistence\Models\PartyMember;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Storage;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('player.{playerId}', function ($user, $playerId) {
    return (int) $user->player_id === (int) $playerId;
});

Broadcast::channel('online', function ($user) {
    $user->loadMissing(['player', 'clanMembership.clan']);
    $clan = $user->clanMembership?->clan;

    return [
        'id' => (int) $user->id,
        'name' => (string) $user->name,
        'lvl' => (int) ($user->player?->lvl ?? 0),
        'location_id' => (int) ($user->location_id ?? 0),
        'time' => now()->format('H:i'),
        'is_online' => true,
        'clan_id' => $clan?->id,
        'clan_name' => $clan?->name,
        'clan_icon' => $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
        'info_url' => url('/info/u/'.$user->id),
    ];
});

Broadcast::channel('chat.main', fn ($user) => $user !== null);
Broadcast::channel('chat.trade', fn ($user) => $user !== null);
Broadcast::channel('chat.system', fn ($user) => $user !== null);

Broadcast::channel('chat.location.{mapId}', function ($user, $mapId) {
    return $user->currentLocation()->where('map_id', (int) $mapId)->exists();
});

Broadcast::channel('chat.clan.{clanId}', function ($user, $clanId) {
    return $user->clanMembership()->where('clan_id', (int) $clanId)->exists();
});

Broadcast::channel('chat.party.{partyId}', function ($user, $partyId) {
    return PartyMember::query()
        ->where('party_id', (int) $partyId)
        ->where('user_id', (int) $user->id)
        ->exists();
});
