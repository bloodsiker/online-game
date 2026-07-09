<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\DTOs;

final readonly class ReferralStageDTO
{
    public function __construct(
        public int $levelThreshold,
        public string $rewardType,
        public int $rewardValue,
        public ?string $rewardItemName,
        public ?int $rewardItemId,
        public ?string $rewardItemImage,
        public string $description,
    ) {}
}
