<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

/**
 * Механизм: выдаёт value шт. target-предмета. Сам предмет не расходуется
 * (ItemEffectType::keepsItem), частоту ограничивает share_item_use_limits.
 */
final class ProduceItemStrategy implements ItemEffectStrategyInterface
{
    public function __construct(
        private readonly BackpackService $backpack,
    ) {}

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $target = $effect->targetShareItemId !== null ? ShareItem::query()->find($effect->targetShareItemId) : null;

        if ($target === null || $effect->value <= 0) {
            throw new ItemUseBlockedException(
                message: __('Предмет настроен неверно. Обратитесь к администрации.'),
                errorCode: 'invalid_produce_item',
            );
        }

        $this->backpack->giveItemsByShareItem($player->user, $target, $effect->value);
    }
}
