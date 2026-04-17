<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\DTOs;

final readonly class ReferralFrameEntryDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public int $level,
        public int $claimedRewardsCount,
    ) {}
}
