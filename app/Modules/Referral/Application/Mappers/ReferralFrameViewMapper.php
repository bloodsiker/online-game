<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\Mappers;

use App\Modules\Referral\Application\DTOs\ReferralFrameDTO;
use App\Modules\Referral\Application\DTOs\ReferralFrameEntryDTO;
use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use Illuminate\Support\Collection;

final class ReferralFrameViewMapper
{
    /**
     * @param  Collection<int, Referral>  $referrals
     */
    public function map(Collection $referrals): ReferralFrameDTO
    {
        return new ReferralFrameDTO(
            referrals: $referrals
                ->filter(static fn (Referral $referral): bool => $referral->referred?->player !== null)
                ->map(
                    static fn (Referral $referral): ReferralFrameEntryDTO => new ReferralFrameEntryDTO(
                        userId: $referral->referred->id,
                        name: $referral->referred->name,
                        level: $referral->referred->player->lvl,
                        claimedRewardsCount: $referral->claims->count(),
                    )
                )
                ->values()
                ->all(),
        );
    }
}
