<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;

/**
 * Eternum: value очков репутации reputation_id за каждую использованную штуку.
 * Итог (получено и сколько стало всего) пишется в чат.
 */
final class GrantReputationStrategy implements ItemEffectStrategyInterface
{
    public function __construct(
        private readonly ReputationService $reputationService,
        private readonly ChatService $chatService,
    ) {}

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $this->grant($player, $effect, quantity: 1, itemName: '');
    }

    /** @return int начислено очков */
    public function grant(Player $player, ItemEffectValue $effect, int $quantity, string $itemName): int
    {
        $reputation = $effect->reputationId !== null ? Reputation::query()->find($effect->reputationId) : null;

        if ($reputation === null || $effect->value <= 0 || $quantity < 1) {
            throw new ItemUseBlockedException(
                message: __('Предмет настроен неверно. Обратитесь к администрации.'),
                errorCode: 'invalid_grant_reputation',
            );
        }

        $points = $effect->value * $quantity;
        $total = (int) $this->reputationService->addPoints($player, $reputation, $points, touchCooldown: false)->points;

        $this->chatService->sendQuestToUser($player->user, new LocalizedChatMessage('chat.reputation_item_used', [
            'count' => $quantity,
            'item' => $itemName,
            'points' => $points,
            'reputation' => LocalizedChatMessage::contentName($reputation),
            'total' => $total,
        ]));

        return $points;
    }
}
