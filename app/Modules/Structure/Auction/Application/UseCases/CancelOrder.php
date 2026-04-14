<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelOrder
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function execute(User $user, int $orderId): array
    {
        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $orderId, &$result) {
            $order = AuctionOrder::where('id', $orderId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $order instanceof AuctionOrder) {
                $result = ['ok' => false, 'message' => 'Заявка не найдена.'];
                return;
            }

            $refund = $order->count * $order->price;
            $user->increment('money', $refund);
            $order->delete();

            $result = ['ok' => true, 'message' => sprintf('Заявка отменена. Возвращено %d монет', $refund)];
        });

        return $result;
    }
}