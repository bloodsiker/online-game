<?php

namespace App\Http\Controllers;

use App\Enums\QuestPlayerStatus;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Npc;
use App\Models\Quest\Quest;
use App\Models\Quest\QuestClanProgress;
use App\Models\Quest\QuestPlayer;
use App\Models\Reputation\Reputation;
use App\Services\ReputationService;
use Illuminate\Support\Facades\Auth;

class NpcController extends Controller
{
    public function __construct(
        private readonly ReputationService $reputationService,
    ) {}

    public function index($id)
    {
        $user = Auth::user();
        $player = $user->player;

        $npc = Npc::find($id);

        $completedQuestIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->pluck('quest_id')
            ->toArray();

        $inProgressQuestIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->pluck('quest_id')
            ->toArray();

        // Repeatable quests that are completed and cooldown has passed (ready to take again)
        $repeatableReadyIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable'))
            ->where(fn ($q) => $q->whereNull('reset_at')->orWhere('reset_at', '<=', now()))
            ->pluck('quest_id')
            ->toArray();

        // Repeatable quests still on cooldown — show at NPC but block taking
        $questsOnCooldown = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable')
                ->where('start_npc_id', $npc->id)
                ->isActive()
            )
            ->where('reset_at', '>', now())
            ->with('quest')
            ->get()
            ->map(fn ($qp) => (object) [
                'quest' => $qp->quest,
                'reset_at' => $qp->reset_at,
                'diff' => $qp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2),
            ]);

        // Clan quest exclusions and cooldowns
        $clanMembership = $user->clanMembership;
        $clanQuestExcludeIds = [];
        $clanQuestsInProgress = collect();

        if ($clanMembership) {
            // Quests starting at this NPC: used for exclusion from available list and cooldown display
            $clanProgressAtNpc = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
                ->whereHas('quest', fn ($q) => $q->where('start_npc_id', $npc->id)->isActive())
                ->with('quest')
                ->get();

            foreach ($clanProgressAtNpc as $cp) {
                if ($cp->status === QuestPlayerStatus::IN_PROGRESS) {
                    $clanQuestExcludeIds[] = $cp->quest_id;
                } elseif ($cp->status === QuestPlayerStatus::COMPLETED
                    && $cp->reset_at
                    && now()->lt($cp->reset_at)
                ) {
                    $clanQuestExcludeIds[] = $cp->quest_id;
                    $questsOnCooldown->push((object) [
                        'quest' => $cp->quest,
                        'reset_at' => $cp->reset_at,
                        'diff' => $cp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2),
                    ]);
                }
            }

            // In-progress clan quests accepted by this user, completable at this NPC
            $clanQuestsInProgress = QuestClanProgress::where('user_id', $user->id)
                ->where('clan_id', $clanMembership->clan_id)
                ->where('status', QuestPlayerStatus::IN_PROGRESS)
                ->with('quest', 'currentStage.objectives')
                ->get()
                ->filter(function (QuestClanProgress $cp) use ($npc) {
                    if ($cp->current_stage_id !== null && $cp->currentStage) {
                        return (int) $cp->currentStage->complete_npc_id === (int) $npc->id;
                    }

                    return (int) $cp->quest->complete_npc_id === (int) $npc->id;
                })
                ->map(function (QuestClanProgress $cp) {
                    $cp->quest->questPlayer = $cp;
                    $cp->quest->canComplete = $cp->isCurrentStageComplete();
                    $cp->quest->currentStage = $cp->currentStage;

                    return $cp->quest;
                })
                ->values();
        }

        // Available quests at this NPC (started here)
        $excludeIds = array_unique(array_merge(
            array_diff(
                array_merge($completedQuestIds, $inProgressQuestIds),
                $repeatableReadyIds
            ),
            $clanQuestExcludeIds
        ));

        $quests = Quest::whereNotIn('id', $excludeIds)
            ->isActive()
            ->where('start_npc_id', $npc->id)
            ->where('type', '!=', 'reputation')
            ->where(function ($query) use ($completedQuestIds) {
                $query->whereNull('after_quest_id')
                    ->orWhereIn('after_quest_id', $completedQuestIds);
            })
            ->when(! $clanMembership, fn ($q) => $q->where('type', '!=', 'clan'))
            ->get();

        // Reputation quests: show 1 random quest per reputation based on player's current tier
        $reputations = Reputation::with('tiers.quests.quest')
            ->where('npc_id', $npc->id)
            ->get();

        foreach ($reputations as $reputation) {
            $pr = $this->reputationService->getOrCreate($player, $reputation);
            $tier = $this->reputationService->getCurrentTier($reputation, $pr->points);

            if (! $tier || $tier->quests->isEmpty()) {
                continue;
            }

            if (! $this->reputationService->canTakeQuest($player, $reputation)) {
                $cooldownDiff = $this->reputationService->getCooldownDiff($player, $reputation);
                if ($cooldownDiff) {
                    $questsOnCooldown->push((object) [
                        'quest' => null,
                        'label' => $reputation->name,
                        'reset_at' => null,
                        'diff' => $cooldownDiff,
                    ]);
                }

                continue;
            }

            $sessionKey = 'rep_offer_'.$player->id.'_'.$reputation->id;
            $questCollection = $tier->quests->pluck('quest')->filter();

            $offeredQuestId = session($sessionKey);
            $randomQuest = $offeredQuestId
                ? $questCollection->firstWhere('id', $offeredQuestId)
                : null;

            if (! $randomQuest) {
                $randomQuest = $questCollection->random();
                session([$sessionKey => $randomQuest->id]);
            }

            $randomQuest->reputation_id = $reputation->id;
            $quests->push($randomQuest);
        }

        // In-progress quests completable at this NPC (non-staged OR current stage belongs to this NPC)
        $inProgressQuestPlayers = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->whereIn('quest_id', $inProgressQuestIds)
            ->with('objectives.questObjective', 'quest', 'currentStage.objectives')
            ->get();

        $questsInProgress = $inProgressQuestPlayers
            ->filter(function (QuestPlayer $qp) use ($npc) {
                if ($qp->current_stage_id !== null && $qp->currentStage) {
                    return (int) $qp->currentStage->complete_npc_id === (int) $npc->id;
                }

                return (int) $qp->quest->complete_npc_id === (int) $npc->id;
            })
            ->map(function (QuestPlayer $qp) {
                $qp->quest->questPlayer = $qp;
                $qp->quest->canComplete = $qp->isCurrentStageComplete();
                $qp->quest->currentStage = $qp->currentStage;

                return $qp->quest;
            })
            ->values()
            ->merge($clanQuestsInProgress);

        $message = session('quest_error') ?? session('quest_success');
        $messageType = session()->has('quest_success') ? 'success' : 'error';

        return view('npc.index', compact('npc', 'quests', 'questsInProgress', 'questsOnCooldown', 'message', 'messageType', 'player'));
    }

    public function info($id)
    {
        $monsterLocation = MonsterOnLocation::find($id);
        $monster = $monsterLocation->monster;

        return view('monster.info', compact('monster'));
    }
}
