<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

readonly class ReputationExchangeService
{
    public function __construct(
        private ReputationService $reputationService,
        private ChatService $chatService,
    ) {}

    public function performExchange(User $user, int $structureId, int $shareItemId, int $count): void
    {
        if ($count <= 0) {
            throw new DomainException('Неверное количество для обмена.');
        }

        $exchange = ReputationExchange::with('reputation', 'shareItem')
            ->where('structure_id', $structureId)
            ->where('share_item_id', $shareItemId)
            ->first();

        if (! $exchange) {
            throw new DomainException('Этот реликт здесь не принимают.');
        }

        $backpackItem = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('items.share_item_id', $shareItemId)
            ->first();

        if (! $backpackItem || $backpackItem->count < $count) {
            throw new DomainException('У вас недостаточно этого реликта.');
        }

        $player = $user->player;
        $currentPoints = $this->reputationService->getOrCreate($player, $exchange->reputation)->points;

        if (! $exchange->isAcceptedAt($currentPoints)) {
            throw new DomainException('Этот предмет сейчас не принимают — попробуйте другой, подходящий вашему уровню репутации.');
        }

        $earnedPoints = $exchange->points * $count;

        DB::transaction(function () use ($backpackItem, $count, $player, $exchange, $earnedPoints): void {
            if ($backpackItem->count <= $count) {
                Item::whereKey($backpackItem->item_id)->delete();
                $backpackItem->delete();
            } else {
                $backpackItem->count -= $count;
                $backpackItem->save();
            }

            $this->reputationService->addPoints($player, $exchange->reputation, $earnedPoints, touchCooldown: false);
        });

        $this->chatService->sendQuestToUser($user, sprintf(
            'Вы сдали %d×«%s» и получили <b>+%d</b> репутации «%s».',
            $count,
            $exchange->shareItem->name,
            $earnedPoints,
            $exchange->reputation->name,
        ));
    }
}
