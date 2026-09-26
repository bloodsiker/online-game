<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

/**
 * Сбрасывает только те очки характеристик, что игрок сам вложил через
 * AllocateStats — расовый прирост не трогает. Расовая часть не хранится
 * отдельно: она стабильно прибавляется на каждом level-up как
 * race.<stat> (см. RecalculatePlayerStats), поэтому всегда восстановима
 * формулой race.<stat> × (lvl − 1) без хранения истории аллокаций.
 */
class RespecStatsStrategy implements ItemEffectStrategyInterface
{
    /** Публичные: переиспользуются в ItemController при смене расы (та же формула базы). */
    public const STARTING_VALUE = 1.0;

    public const STATS = ['strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance'];

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $race = $player->race;
        $levelsGained = max(0, $player->lvl - 1);
        $refund = 0;

        foreach (self::STATS as $stat) {
            $raceGrowth = (float) ($race?->{$stat} ?? 0) * $levelsGained;
            $baseline = self::STARTING_VALUE + $raceGrowth;
            $allocated = max(0.0, (float) $player->{$stat} - $baseline);

            $refund += (int) round($allocated);
            $player->{$stat} = $baseline;
        }

        if ($refund === 0) {
            return;
        }

        $player->free_stats += $refund;
        $player->save();

        event(new PlayerChangeStat($player));
    }
}
