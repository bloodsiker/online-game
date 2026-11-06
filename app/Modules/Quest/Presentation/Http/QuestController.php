<?php

declare(strict_types=1);

namespace App\Modules\Quest\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Quest\Application\Services\QuestActionTransaction;
use App\Modules\Quest\Application\UseCases\CompleteQuest;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Enums\QuestType;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Quest\Domain\Services\QuestStageRuntimeService;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestDialogue;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTierQuest;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestController extends Controller
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ChatService $chatService,
        private readonly ExperienceService $experienceService,
        private readonly ReputationService $reputationService,
        private readonly ClanLogService $clanLogService,
        private readonly QuestStageRuntimeService $questStageRuntimeService,
        private readonly QuestProgressService $questProgressService,
        private readonly ?MapInfluenceRequirementService $influenceRequirements = null,
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
                    ->with('clan', 'quest.rewards.itemInfo', 'quest.rewards.location', 'quest.stages.objectives', 'quest.dialogues', 'objectives.questObjective', 'currentStage', 'user')
                    ->paginate(20)
                    ->withQueryString();
                $clanQuestProgress = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
                    ->where('status', QuestPlayerStatus::IN_PROGRESS)
                    ->with('quest', 'objectives.questObjective', 'currentStage')
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

        $quests = $query->with('quest.rewards.itemInfo', 'quest.rewards.location', 'quest.stages.objectives', 'quest.dialogues', 'currentStage')->paginate(20)->withQueryString();
        $questIds = $quests->pluck('id')->implode(',');

        return view('quest::list', compact('quests', 'questIds', 'tab')
            + ['clanQuests' => collect(), 'clanQuestProgress' => null]);
    }

    public function quest($id, Request $request)
    {
        // stages + objectives.map: firstStage()/hasStages() и карта в view
        // работают по уже загруженным связям без лишних exists()/lazy-запросов.
        $quest = Quest::with('rewards.itemInfo', 'rewards.location', 'rewards.reputation', 'dialogues', 'stages', 'objectives.map')->find($id);
        $npc = Npc::find($request->integer('npc'));
        $user = Auth::user();
        $player = $user->player;

        $questPlayer = null;
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
            $stageMessage = $this->questStageRuntimeService->messageFor($clanProgress);

            [$dialoguePage, $dialogueNextUrl, $showObjectives] = $this->resolveDialoguePage($quest, $inProgress, $request, $npc?->id);

            return view('quest::quest', compact(
                'quest', 'npc', 'inProgress', 'visibleObjectives', 'currentStage',
                'canComplete', 'progressMap', 'clanProgress', 'isAcceptor', 'canAccept',
                'dialoguePage', 'dialogueNextUrl', 'showObjectives', 'stageMessage', 'questPlayer'
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
        $stageMessage = $this->questStageRuntimeService->messageFor($questPlayer);

        [$dialoguePage, $dialogueNextUrl, $showObjectives] = $this->resolveDialoguePage($quest, $inProgress, $request, $npc?->id);

        return view('quest::quest', compact(
            'quest', 'npc', 'inProgress', 'visibleObjectives', 'currentStage',
            'canComplete', 'progressMap', 'clanProgress', 'isAcceptor', 'canAccept',
            'dialoguePage', 'dialogueNextUrl', 'showObjectives', 'stageMessage', 'questPlayer'
        ));
    }

    /**
     * До принятия — реплики показываются по одной, цели скрыты кроме последней (не спойлерим раньше времени).
     * После принятия — если реплики есть, для контекста просто показываем последнюю (без пагинации/кнопки —
     * та часть шаблона, где используется $nextUrl, рендерится только пока квест не принят); если реплик
     * нет — ничего не меняем, шаблон сам возьмёт $quest->description.
     *
     * Реплика с npc_id=null видна на странице любого NPC (старые многостраничные монологи
     * одного персонажа). Реплика с заполненным npc_id видна только на странице этого NPC —
     * иначе, например, финальная реплика NPC, принимающего квест, "утекала" бы в пагинацию
     * у стартового NPC ещё до принятия задания.
     *
     * @return array{0: ?QuestDialogue, 1: ?string, 2: bool}
     */
    private function resolveDialoguePage(Quest $quest, bool $inProgress, Request $request, ?int $npcId): array
    {
        $dialogues = $quest->dialogues
            ->filter(fn (QuestDialogue $d) => $d->npc_id === null || $d->npc_id === $npcId)
            ->values();

        if ($dialogues->isEmpty()) {
            return [null, null, true];
        }

        if ($inProgress) {
            return [$dialogues->last(), null, true];
        }

        $page = max(1, min($dialogues->count(), $request->integer('page', 1)));
        $currentDialogue = $dialogues->get($page - 1);
        $isLast = $page >= $dialogues->count();

        $nextUrl = $isLast
            ? route('quest.take', ['id' => $quest->id, 'npc' => $npcId])
            : route('quest', ['id' => $quest->id, 'npc' => $npcId, 'page' => $page + 1]);

        return [$currentDialogue, $nextUrl, $isLast];
    }

    private function takeClan($id, Request $request, User $user)
    {
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        if ($this->influenceRequirements !== null && ! $this->influenceRequirements->allowsQuest($user->id, $quest->id)) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Для этого задания недостаточно влияния на территории.');
        }

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

        if ($existing?->status === QuestPlayerStatus::IN_PROGRESS) {
            return redirect()->route('npc', ['id' => $npcId])->with('quest_error', 'Клан уже выполняет этот квест.');
        }

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
                    'revision' => (int) $existing->revision + 1,
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    ...$this->questStageRuntimeService->stateFor($firstStage),
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
                    ...$this->questStageRuntimeService->stateFor($firstStage),
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestClanObjective::create([
                        'quest_clan_progress_id' => $progress->id,
                        'quest_objective_id' => $objective->id,
                    ]);
                }
                $this->giveDeliverItems($user, $quest, $progress->current_stage_id);
            }

            $this->clanLogService->write(
                $clan,
                $user,
                ClanLogAction::QUEST_STARTED,
                'Квест: '.$quest->getTranslation('title', 'ru', false),
            );
        });

        DB::afterCommit(fn () => $this->chatService->sendQuestToUser($user, new LocalizedChatMessage('chat.clan_quest_started', ['title' => LocalizedChatMessage::contentName($quest, 'title')])));

        return redirect()->route('npc', ['id' => $npcId]);
    }

    public function cancelQuest(int $id): RedirectResponse
    {
        $progress = QuestPlayer::whereKey($id)->where('player_id', Auth::user()->player_id)->firstOrFail();

        return app(QuestActionTransaction::class)->run(Auth::user(), $progress->quest,
            fn (User $user) => $this->cancelQuestLocked($id, $user));
    }

    private function cancelQuestLocked(int $id, User $user): RedirectResponse
    {
        $player = $user->player;

        $questPlayer = QuestPlayer::where('player_id', $player->id)
            ->where('id', $id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('quest.objectives.shareItem')
            ->firstOrFail();

        DB::transaction(function () use ($questPlayer, $player) {
            foreach ($questPlayer->quest->objectives->where('type', 'deliver') as $objective) {
                $shareItem = $objective->shareItem;
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
        $progress = QuestClanProgress::findOrFail($id);

        return app(QuestActionTransaction::class)->run(Auth::user(), $progress->quest,
            fn (User $user) => $this->cancelClanQuestLocked($id, $request, $user));
    }

    private function cancelClanQuestLocked($id, Request $request, User $user)
    {
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
            ->with('quest.objectives.shareItem', 'user')
            ->firstOrFail();

        DB::transaction(function () use ($user, $clan, $progress) {
            // Remove deliver items from the acceptor's backpack
            foreach ($progress->quest->objectives->where('type', 'deliver') as $objective) {
                $shareItem = $objective->shareItem;
                if ($shareItem && $progress->user) {
                    $this->backpackService->removeItemByShareItem($progress->user, $shareItem, $objective->required_amount);
                }
            }

            $progress->objectives()->delete();
            $progress->delete();

            $this->clanLogService->write(
                $clan,
                $user,
                ClanLogAction::QUEST_CANCELLED,
                'Квест: '.$progress->quest->getTranslation('title', 'ru', false),
            );
        });

        $redirectRoute = $npcId ? redirect()->route('npc', ['id' => $npcId]) : redirect()->route('quests', ['tab' => 'clan']);

        return $redirectRoute->with('quest_success', 'Клановый квест отменён.');
    }

    public function take($id, Request $request)
    {
        $quest = Quest::findOrFail($id);

        return app(QuestActionTransaction::class)->run(Auth::user(), $quest,
            fn (User $user) => $this->takeLocked($id, $request, $user));
    }

    private function takeLocked($id, Request $request, User $user)
    {
        $player = $user->player;
        $quest = Quest::findOrFail($id);
        $npcId = $request->integer('npc');

        if ($this->influenceRequirements !== null && ! $this->influenceRequirements->allowsQuest($user->id, $quest->id)) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', 'Для этого задания недостаточно влияния на территории.');
        }

        // Clan quests have their own take method
        if ($quest->isClan()) {
            return $this->takeClan($id, $request, $user);
        }

        // Квест-подвиг нельзя взять прямым запросом до достижения его тира.
        $featTier = ReputationTier::query()
            ->with('reputation')
            ->where('feat_quest_id', $quest->id)
            ->first();

        if ($featTier) {
            $points = $this->reputationService
                ->getOrCreate($player, $featTier->reputation)
                ->points;

            if ($points < $featTier->min_points) {
                return redirect()->route('npc', ['id' => $npcId])
                    ->with('quest_error', "Квест-подвиг доступен с {$featTier->min_points} очков репутации.");
            }
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
            $itemGate = $this->questProgressService->resolveItemGate($existingQuestPlayer->player->user, $quest);
            DB::transaction(function () use ($existingQuestPlayer, $quest, $itemGate) {
                $firstStage = $quest->firstStage();
                $existingQuestPlayer->objectives()->delete();
                $existingQuestPlayer->update([
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    'revision' => (int) $existingQuestPlayer->revision + 1,
                    ...$this->questStageRuntimeService->stateFor($firstStage),
                    'completed_at' => null,
                    'reset_at' => null,
                ]);
                foreach ($quest->objectives as $objective) {
                    QuestPlayerObjective::create([
                        'quest_player_id' => $existingQuestPlayer->id,
                        'quest_objective_id' => $objective->id,
                        'amount' => $itemGate->initialAmountFor($objective),
                    ]);
                }
                // Give deliver items for new run (first stage only if staged)
                $this->giveDeliverItems($existingQuestPlayer->player->user, $quest, $firstStage?->id);
            });

            DB::afterCommit(fn () => $this->chatService->sendQuestToUser($user, new LocalizedChatMessage('chat.quest_started', ['title' => LocalizedChatMessage::contentName($quest, 'title')])));

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

        // Item-gate objectives (type=collect, target_type=item)
        // не имеют живого триггера прогресса после взятия квеста: их amount замораживается
        // в момент take() и никогда не пересчитывается заново. Если пустить игрока без всех
        // предметов на руках, квест станет НАВСЕГДА невыполнимым, даже когда предметы появятся
        // позже — поэтому просто не даём взять квест, пока не собрано всё нужное.
        $itemGate = $this->questProgressService->resolveItemGate($user, $quest);
        $missingItemGate = $itemGate->missingDescription();
        if ($missingItemGate !== null) {
            return redirect()->route('npc', ['id' => $npcId])
                ->with('quest_error', "Для взятия этого квеста нужно уже иметь при себе: {$missingItemGate}.");
        }

        DB::transaction(function () use ($player, $user, $quest, $itemGate, &$existingQuestPlayer) {
            $firstStage = $quest->firstStage();
            $questPlayer = QuestPlayer::create([
                'player_id' => $player->id,
                'quest_id' => $quest->id,
                ...$this->questStageRuntimeService->stateFor($firstStage),
            ]);

            foreach ($quest->objectives as $objective) {
                QuestPlayerObjective::create([
                    'quest_player_id' => $questPlayer->id,
                    'quest_objective_id' => $objective->id,
                    'amount' => $itemGate->initialAmountFor($objective),
                ]);
            }

            // Give deliver items for first stage only (or all if non-staged)
            $this->giveDeliverItems($user, $quest, $questPlayer->current_stage_id);

            $existingQuestPlayer = $questPlayer;
        });

        DB::afterCommit(fn () => $this->chatService->sendQuestToUser($user, new LocalizedChatMessage('chat.quest_started', ['title' => LocalizedChatMessage::contentName($quest, 'title')])));

        return redirect()->route('npc', ['id' => $npcId]);
    }

    public function complete($id, Request $request): RedirectResponse
    {
        $data = $request->validate(['progress_id' => 'required|integer|min:1', 'revision' => 'required|integer|min:0']);
        $quest = Quest::findOrFail($id);
        try {
            $advanced = app(CompleteQuest::class)->execute(
                user: Auth::user(), quest: $quest,
                progressId: (int) $data['progress_id'], revision: (int) $data['revision'],
            );
        } catch (ValidationException $exception) {
            return redirect()->route('npc', ['id' => $request->integer('npc')])->with('quest_error', $exception->validator->errors()->first());
        }
        if ($advanced) {
            return redirect()->route('quest', ['id' => $quest->id, 'npc' => $request->integer('npc')]);
        }

        return redirect()->route('npc', ['id' => $request->integer('npc')])
            ->with('quest_success', 'Квест выполнен! Награда получена.');
    }

    private function giveDeliverItems($user, Quest $quest, ?int $stageId = null): void
    {
        $objectives = $quest->objectives->where('type', 'deliver');

        if ($stageId !== null) {
            $objectives = $objectives->where('stage_id', $stageId);
        }

        $objectives->loadMissing('shareItem');

        foreach ($objectives as $objective) {
            $shareItem = $objective->shareItem;
            if ($shareItem) {
                $this->backpackService->addItemByShareItem($user, $shareItem, $objective->required_amount);
            }
        }
    }
}
