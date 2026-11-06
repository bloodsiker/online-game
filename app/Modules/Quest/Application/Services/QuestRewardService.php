<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerLocationAccess;
use App\Modules\Quest\Domain\Enums\QuestRewardType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Events\ReputationMedalEarned;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestRewardService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ChatService $chatService,
        private readonly ExperienceService $experienceService,
        private readonly ReputationService $reputationService,
        private readonly ClanLogService $clanLogService,
    ) {}

    public function grant($user, Quest $quest): void
    {
        $player = $user->player;
        foreach ($quest->rewards as $reward) {
            match ($reward->type) {
                QuestRewardType::EXP => $this->giveExp($player, (int) $reward->amount),
                QuestRewardType::MONEY => $this->giveMoney($player, (int) $reward->amount),
                QuestRewardType::ITEM => $this->giveItem($user, $reward),
                QuestRewardType::LOCATION_ACCESS => $this->giveLocationAccess($player, $reward, $quest),
                QuestRewardType::CLAN_POINTS => $quest->isClan() ? $this->giveClanPoints($user->clanMembership->clan, (int) $reward->amount, $user) : null,
                QuestRewardType::REPUTATION_POINTS => $quest->isClan() ? null : $this->giveReputationPoints($player, $reward),
            };
        }
        if ($quest->isClan()) {
            $this->clanLogService->write($user->clanMembership->clan, $user, ClanLogAction::QUEST_COMPLETED, 'Квест: '.$quest->getTranslation('title', 'ru', false));
        } else {
            $this->reputationService->syncReputationRating($player);
        }
        DB::afterCommit(function () use ($user, $quest, $player): void {
            $this->chatService->sendSystemToUser($user, $this->buildQuestCompleteMessage($quest));
            if (! $quest->isClan()) {
                $featTier = ReputationTier::where('feat_quest_id', $quest->id)->with('reputation')->first();
                if ($featTier && $featTier->hasFeatMedal()) {
                    event(new ReputationMedalEarned($player, $featTier, $featTier->feat_medal_name, isFeat: true));
                }
            }
        });
    }

    private function buildQuestCompleteMessage(Quest $quest): LocalizedChatMessage
    {
        $parts = [];

        foreach ($quest->rewards as $reward) {
            if ($parts !== []) {
                $parts[] = ' | ';
            }
            $parts[] = match ($reward->type) {
                QuestRewardType::EXP => new LocalizedChatMessage('chat.reward_exp', ['amount' => $reward->amount]),
                QuestRewardType::MONEY => new LocalizedChatMessage('chat.reward_money', ['amount' => $reward->amount]),
                QuestRewardType::ITEM => [($reward->amount > 1 ? "{$reward->amount}x " : ''), (
                    $reward->share_item_id && $reward->itemInfo
                        ? "[[share_item_{$reward->share_item_id}]]"
                        : new LocalizedChatMessage('chat.reward_item')
                )],
                QuestRewardType::LOCATION_ACCESS => new LocalizedChatMessage('chat.reward_access', ['location' => $reward->location?->name ?? new LocalizedChatMessage('chat.location')]),
                QuestRewardType::CLAN_POINTS => new LocalizedChatMessage('chat.reward_clan', ['amount' => $reward->amount]),
                QuestRewardType::REPUTATION_POINTS => new LocalizedChatMessage('chat.reward_reputation', ['amount' => $reward->amount]),
            };
        }

        return new LocalizedChatMessage('chat.quest_completed', ['title' => LocalizedChatMessage::contentName($quest, 'title'), 'reward' => new LocalizedChatMessage('chat.reward', ['parts' => $parts])]);
    }

    private function giveClanPoints(Clan $clan, int $amount, $user): void
    {
        $clan->increment('points', $amount);
        $user->clanMembership?->increment('points', $amount);
        $this->clanLogService->write(
            $clan,
            $user,
            ClanLogAction::BONUS_POINTS_EARNED,
            "+{$amount} очков за клановый квест",
        );
    }

    private function giveExp(Player $player, int $amount): void
    {
        $experience = $this->experienceService->calculateGain($player, $amount);

        if ($experience > 0) {
            $player->increment('exp', $experience);
        }
    }

    private function giveMoney($player, int $amount): void
    {
        if ($amount < 0 || $amount > WalletBalanceLimits::MAX_BALANCE) {
            throw ValidationException::withMessages(['quest' => 'Некорректная денежная награда квеста.']);
        }
        if ($amount > 0 && User::whereKey($player->user_id)
            ->where('money', '<=', WalletBalanceLimits::MAX_BALANCE - $amount)->increment('money', $amount) !== 1) {
            throw ValidationException::withMessages(['quest' => 'Награда превысит допустимый баланс кошелька.']);
        }
    }

    private function giveItem($user, QuestReward $reward): void
    {
        if ($reward->share_item_id && $reward->itemInfo) {
            $this->backpackService->addItemByShareItem($user, $reward->itemInfo, (int) ($reward->amount ?: 1));
        }
    }

    private function giveLocationAccess($player, QuestReward $reward, Quest $quest): void
    {
        if ($reward->location_id) {
            PlayerLocationAccess::firstOrCreate([
                'player_id' => $player->id,
                'location_id' => $reward->location_id,
                'quest_id' => $quest->id,
            ]);
        }
    }

    private function giveReputationPoints($player, QuestReward $reward): void
    {
        if (! $reward->reputation_id) {
            return;
        }
        $reputation = Reputation::find($reward->reputation_id);
        if ($reputation) {
            // 2-дневный кулдаун касается только квестов из пула тира;
            // подвиги (feat) и прочие квесты с наградой репутацией его не трогают
            $touchCooldown = $reward->quest?->reputationTierQuests()->exists() ?? false;
            $this->reputationService->addPoints($player, $reputation, (int) $reward->amount, $touchCooldown);
        }
    }
}
