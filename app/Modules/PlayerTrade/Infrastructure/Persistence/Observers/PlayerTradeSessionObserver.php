<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Infrastructure\Persistence\Observers;

use App\Modules\PlayerTrade\Domain\Events\PlayerTradeChanged;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;

final class PlayerTradeSessionObserver
{
    public function created(PlayerTradeSession $trade): void
    {
        PlayerTradeChanged::dispatch($trade->uuid);
    }

    public function updated(PlayerTradeSession $trade): void
    {
        if ($trade->wasChanged(['status', 'initiator_offer', 'target_offer', 'initiator_confirmed', 'target_confirmed', 'expires_at'])) {
            PlayerTradeChanged::dispatch($trade->uuid);
        }
    }
}
