<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Reputation\Application\DTOs\ReputationActionResultDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Application\Services\ReputationShopCartService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseReputationCart
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
        private readonly ReputationShopCartService $cartService,
        private readonly BackpackService $backpackService,
    ) {}

    public function execute(User $user, int $reputationId): ReputationActionResultDTO
    {
        $reputation = $this->readRepository->findReputationForShopOrFail($reputationId);

        if (! $this->isAtNpcLocation($user, $reputation)) {
            return new ReputationActionResultDTO(false, 'Магазин доступен только находясь рядом с НПС.');
        }

        $cart = $this->cartService->getCart($user, $reputation->id);
        if ($cart->getItems()->isEmpty()) {
            return new ReputationActionResultDTO(false, 'Корзина пуста.');
        }

        $pr = $this->reputationService->getOrCreate($user->player, $reputation);

        // Проверки по всей корзине
        if ($cart->getTotalPrice() > 0 && $user->money < $cart->getTotalPrice()) {
            return new ReputationActionResultDTO(false, 'Недостаточно монет.');
        }
        if ($cart->getTotalDiamond() > 0 && $user->diamond < $cart->getTotalDiamond()) {
            return new ReputationActionResultDTO(false, 'Недостаточно кристаллов.');
        }

        foreach ($cart->getItems() as $cartItem) {
            $shopItem = $cartItem->shopItem;

            if ($pr->points < $shopItem->min_points) {
                return new ReputationActionResultDTO(false, "Недостаточно очков репутации для «{$shopItem->item->name}».");
            }

            foreach ($shopItem->requirements as $req) {
                $need = $req->quantity * $cartItem->quantity;
                if (! $this->backpackService->hasItemByShareItem($user, $req->item, $need)) {
                    return new ReputationActionResultDTO(false, "Нет нужного предмета: {$req->item->name} ×{$need}.");
                }
            }
        }

        DB::transaction(function () use ($user, $cart, $reputation) {
            if ($cart->getTotalPrice() > 0) {
                $user->decrement('money', $cart->getTotalPrice());
            }
            if ($cart->getTotalDiamond() > 0) {
                $user->decrement('diamond', $cart->getTotalDiamond());
            }

            foreach ($cart->getItems() as $cartItem) {
                $shopItem = $cartItem->shopItem;

                foreach ($shopItem->requirements as $req) {
                    $this->backpackService->removeItemByShareItem($user, $req->item, $req->quantity * $cartItem->quantity);
                }

                for ($i = 0; $i < $cartItem->quantity; $i++) {
                    $this->backpackService->addItemByShareItem($user, $shopItem->item, 1);
                }
            }

            $this->cartService->clearCart($user, $reputation->id);
        });

        return new ReputationActionResultDTO(true, 'Товары куплены!', 'success');
    }

    private function isAtNpcLocation(User $user, Reputation $reputation): bool
    {
        if (! $reputation->npc || ! $reputation->npc->location_id) {
            return true;
        }

        return (int) $user->currentLocation?->id === (int) $reputation->npc->location_id;
    }
}
