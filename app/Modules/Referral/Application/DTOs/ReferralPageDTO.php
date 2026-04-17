<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\DTOs;

final readonly class ReferralPageDTO
{
    /**
     * @param  list<ReferralInviteDTO>  $referred
     * @param  list<ReferralStageDTO>  $stages
     */
    public function __construct(
        public string $referralLink,
        public ?string $invitedByName,
        public array $referred,
        public array $stages,
    ) {}
}
