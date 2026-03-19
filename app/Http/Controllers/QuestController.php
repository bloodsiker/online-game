<?php

namespace App\Http\Controllers;

use App\Enums\QuestPlayerStatus;
use App\Enums\QuestType;
use App\Models\Npc;
use App\Models\Player\PlayerLocationAccess;
use App\Models\Quest\Quest;
use App\Models\Quest\QuestPlayer;
use App\Models\Quest\QuestPlayerObjective;
use App\Models\Quest\QuestReward;
use App\Models\Quest\QuestStage;
use App\Models\Share\ShareItem;
use App\Services\BackpackService;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestController extends Controller
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ChatService $chatService,
    ) {}

    public function list(Request $request)
    {
        $player = Auth::user()->player;
        $tab = $request->input('tab', 'started');

        $query = QuestPlayer::where('player_id', $player->id)
            ->orderByDesc('id');

        if ($tab === 'repeatable') {
            $query->whereHas('quest', fn ($q) => $q->where('type', QuestType::REPEATABLE));
        } elseif ($tab === 'completed') {
            $query->where('status', QuestPlayerStatus::COMPLETED)
                ->whereHas('quest', fn ($q) => $q->where('type', '!=', QuestType::REPEATABLE));
        } else {
            $tab = 'started';
            $query->where('status', QuestPlayerStatus::IN_PROGRESS)
                ->whereHas('quest', fn ($q) => $q->where('type', '!=', QuestType::REPEATABLE));
        }

        $quests = $query->with('quest.rewards.itemInfo', 'quest.rewards.location', 'quest.stages.objectives')->paginate(20)->withQueryString();
        $questIds = $quests->pluck('id')->implode(',');

        return view('quest.list', compact('quests', 'questIds', 'tab'));
    }

    public function quest($id, Request $request)
    {
        $quest = Quest::find($id);
        $npc = Npc::find($request->integer('npc'));
        $player = Auth::user()->player;

        $questPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('quest_id', $quest->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective')
            ->first();

        $inProgress = $questPlayer !== null;

        // Build a map of objective_id => player amount for progress display
        // deliver type is always considered "given" — backpack check happens at NPC
        $progressMap = [];
        if ($questPlayer) {
            foreach ($questPlayer->objectives as $po) {
                $progressMap[$po->quest_objective_id] = $po->questObjective->type === 'deliver'
                    ? $po->questObjective->required_amount
                    : $po->amount;
            }
        }

        if ($questPlayer && $questPlayer->current_stage_id !== null) {
            // Show only current stage objectives
            $visibleObjectives = $quest->objectives->where('stage_id', $questPlayer->current_stage_id);
            $currentStage      = $questPlayer->currentStage;

            $stageComplete  = $questPlayer->isCurrentStageComplete();
            $correctNpc     = $currentStage && $currentStage->complete_npc_id == $npc->id;
            $canComplete    = $stageComplete && $correctNpc;
        } else {
            // Not taken yet or no stages: show first stage objectives only (or all if non-staged)
            $firstStage        = $quest->firstStage();
            $visibleObjectives = $firstStage
                ? $quest->objectives->where('stage_id', $firstStage->id)
                : $quest->objectives;
            $currentStage      = $firstStage;

            $allDone     = $questPlayer && $questPlayer->isAllObjectivesComplete();
            $correctNpc  = (int) $quest->complete_npc_id === $npc->id;
            $canComplete = $inProgress && $allDone && $correctNpc;
        }

        return view('quest.quest', compact('quest', 'npc', 'inProgress', 'visibleObjectives', 'currentStage', 'canComplete', 'progressMap'));
    }

    public function take($id, Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        $existingQuestPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('quest_id', $quest->id)
            ->first();

        // Repeatable quest on cooldown
        if ($existingQuestPlayer
            && $existingQuestPlayer->status === QuestPlayerStatus::COMPLETED
            && $quest->isRepeatable()
        ) {
            if ($existingQuestPlayer->reset_at && now()->lt($existingQuestPlayer->reset_at)) {
                $diff = now()->diffForHumans($existingQuestPlayer->reset_at, ['parts' => 2]);

                return redirect()->route('npc', ['id' => $npcId])
                    ->with('quest_error', "Квест будет доступен через {$diff}.");
            }

            // Reset for another run
            DB::transaction(function () use ($existingQuestPlayer, $quest) {
                $firstStage = $quest->firstStage();
                $existingQuestPlayer->objectives()->delete();
                $existingQuestPlayer->update([
                    'status'           => QuestPlayerStatus::IN_PROGRESS,
                    'current_stage_id' => $firstStage?->id,
                    'completed_at'     => null,
                    'reset_at'         => null,
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestPlayerObjective::create([
                        'quest_player_id'    => $existingQuestPlayer->id,
                        'quest_objective_id' => $objective->id,
                    ]);
                }
                // Give deliver items for new run (first stage only if staged)
                $this->giveDeliverItems($existingQuestPlayer->player->user, $quest, $firstStage?->id);
            });

            $this->chatService->sendQuestToUser($user, "Для Вас начался квест <b>«{$quest->title}»</b>. Желаем удачи!");

            return redirect()->route('npc', ['id' => $npcId]);
        }

        // Already in progress or completed (one_time/main)
        if ($existingQuestPlayer) {
            return redirect()->route('npc', ['id' => $npcId]);
        }

        // Check parent quest is completed
        if ($quest->after_quest_id) {
            $afterDone = QuestPlayer::where('player_id', $player->id)
                ->where('quest_id', $quest->after_quest_id)
                ->where('status', QuestPlayerStatus::COMPLETED)
                ->exists();

            if (! $afterDone) {
                return redirect()->route('npc', ['id' => $npcId])
                    ->with('quest_error', 'Для взятия этого квеста необходимо выполнить предыдущий квест.');
            }
        }

        DB::transaction(function () use ($player, $user, $quest, &$existingQuestPlayer) {
            $questPlayer = QuestPlayer::create([
                'player_id'        => $player->id,
                'quest_id'         => $quest->id,
                'current_stage_id' => $quest->firstStage()?->id,
            ]);

            foreach ($quest->objectives as $objective) {
                QuestPlayerObjective::create([
                    'quest_player_id' => $questPlayer->id,
                    'quest_objective_id' => $objective->id,
                ]);
            }

            // Give deliver items for first stage only (or all if non-staged)
            $this->giveDeliverItems($user, $quest, $questPlayer->current_stage_id);

            $existingQuestPlayer = $questPlayer;
        });

        $this->chatService->sendQuestToUser($user, "Для Вас начался квест <b>«{$quest->title}»</b>. Желаем удачи!");

        return redirect()->route('npc', ['id' => $npcId]);
    }

    public function complete($id, Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        $questPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('quest_id', $quest->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective', 'currentStage')
            ->first();

        if (! $questPlayer) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Квест не найден или уже завершён.');
        }

        // --- STAGED QUEST: player is on an active stage ---
        if ($questPlayer->current_stage_id !== null) {
            if (! $questPlayer->isCurrentStageComplete()) {
                return redirect()->route('npc', ['id' => $npcId])
                    ->with('quest_error', 'Не все задания текущего этапа выполнены.');
            }

            $currentStage = $questPlayer->currentStage;

            // Check deliver items for this stage
            foreach ($currentStage->objectives->where('type', 'deliver') as $objective) {
                $shareItem = ShareItem::find($objective->target_id);
                if ($shareItem && ! $this->backpackService->hasItemByShareItem($user, $shareItem, $objective->required_amount)) {
                    return redirect()->route('npc', ['id' => $npcId])
                        ->with('quest_error', "В рюкзаке нет нужного предмета: {$shareItem->name}.");
                }
            }

            // Check collect items for this stage
            foreach ($currentStage->objectives->where('type', 'collect') as $objective) {
                if (! $objective->share_item_id) {
                    continue;
                }
                $shareItem = ShareItem::find($objective->share_item_id);
                if ($shareItem && ! $this->backpackService->hasItemByShareItem($user, $shareItem, $objective->required_amount)) {
                    return redirect()->route('npc', ['id' => $npcId])
                        ->with('quest_error', "В рюкзаке нет нужного предмета: {$shareItem->name}.");
                }
            }

            $nextStage = QuestStage::where('quest_id', $quest->id)
                ->where('order', '>', $currentStage->order)
                ->orderBy('order')
                ->first();

            if ($nextStage) {
                DB::transaction(function () use ($user, $currentStage, $nextStage, $questPlayer) {
                    // Remove deliver items for completed stage
                    foreach ($currentStage->objectives->where('type', 'deliver') as $objective) {
                        $shareItem = ShareItem::find($objective->target_id);
                        if ($shareItem) {
                            $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                        }
                    }

                    // Remove collect items for completed stage
                    foreach ($currentStage->objectives->where('type', 'collect') as $objective) {
                        if (! $objective->share_item_id) {
                            continue;
                        }
                        $shareItem = ShareItem::find($objective->share_item_id);
                        if ($shareItem) {
                            $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                        }
                    }

                    $questPlayer->update(['current_stage_id' => $nextStage->id]);

                    // Give deliver items for the new stage
                    foreach ($nextStage->objectives->where('type', 'deliver') as $objective) {
                        $shareItem = ShareItem::find($objective->target_id);
                        if ($shareItem) {
                            $this->backpackService->addItemByShareItem($user, $shareItem, $objective->required_amount);
                        }
                    }
                });

                // Redirect to quest detail page so the NPC delivers the new stage briefing
                return redirect()->route('quest', ['id' => $quest->id, 'npc' => $npcId]);
            }

            // Last stage done — remove its deliver and collect items and fall through to quest completion
            DB::transaction(function () use ($user, $currentStage, $questPlayer) {
                foreach ($currentStage->objectives->where('type', 'deliver') as $objective) {
                    $shareItem = ShareItem::find($objective->target_id);
                    if ($shareItem) {
                        $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                    }
                }
                foreach ($currentStage->objectives->where('type', 'collect') as $objective) {
                    if (! $objective->share_item_id) {
                        continue;
                    }
                    $shareItem = ShareItem::find($objective->share_item_id);
                    if ($shareItem) {
                        $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                    }
                }
                $questPlayer->update(['current_stage_id' => null]);
            });

            $questPlayer->refresh();
        }

        // --- NON-STAGED QUEST or all stages done: final completion ---
        if (! $quest->hasStages() && ! $questPlayer->isAllObjectivesComplete()) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Не все задания квеста выполнены.');
        }

        // Check deliver and collect items for non-staged quest
        if (! $quest->hasStages()) {
            foreach ($quest->objectives->where('type', 'deliver') as $objective) {
                $shareItem = ShareItem::find($objective->target_id);
                if ($shareItem && ! $this->backpackService->hasItemByShareItem($user, $shareItem, $objective->required_amount)) {
                    return redirect()->route('npc', ['id' => $npcId])
                        ->with('quest_error', "В рюкзаке нет нужного предмета: {$shareItem->name}.");
                }
            }
            foreach ($quest->objectives->where('type', 'collect') as $objective) {
                if (! $objective->share_item_id) {
                    continue;
                }
                $shareItem = ShareItem::find($objective->share_item_id);
                if ($shareItem && ! $this->backpackService->hasItemByShareItem($user, $shareItem, $objective->required_amount)) {
                    return redirect()->route('npc', ['id' => $npcId])
                        ->with('quest_error', "В рюкзаке нет нужного предмета: {$shareItem->name}.");
                }
            }
        }

        DB::transaction(function () use ($user, $player, $quest, $questPlayer) {
            // Remove deliver and collect items from backpack (non-staged only; staged already handled above)
            if (! $quest->hasStages()) {
                foreach ($quest->objectives->where('type', 'deliver') as $objective) {
                    $shareItem = ShareItem::find($objective->target_id);
                    if ($shareItem) {
                        $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                    }
                }
                foreach ($quest->objectives->where('type', 'collect') as $objective) {
                    if (! $objective->share_item_id) {
                        continue;
                    }
                    $shareItem = ShareItem::find($objective->share_item_id);
                    if ($shareItem) {
                        $this->backpackService->removeItemByShareItem($user, $shareItem, $objective->required_amount);
                    }
                }
            }

            // Give rewards
            foreach ($quest->rewards as $reward) {
                match ($reward->type) {
                    \App\Enums\QuestRewardType::EXP             => $this->giveExp($player, (int) $reward->amount),
                    \App\Enums\QuestRewardType::MONEY           => $this->giveMoney($player, (int) $reward->amount),
                    \App\Enums\QuestRewardType::ITEM            => $this->giveItem($user, $reward),
                    \App\Enums\QuestRewardType::LOCATION_ACCESS => $this->giveLocationAccess($player, $reward, $quest),
                };
            }

            $resetAt = $quest->isRepeatable() && $quest->reset_period
                ? now()->addSeconds($quest->reset_period)
                : null;

            $questPlayer->update([
                'status' => QuestPlayerStatus::COMPLETED,
                'completed_at' => now(),
                'reset_at' => $resetAt,
            ]);
        });

        // Send personal system chat notification about quest completion
        $this->chatService->sendSystemToUser($user, $this->buildQuestCompleteMessage($quest));

        return redirect()->route('npc', ['id' => $npcId])
            ->with('quest_success', 'Квест выполнен! Награда получена.');
    }

    private function buildQuestCompleteMessage(Quest $quest): string
    {
        $parts = ["Квест «{$quest->title}» выполнен! Награда:"];

        foreach ($quest->rewards as $reward) {
            $parts[] = match ($reward->type) {
                \App\Enums\QuestRewardType::EXP             => "+{$reward->amount} опыта",
                \App\Enums\QuestRewardType::MONEY           => "+{$reward->amount} монет",
                \App\Enums\QuestRewardType::ITEM            => ($reward->amount > 1 ? "{$reward->amount}x " : '').($reward->itemInfo?->name ?? 'предмет'),
                \App\Enums\QuestRewardType::LOCATION_ACCESS => 'открыт доступ к «'.($reward->location?->name ?? 'локации').'»',
            };
        }

        return implode(' | ', array_filter($parts));
    }

    private function giveDeliverItems($user, Quest $quest, ?int $stageId = null): void
    {
        $objectives = $quest->objectives->where('type', 'deliver');

        if ($stageId !== null) {
            $objectives = $objectives->where('stage_id', $stageId);
        }

        foreach ($objectives as $objective) {
            $shareItem = ShareItem::find($objective->target_id);
            if ($shareItem) {
                $this->backpackService->addItemByShareItem($user, $shareItem, $objective->required_amount);
            }
        }
    }

    private function giveExp($player, int $amount): void
    {
        $player->increment('exp', $amount);
    }

    private function giveMoney($player, int $amount): void
    {
        $player->user->increment('money', $amount);
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
}
