<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\Listeners;

use App\Modules\Reputation\Domain\Events\ReputationMedalEarned;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputationMedal;

class RecordEarnedMedal
{
    public function handle(ReputationMedalEarned $event): void
    {
        PlayerReputationMedal::firstOrCreate(
            [
                'player_id' => $event->player->id,
                'tier_id' => $event->tier->id,
                'is_feat' => $event->isFeat,
            ],
            [
                'reputation_id' => $event->tier->reputation_id,
                'earned_at' => now(),
            ],
        );
    }
}
