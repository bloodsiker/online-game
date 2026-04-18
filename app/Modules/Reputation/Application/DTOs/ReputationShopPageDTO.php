<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\DTOs;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;

final readonly class ReputationShopPageDTO
{
    public function __construct(
        public Reputation $reputation,
        public PlayerReputation $pr,
        public ?string $message,
        public string $messageType,
        public Player $player,
    ) {}
}
