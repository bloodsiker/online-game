<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\DTOs;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Support\Collection;

final readonly class ReputationPageDTO
{
    public function __construct(
        public Reputation $reputation,
        public PlayerReputation $pr,
        public ?ReputationTier $currentTier,
        public bool $canTake,
        public mixed $activeQuest,
        public ?string $cooldownDiff,
        public Collection $earnedMedals,
        public Collection $earnedFeatMedals,
        public array $progressMap,
        public ?string $message,
        public string $messageType,
        public Player $player,
        public string $group,
    ) {}
}
