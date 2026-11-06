<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;
use App\Modules\Referral\Domain\Contracts\TransactionManager;
use App\Modules\Referral\Domain\Enums\ReferralRewardType;

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

        $this->transactionManager->run(function () use ($referral): void {
            $referral = $this->referralRepository->lockRewardReferral($referral);
            if ($referral === null) {
                return;
            }
            $claimedStageIds = $this->referralRepository->getClaimedStageIds($referral->id);
            $stages = $this->referralRepository->getUnlockedStages((int) $referral->referred->player->lvl, $claimedStageIds);
            $referrerId = (int) $referral->referrer_user_id;
            foreach ($stages as $stage) {
                if ($stage->reward_value < 1 || ($stage->reward_type === ReferralRewardType::ITEM && $stage->reward_item_id === null)) {
                    throw new \RuntimeException('Некорректная настройка реферальной награды.');
                }
                $this->referralRepository->createClaim($referral->id, $stage->id);
                match ($stage->reward_type) {
                    ReferralRewardType::GOLD => $this->rewardIssuer->grantGold($referrerId, $stage->reward_value),
                    ReferralRewardType::DIAMOND => $this->rewardIssuer->grantDiamonds($referrerId, $stage->reward_value),
                    ReferralRewardType::ITEM => $this->rewardIssuer->grantItem($referrerId, $stage->reward_item_id, $stage->reward_value),
                };
            }
        });
    }
}
