<?php

declare(strict_types=1);

namespace App\Modules\Quest\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerLocationAccess;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Enums\QuestRewardType;
use App\Modules\Quest\Domain\Enums\QuestType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTierQuest;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestController extends Controller
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ChatService $chatService,
        private readonly ReputationService $reputationService,
    ) {}

    public function list(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $tab = $request->input('tab', 'started');

        $clanQuestProgress = null;
        $clanQuests = collect();

        if ($tab === 'clan') {
            $clanMembership = $user->clanMembership;
            if ($clanMembership) {
                $clanQuests = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
                    ->orderByDesc('id')
                    ->with('quest.rewards.itemInfo', 'quest.rewards.location', 'quest.stages.objectives', 'objectives.questObjective', 'user')
                    ->paginate(20)
                    ->withQueryString();
                $clanQuestProgress = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
                    ->where('status', QuestPlayerStatus::IN_PROGRESS)
                    ->with('quest', 'objectives.questObjective')
                    ->first();
            }

            return view('quest::list', compact('tab', 'clanQuests', 'clanQuestProgress')
                + ['quests' => collect(), 'questIds' => '']);
        }

        $query = QuestPlayer::where('player_id', $player->id)
            ->orderByDesc('id');

        if ($tab === 'repeatable') {
            $query->whereHas('quest', fn ($q) => $q->where('type', QuestType::REPEATABLE));
        } elseif ($tab === 'completed') {
            $query->where('status', QuestPlayerStatus::COMPLETED)
                ->whereHas('quest', fn ($q) => $q->whereNotIn('type', [QuestType::REPEATABLE, QuestType::CLAN]));
        } else {
            $tab = 'started';
            $query->where('status', QuestPlayerStatus::IN_PROGRESS)
                ->whereHas('quest', fn ($q) => $q->whereNotIn('type', [QuestType::REPEATABLE, QuestType::CLAN]));
        }

        $quests = $query->with('quest.rewards.itemInfo', 'quest.rewards.location', 'quest.stages.objectives')->paginate(20)->withQueryString();
        $questIds = $quests->pluck('id')->implode(',');

        return view('quest::list', compact('quests', 'questIds', 'tab')
            + ['clanQuests' => collect(), 'clanQuestProgress' => null]);
    }

    public function quest($id, Request $request)
    {
        $quest = Quest::with('rewards.itemInfo', 'rewards.location', 'rewards.reputation')->find($id);
        $npc = Npc::find($request->integer('npc'));
        $user = Auth::user();
        $player = $user->player;

        // --- CLAN QUEST ---
        if ($quest->isClan()) {
            $clanMembership = $user->clanMembership;
            $clanProgress = null;

            if ($clanMembership) {
                $clanProgress = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
                    ->where('quest_id', $quest->id)
                    ->where('status', QuestPlayerStatus::IN_PROGRESS)
                    ->with('objectives.questObjective')
                    ->first();
            }

            $inProgress = $clanProgress !== null;
            $isAcceptor = $clanProgress && (int) $clanProgress->user_id === $user->id;
            $progressMap = [];

            if ($clanProgress) {
                foreach ($clanProgress->objectives as $co) {
                    $progressMap[$co->quest_objective_id] = $co->questObjective->type === 'deliver'
                        ? $co->questObjective->required_amount
                        : $co->amount;
                }
            }

            if ($clanProgress && $clanProgress->current_stage_id !== null) {
                $visibleObjectives = $quest->objectives->where('stage_id', $clanProgress->current_stage_id);
                $currentStage = $clanProgress->currentStage;
                $stageComplete = $clanProgress->isCurrentStageComplete();
                $correctNpc = $currentStage && $currentStage->complete_npc_id == $npc->id;
                $canComplete = $isAcceptor && $stageComplete && $correctNpc;
            } else {
                $firstStage = $quest->firstStage();
                $visibleObjectives = $firstStage
                    ? $quest->objectives->where('stage_id', $firstStage->id)
                    : $quest->objectives;
                $currentStage = $firstStage;
                $allDone = $clanProgress && $clanProgress->isAllObjectivesComplete();
                $correctNpc = (int) $quest->complete_npc_id === $npc->id;
                $canComplete = $isAcceptor && $allDone && $correctNpc;
            }

            // Can accept: in clan, this user has no active clan quest already
            $canAccept = $clanMembership && ! $inProgress && ! QuestClanProgress::where('user_id', $user->id)
                ->where('status', QuestPlayerStatus::IN_PROGRESS)
                ->exists();

            return view('quest::quest', compact(
                'quest', 'npc', 'inProgress', 'visibleObjectives', 'currentStage',
                'canComplete', 'progressMap', 'clanProgress', 'isAcceptor', 'canAccept'
            ));
        }

        // --- PERSONAL QUEST ---
        $questPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('quest_id', $quest->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective')
            ->first();

        $inProgress = $questPlayer !== null;

        $progressMap = [];
        if ($questPlayer) {
            foreach ($questPlayer->objectives as $po) {
                $progressMap[$po->quest_objective_id] = $po->questObjective->type === 'deliver'
                    ? $po->questObjective->required_amount
                    : $po->amount;
            }
        }

        if ($questPlayer && $questPlayer->current_stage_id !== null) {
            $visibleObjectives = $quest->objectives->where('stage_id', $questPlayer->current_stage_id);
            $currentStage = $questPlayer->currentStage;
            $stageComplete = $questPlayer->isCurrentStageComplete();
            $correctNpc = $currentStage && $currentStage->complete_npc_id == $npc->id;
            $canComplete = $stageComplete && $correctNpc;
        } else {
            $firstStage = $quest->firstStage();
            $visibleObjectives = $firstStage
                ? $quest->objectives->where('stage_id', $firstStage->id)
                : $quest->objectives;
            $currentStage = $firstStage;
            $allDone = $questPlayer && $questPlayer->isAllObjectivesComplete();
            $correctNpc = (int) $quest->complete_npc_id === $npc->id;
            $canComplete = $inProgress && $allDone && $correctNpc;
        }

        $clanProgress = null;
        $isAcceptor = false;
        $canAccept = true;

        return view('quest::quest', compact(
            'quest', 'npc', 'inProgress', 'visibleObjectives', 'currentStage',
            'canComplete', 'progressMap', 'clanProgress', 'isAcceptor', 'canAccept'
        ));
    }

    public function takeClan($id, Request $request)
    {
        $user = Auth::user();
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        if (! $quest->isClan()) {
            return redirect()->route('npc', ['id' => $npcId]);
        }

        $clanMembership = $user->clanMembership;
        if (! $clanMembership) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Вы не состоите в клане.');
        }

        $clan = $clanMembership->clan;

        $existing = QuestClanProgress::where('clan_id', $clan->id)
            ->where('quest_id', $quest->id)
            ->first();

        // Repeatable on cooldown
        if ($existing
            && $existing->status === QuestPlayerStatus::COMPLETED
            && $existing->reset_at
            && now()->lt($existing->reset_at)
        ) {
            $diff = now()->diffForHumans($existing->reset_at, ['parts' => 2]);

            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', "Клановый квест будет доступен через {$diff}.");
        }

        // Check this user has no active clan quest already
        $activeExists = QuestClanProgress::where('user_id', $user->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->exists();

        if ($activeExists) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'У вас уже есть активный клановый квест.');
        }

        DB::transaction(function () use ($user, $clan, $quest, $existing) {
            $firstStage = $quest->firstStage();

            if ($existing) {
                // Reset repeatable
                $existing->objectives()->delete();
                $existing->update([
                    'user_id' => $user->id,
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    'current_stage_id' => $firstStage?->id,
                    'completed_at' => null,
                    'reset_at' => null,
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestClanObjective::create([
                        'quest_clan_progress_id' => $existing->id,
                        'quest_objective_id' => $objective->id,
                    ]);
                }
                $this->giveDeliverItems($user, $quest, $firstStage?->id);
            } else {
                $progress = QuestClanProgress::create([
                    'quest_id' => $quest->id,
                    'clan_id' => $clan->id,
                    'user_id' => $user->id,
                    'current_stage_id' => $firstStage?->id,
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestClanObjective::create([
                        'quest_clan_progress_id' => $progress->id,
                        'quest_objective_id' => $objective->id,
                    ]);
                }
                $this->giveDeliverItems($user, $quest, $progress->current_stage_id);
            }

            ClanLog::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'action' => ClanLogAction::QUEST_STARTED,
                'details' => "Квест: {$quest->title}",
            ]);
        });

        $this->chatService->sendQuestToUser($user, "Для вашего клана начался квест <b>«{$quest->title}»</b>. Удачи!");

        return redirect()->route('npc', ['id' => $npcId]);
    }

    public function cancelQuest(int $id): RedirectResponse
    {
        $player = Auth::user()->player;

        $questPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('id', $id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('quest.objectives')
            ->firstOrFail();

        DB::transaction(function () use ($questPlayer, $player) {
            foreach ($questPlayer->quest->objectives->where('type', 'deliver') as $objective) {
                $shareItem = ShareItem::find($objective->target_id);
                if ($shareItem) {
                    $this->backpackService->removeItemByShareItem($player->user, $shareItem, $objective->required_amount);
                }
            }

            $questPlayer->objectives()->delete();
            $questPlayer->delete();
        });

        if ($questPlayer->quest->type === QuestType::REPUTATION) {
            $tierQuest = ReputationTierQuest::where('quest_id', $questPlayer->quest->id)
                ->with('tier')
                ->first();

            if ($tierQuest) {
                $reputation = $tierQuest->tier->reputation;
                if ($reputation) {
                    $pr = $this->reputationService->getOrCreate($player, $reputation);
                    $pr->update(['last_completed_at' => now()]);
                    session()->forget('rep_offer_'.$player->id.'_'.$reputation->id);
                }
            }
        }

        return redirect()->route('quests')->with('quest_success', 'Квест отменён.');
    }

    public function cancelClanQuest($id, Request $request)
    {
        $user = Auth::user();
        $npcId = $request->integer('npc');

        $clanMembership = $user->clanMembership;
        if (! $clanMembership) {
            return redirect()->back()->with('quest_error', 'Вы не состоите в клане.');
        }

        $clan = $clanMembership->clan;

        if ((int) $clan->owner_id !== $user->id) {
            return redirect()->back()->with('quest_error', 'Только лидер клана может отменить квест.');
        }

        $progress = QuestClanProgress::where('clan_id', $clan->id)
            ->where('id', $id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('quest')
            ->firstOrFail();

        DB::transaction(function () use ($user, $clan, $progress) {
            // Remove deliver items from the acceptor's backpack
            foreach ($progress->quest->objectives->where('type', 'deliver') as $objective) {
                $shareItem = ShareItem::find($objective->target_id);
                if ($shareItem) {
                    $acceptorUser = User::find($progress->user_id);
                    if ($acceptorUser) {
                        $this->backpackService->removeItemByShareItem($acceptorUser, $shareItem, $objective->required_amount);
                    }
                }
            }

            $progress->objectives()->delete();
            $progress->delete();

            ClanLog::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'action' => ClanLogAction::QUEST_CANCELLED,
                'details' => "Квест: {$progress->quest->title}",
            ]);
        });

        $redirectRoute = $npcId ? redirect()->route('npc', ['id' => $npcId]) : redirect()->route('quests', ['tab' => 'clan']);

        return $redirectRoute->with('quest_success', 'Клановый квест отменён.');
    }

    public function completeClan($id, Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        $clanMembership = $user->clanMembership;
        if (! $clanMembership) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Вы не состоите в клане.');
        }

        $clan = $clanMembership->clan;

        $clanProgress = QuestClanProgress::where('clan_id', $clan->id)
            ->where('quest_id', $quest->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective', 'currentStage')
            ->first();

        if (! $clanProgress) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Клановый квест не найден или уже завершён.');
        }

        if ((int) $clanProgress->user_id !== $user->id) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Сдать квест может только тот, кто его принял.');
        }

        // --- STAGED ---
        if ($clanProgress->current_stage_id !== null) {
            if (! $clanProgress->isCurrentStageComplete()) {
                return redirect()->route('npc', ['id' => $npcId])
                    ->with('quest_error', 'Не все задания текущего этапа выполнены.');
            }

            $currentStage = $clanProgress->currentStage;

            foreach ($currentStage->objectives->where('type', 'deliver') as $objective) {
                $shareItem = ShareItem::find($objective->target_id);
                if ($shareItem && ! $this->backpackService->hasItemByShareItem($user, $shareItem, $objective->required_amount)) {
                    return redirect()->route('npc', ['id' => $npcId])
                        ->with('quest_error', "В рюкзаке нет нужного предмета: {$shareItem->name}.");
                }
            }

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
                DB::transaction(function () use ($user, $currentStage, $nextStage, $clanProgress) {
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
                    $clanProgress->update(['current_stage_id' => $nextStage->id]);
                    foreach ($nextStage->objectives->where('type', 'deliver') as $objective) {
                        $shareItem = ShareItem::find($objective->target_id);
                        if ($shareItem) {
                            $this->backpackService->addItemByShareItem($user, $shareItem, $objective->required_amount);
                        }
                    }
                });

                return redirect()->route('quest', ['id' => $quest->id, 'npc' => $npcId]);
            }

            DB::transaction(function () use ($user, $currentStage, $clanProgress) {
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
                $clanProgress->update(['current_stage_id' => null]);
            });

            $clanProgress->refresh();
        }

        // --- FINAL COMPLETION ---
        if (! $quest->hasStages() && ! $clanProgress->isAllObjectivesComplete()) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Не все задания квеста выполнены.');
        }

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

        DB::transaction(function () use ($user, $player, $quest, $clanProgress, $clan) {
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

            foreach ($quest->rewards as $reward) {
                match ($reward->type) {
                    QuestRewardType::EXP => $this->giveExp($player, (int) $reward->amount),
                    QuestRewardType::MONEY => $this->giveMoney($player, (int) $reward->amount),
                    QuestRewardType::ITEM => $this->giveItem($user, $reward),
                    QuestRewardType::LOCATION_ACCESS => $this->giveLocationAccess($player, $reward, $quest),
                    QuestRewardType::CLAN_POINTS => $this->giveClanPoints($clan, (int) $reward->amount, $user),
                    QuestRewardType::REPUTATION_POINTS => null, // clan quests don't give reputation
                };
            }

            $resetAt = $quest->reset_period ? now()->addSeconds($quest->reset_period) : null;

            $clanProgress->update([
                'status' => QuestPlayerStatus::COMPLETED,
                'completed_at' => now(),
                'reset_at' => $resetAt,
            ]);

            ClanLog::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'action' => ClanLogAction::QUEST_COMPLETED,
                'details' => "Квест: {$quest->title}",
            ]);
        });

        $this->chatService->sendSystemToUser($user, $this->buildQuestCompleteMessage($quest));

        return redirect()->route('npc', ['id' => $npcId])
            ->with('quest_success', 'Клановый квест выполнен! Награда получена.');
    }

    public function take($id, Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        // Clan quests have their own take method
        if ($quest->isClan()) {
            return $this->takeClan($id, $request);
        }

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
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    'current_stage_id' => $firstStage?->id,
                    'completed_at' => null,
                    'reset_at' => null,
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestPlayerObjective::create([
                        'quest_player_id' => $existingQuestPlayer->id,
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
                'player_id' => $player->id,
                'quest_id' => $quest->id,
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

        if ($quest->isClan()) {
            return $this->completeClan($id, $request);
        }

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
                    QuestRewardType::EXP => $this->giveExp($player, (int) $reward->amount),
                    QuestRewardType::MONEY => $this->giveMoney($player, (int) $reward->amount),
                    QuestRewardType::ITEM => $this->giveItem($user, $reward),
                    QuestRewardType::LOCATION_ACCESS => $this->giveLocationAccess($player, $reward, $quest),
                    QuestRewardType::CLAN_POINTS => null, // personal quests can't have clan_points reward
                    QuestRewardType::REPUTATION_POINTS => $this->giveReputationPoints($player, $reward),
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
                QuestRewardType::EXP => "+{$reward->amount} опыта",
                QuestRewardType::MONEY => "+{$reward->amount} монет",
                QuestRewardType::ITEM => ($reward->amount > 1 ? "{$reward->amount}x " : '').($reward->itemInfo?->name ?? 'предмет'),
                QuestRewardType::LOCATION_ACCESS => 'открыт доступ к «'.($reward->location?->name ?? 'локации').'»',
                QuestRewardType::CLAN_POINTS => "+{$reward->amount} клановых очков",
                QuestRewardType::REPUTATION_POINTS => "+{$reward->amount} очков репутации",
            };
        }

        return implode(' | ', array_filter($parts));
    }

    private function giveClanPoints(Clan $clan, int $amount, $user): void
    {
        $clan->increment('points', $amount);
        $user->clanMembership?->increment('points', $amount);
        ClanLog::create([
            'clan_id' => $clan->id,
            'user_id' => $user->id,
            'action' => ClanLogAction::BONUS_POINTS_EARNED,
            'details' => "+{$amount} очков за клановый квест",
        ]);
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
