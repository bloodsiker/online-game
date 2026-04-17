<?php

namespace App\Listeners;

use App\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Services\PlayerStatService;

class RecalculatePlayerModification
{
    public const DEFAULT_HP = 10;

    public const DEFAULT_MP = 10;

    public const HP_PER_STR = 3;

    public const MP_PER_MUD = 3;

    public const DODGE_PER_AGILITY = 1;

    public const CRITICAL_PER_INT = 1;

    public const ARMOR_PER_STR = 1;

    public function __construct(private readonly PlayerStatService $statService) {}

    public function handle(PlayerChangeStat $event): void
    {
        $player = $event->player;

        $player->hp_max = self::DEFAULT_HP + (self::HP_PER_STR * ($player->getStrength() - 1));
        $player->mp_max = self::DEFAULT_MP + (self::MP_PER_MUD * ($player->getMud() - 1));

        $player->save();

        // Set hp_now/mp_now to full bonus-aware maximum
        $sheet = $this->statService->resolve($player);
        $player->hp_now = $sheet->getHpMax();
        $player->mp_now = $sheet->getMpMax();
        $player->save();
    }
}
