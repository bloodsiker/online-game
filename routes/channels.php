<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('player.{playerId}', function ($user, $playerId) {
    return (int) $user->player_id === (int) $playerId;
});
