<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final class GrantDiamondsStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        if (! $effect->isFlat() || $effect->value <= 0 || $effect->durationSeconds !== null) {
            throw new ItemUseBlockedException(
                message: __('Неверно настроен номинал сертификата. Обратитесь к администрации.'),
                errorCode: 'invalid_certificate_value',
            );
        }

        // The item-use transaction locks the owner/player and consumes the certificate atomically.
        $player->user->increment('diamond', $effect->value);
    }
}
