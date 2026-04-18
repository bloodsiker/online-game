<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Enums\ReferralRewardType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;
use App\Modules\Referral\Domain\Contracts\TransactionManager;

final readonly class GrantReferralRewards
{
    public function __construct(
        private ReferralRepository $referralRepository,
        private ReferralRewardIssuer $rewardIssuer,
        private TransactionManager $transactionManager,
    ) {}

    public function handle(Player $player): void
    {
        $referral = $this->referralRepository->findReferralByReferredUserId($player->user_id);

        if ($referral === null || $referral->referrer === null) {
            return;
        }

        $claimedStageIds = $this->referralRepository->getClaimedStageIds($referral->id);
        $stages = $this->referralRepository->getUnlockedStages($player->lvl, $claimedStageIds);

        if ($stages->isEmpty()) {
            return;
        }

        $referrerId = $referral->referrer->id;

        $this->transactionManager->run(function () use ($stages, $referral, $referrerId): void {
            foreach ($stages as $stage) {
                match ($stage->reward_type) {
                    ReferralRewardType::GOLD => $this->rewardIssuer->grantGold($referrerId, $stage->reward_value),
                    ReferralRewardType::DIAMOND => $this->rewardIssuer->grantDiamonds($referrerId, $stage->reward_value),
                    ReferralRewardType::ITEM => $stage->reward_item_id !== null
                        ? $this->rewardIssuer->grantItem($referrerId, $stage->reward_item_id, $stage->reward_value)
                        : null,
                };

                $this->referralRepository->createClaim($referral->id, $stage->id);
            }
        });
    }
}
