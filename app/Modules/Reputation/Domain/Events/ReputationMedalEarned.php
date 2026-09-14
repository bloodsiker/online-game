<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Domain\Events;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReputationMedalEarned implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Player $player,
        public ReputationTier $tier,
        public string $medalName,
        public bool $isFeat,
    ) {}
}
