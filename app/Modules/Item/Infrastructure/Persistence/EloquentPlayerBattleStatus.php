<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence;

use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Item\Domain\Contracts\PlayerBattleStatus;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class EloquentPlayerBattleStatus implements PlayerBattleStatus
{
    public function __construct(private readonly BattleRepository $battles) {}

    public function isInBattle(Player $player): bool
    {
        $locationId = User::query()->whereKey($player->user_id)->value('location_id');

        return $locationId !== null && $this->battles->hasLivingPlayerInActiveBattle((int) $player->user_id, (int) $locationId);
    }
}
