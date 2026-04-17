<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\DTOs;

final readonly class ReferralFrameDTO
{
    /**
     * @param  list<ReferralFrameEntryDTO>  $referrals
     */
    public function __construct(public array $referrals) {}
}
