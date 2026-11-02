<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Application\Services;

use App\Modules\Commerce\Domain\Exceptions\PurchaseLimitExceeded;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

class ShopPurchaseLimitService
{
    public function availability(User $user, Model $offer): ?array
    {
        $limit = (int) $offer->purchase_limit_quantity;
        $seconds = (int) $offer->purchase_limit_period_seconds;
        if ($limit <= 0 || $seconds <= 0) {
            return null;
        }
        $state = DB::table('shop_purchase_limit_states')->where($this->key($user, $offer))->first();
        $until = $state ? Carbon::parse($state->period_started_at)->addSeconds($seconds) : null;
        $remaining = $until?->isFuture() ? max(0, $limit - $state->quantity) : $limit;

        return [
            'limit' => $limit,
            'remaining' => $remaining,
            'available_at' => $until?->isFuture() ? $until : null,
            'label' => __('commerce.purchase_limit', [
                'quantity' => $limit,
                'period' => CarbonInterval::seconds($seconds)->cascade()->locale(app()->getLocale())->forHumans(),
                'remaining' => $remaining,
            ]),
        ];
    }

    /** Reserve only inside the transaction that pays for and delivers the purchase. */
    public function reserve(User $user, Model $offer, int $quantity): void
    {
        if ((int) $offer->purchase_limit_quantity <= 0 || (int) $offer->purchase_limit_period_seconds <= 0) {
            return;
        }
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Purchase limits require a purchase transaction.');
        }
        // A stable lock also serializes the first purchase, before a state row exists.
        User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
        $available = $this->availability($user, $offer);
        if ($quantity < 1 || $quantity > $available['remaining']) {
            $message = __('commerce.purchase_blocked', ['item' => $offer->item->name]).' '.$available['label'];
            if ($available['available_at'] !== null) {
                $message .= ' '.__('commerce.purchase_available', ['date' => $available['available_at']->format('d.m.Y H:i')]);
            }
            throw new PurchaseLimitExceeded($message);
        }
        $state = DB::table('shop_purchase_limit_states')->where($this->key($user, $offer))->first();
        $started = $available['available_at'] === null ? now() : Carbon::parse($state->period_started_at);
        $used = $available['available_at'] === null ? 0 : (int) $state->quantity;
        DB::table('shop_purchase_limit_states')->updateOrInsert($this->key($user, $offer), [
            'period_started_at' => $started, 'quantity' => $used + $quantity,
        ]);
    }

    private function key(User $user, Model $offer): array
    {
        return ['user_id' => $user->id, 'offer_type' => $offer->getTable(), 'offer_id' => $offer->getKey()];
    }
}
