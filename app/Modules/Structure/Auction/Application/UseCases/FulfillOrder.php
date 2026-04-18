<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\AuctionClaim;
use App\Modules\Structure\Auction\Domain\Models\AuctionHistory;
use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use App\Modules\Structure\Auction\Domain\Services\AuctionFeeCalculator;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class FulfillOrder
{
    public function __construct(
        private readonly AuctionFeeCalculator $feeCalculator,
    ) {}

    public function execute(User $user, Structure $auction, int $orderId, int $count): AuctionResultDTO
    {
        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $auction, $orderId, $count, &$result) {
            $order = AuctionOrder::where('id', $orderId)
                ->where('structure_id', $auction->id)
                ->lockForUpdate()
                ->first();

            if (! $order instanceof AuctionOrder) {
                $result = ['ok' => false, 'message' => 'Заявка не найдена или уже выполнена.'];

                return;
            }

            if ($order->user_id === $user->id) {
                $result = ['ok' => false, 'message' => 'Нельзя выполнить собственную заявку.'];

                return;
            }

            $slotItem = Backpack::select('backpacks.*')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->where('backpacks.user_id', $user->id)
                ->where('items.share_item_id', $order->share_item_id)
                ->where('backpacks.equipped', 0)
                ->lockForUpdate()
                ->with('item')
                ->first();

            if (! $slotItem instanceof Backpack) {
                $result = ['ok' => false, 'message' => 'У вас нет этого предмета в сумке.'];

                return;
            }

            $sellCount = min($count, $order->count, $slotItem->count);
            $totalPayment = $sellCount * $order->price;
            $fee = $this->feeCalculator->calculate($totalPayment);

            $freshSeller = User::lockForUpdate()->find($user->id);
            if ($freshSeller->money < $fee) {
                $result = ['ok' => false, 'message' => sprintf('Недостаточно монет для оплаты налога (%d монет).', $fee)];

                return;
            }

            if ($slotItem->count <= $sellCount) {
                Backpack::where('user_id', $user->id)->where('item_id', $slotItem->item_id)->delete();
            } else {
                $slotItem->decrement('count', $sellCount);
            }

            $freshSeller->increment('money', $totalPayment - $fee);

            $claimItemId = ($slotItem->count <= $sellCount)
                ? $slotItem->item_id
                : Item::create(['share_item_id' => $order->share_item_id])->id;

            AuctionClaim::create([
                'user_id' => $order->user_id,
                'structure_id' => $auction->id,
                'item_id' => $claimItemId,
                'count' => $sellCount,
            ]);

            $order->count -= $sellCount;
            $order->count <= 0 ? $order->delete() : $order->save();

            AuctionHistory::create([
                'buy_user_id' => $order->user_id,
                'sell_user_id' => $user->id,
                'structure_id' => $auction->id,
                'item_id' => $slotItem->item_id,
                'count' => $sellCount,
                'price' => $totalPayment,
            ]);

            $result = ['ok' => true, 'message' => sprintf(
                'Продано «%s» %d шт. Получено %d монет (налог %d)',
                $order->shareItem->name, $sellCount, $totalPayment - $fee, $fee,
            )];
        });

        return new AuctionResultDTO($result['ok'], $result['message']);
    }
}
