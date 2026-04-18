<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Reputation\Application\DTOs\ReputationActionResultDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class BuyReputationShopItem
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
        private readonly BackpackService $backpackService,
    ) {}

    public function execute(User $user, int $id, int $itemId): ReputationActionResultDTO
    {
        $reputation = $this->readRepository->findReputationForShopOrFail($id);

        if (! $this->isAtNpcLocation($user, $reputation)) {
            return new ReputationActionResultDTO(false, 'Магазин доступен только находясь рядом с НПС.');
        }

        $shopItem = $this->readRepository->findShopItemOrFail($reputation->id, $itemId);
        $pr = $this->reputationService->getOrCreate($user->player, $reputation);

        if ($pr->points < $shopItem->min_points) {
            return new ReputationActionResultDTO(false, 'Недостаточно очков репутации.');
        }
        if ($shopItem->price > 0 && $user->money < $shopItem->price) {
            return new ReputationActionResultDTO(false, 'Недостаточно монет.');
        }
        if ($shopItem->diamond > 0 && $user->diamond < $shopItem->diamond) {
            return new ReputationActionResultDTO(false, 'Недостаточно кристаллов.');
        }

        foreach ($shopItem->requirements as $req) {
            if (! $this->backpackService->hasItemByShareItem($user, $req->item, $req->quantity)) {
                return new ReputationActionResultDTO(false, "Нет нужного предмета: {$req->item->name}.");
            }
        }

        DB::transaction(function () use ($user, $shopItem) {
            if ($shopItem->price > 0) {
                $user->decrement('money', $shopItem->price);
            }
            if ($shopItem->diamond > 0) {
                $user->decrement('diamond', $shopItem->diamond);
            }

            foreach ($shopItem->requirements as $req) {
                $this->backpackService->removeItemByShareItem($user, $req->item, $req->quantity);
            }

            $this->backpackService->addItemByShareItem($user, $shopItem->item, 1);
        });

        return new ReputationActionResultDTO(true, "Товар «{$shopItem->item->name}» куплен!", 'success');
    }

    private function isAtNpcLocation(User $user, Reputation $reputation): bool
    {
        if (! $reputation->npc || ! $reputation->npc->location_id) {
            return true;
        }

        return (int) $user->currentLocation?->id === (int) $reputation->npc->location_id;
    }
}
