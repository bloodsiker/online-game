<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Quest\Application\Services\QuestActionTransaction;
use App\Modules\Quest\Application\Services\QuestInventoryService;
use App\Modules\Quest\Application\Services\QuestRewardService;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Services\QuestStageRuntimeService;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Validation\ValidationException;

class CompleteQuest
{
    public function __construct(
        private readonly QuestActionTransaction $transaction,
        private readonly QuestInventoryService $inventory,
        private readonly QuestRewardService $rewards,
        private readonly BackpackService $backpack,
        private readonly QuestStageRuntimeService $stages,
    ) {}

    /** True: advanced a stage. False: completed the quest. All effects share one transaction. */
    public function execute(User $user, Quest $quest, int $progressId, int $revision): bool
    {
        return $this->transaction->run($user, $quest, function (User $user) use ($quest, $progressId, $revision): bool {
            $quest = Quest::findOrFail($quest->id);
            $query = $quest->isClan()
                ? QuestClanProgress::where('clan_id', $user->clanMembership->clan_id)
                : QuestPlayer::where('player_id', $user->player_id);
            $progress = $query->whereKey($progressId)->where('quest_id', $quest->id)
                ->with('currentStage')->lockForUpdate()->first();
            if ($progress === null || $progress->status !== QuestPlayerStatus::IN_PROGRESS || (int) $progress->revision !== $revision) {
                throw ValidationException::withMessages(['quest' => 'Состояние квеста изменилось. Откройте задание заново.']);
            }
            if ($quest->isClan() && (int) $progress->user_id !== (int) $user->id) {
                throw ValidationException::withMessages(['quest' => 'Сдать квест может только тот, кто его принял.']);
            }
            $stage = $progress->currentStage;
            if ($stage !== null) {
                if (! $progress->isCurrentStageComplete()) {
                    throw ValidationException::withMessages(['quest' => $this->stages->messageFor($progress) ?: 'Не все задания текущего этапа выполнены.']);
                }
                $stage->loadMissing('objectives', 'grantItem');
                $this->inventory->consume(user: $user, objectives: $stage->objectives);
                if ($stage->grantItem !== null) {
                    $this->backpack->addItemByShareItem($user, $stage->grantItem, (int) $stage->grant_amount);
                }
                $next = QuestStage::where('quest_id', $quest->id)->where('order', '>', $stage->order)->orderBy('order')->first();
                if ($next !== null) {
                    $progress->update([...$this->stages->stateFor($next), 'revision' => $revision + 1]);
                    foreach ($next->objectives->where('type', 'deliver') as $objective) {
                        if ($objective->shareItem !== null) {
                            $this->backpack->addItemByShareItem($user, $objective->shareItem, (int) $objective->required_amount);
                        }
                    }

                    return true;
                }
            } elseif (! $quest->hasStages()) {
                if (! $progress->isAllObjectivesComplete()) {
                    throw ValidationException::withMessages(['quest' => 'Не все задания квеста выполнены.']);
                }
                $this->inventory->consume(user: $user, objectives: $quest->objectives);
            }
            $resetAt = ($quest->isClan() || $quest->isRepeatable()) && $quest->reset_period
                ? now()->addSeconds($quest->reset_period) : null;
            $progress->update([
                ...$this->stages->stateFor(null), 'revision' => $revision + 1,
                'status' => QuestPlayerStatus::COMPLETED, 'completed_at' => now(), 'reset_at' => $resetAt,
            ]);
            $this->rewards->grant($user, $quest);

            return false;
        });
    }
}
