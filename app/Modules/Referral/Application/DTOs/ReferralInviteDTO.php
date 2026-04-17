<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\DTOs;

final readonly class ReferralInviteDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?int $level,
        public bool $isOnline,
        public ?string $lastOnlineLabel,
        public int $claimedRewardsCount,
        public int $totalStagesCount,
    ) {}
}
