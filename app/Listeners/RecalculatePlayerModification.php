<?php

namespace App\Listeners;

use App\Events\PlayerChangeStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RecalculatePlayerModification
{
    public const DEFAULT_HP = 10;
    public const DEFAULT_MP = 0;
    public const HP_PER_STR = 3;
    public const MP_PER_MUD = 3;
    public const DODGE_PER_AGILITY = 1;
    public const CRITICAL_PER_INT = 1;

    /**
     * Handle the event.
     */
    public function handle(PlayerChangeStat $event): void
    {
        $player = $event->player;

        $player->hp_max = self::DEFAULT_HP + (self::HP_PER_STR * ($player->getStrength() - 1));
        $player->mp_max = self::DEFAULT_MP + (self::MP_PER_MUD * ($player->getMud() - 1));
        $player->critical = self::CRITICAL_PER_INT * ($player->getInt() - 1);
        $player->dodge = self::DODGE_PER_AGILITY * ($player->getAgility() - 1);

        $player->save();
    }
}
