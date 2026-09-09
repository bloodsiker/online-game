<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

/**
 * «Возрождение» — эффект на 30 минут после гибели игрока. Пока он активен
 * и current_value (сумма опыта, потерянного при последней смерти) больше
 * нуля, предмет из премиум-магазина может вернуть этот опыт — см.
 * RestoreLostExpStrategy. Само наложение эффекта делает PlayerDeathFinalizer.
 */
final class PlayerRevivalService
{
    public const EFFECT_SLUG = 'revival';

    public const DURATION_SECONDS = 1800;

    public function findActiveRedeemable(Player $player): ?PlayerActiveEffect
    {
        return PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('current_value', '>', 0)
            ->whereHas('effect', fn ($query) => $query->where('slug', self::EFFECT_SLUG))
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()))
            ->first();
    }
}
