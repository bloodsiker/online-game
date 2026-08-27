<?php

declare(strict_types=1);

namespace App\Modules\Npc\Application\UseCases;

use App\Modules\Npc\Application\DTOs\NpcPageDTO;
use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetNpcPage
{
    public function __construct(
        private readonly NpcReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $npcId): NpcPageDTO
    {
        $player = $user->player;
        $npc = $this->readRepository->findNpcByIdOrFail($npcId);

        $questStates = $this->readRepository->getPlayerQuestStateGroups($player->id);
        $completedQuestIds = $questStates['completed'];
        $inProgressQuestIds = $questStates['in_progress'];
        $repeatableReadyIds = $questStates['repeatable_ready'];
        $questsOnCooldown = $this->readRepository->getQuestsOnCooldown($player->id, $npc->id);

        $clanMembership = $user->clanMembership;
        $clanQuestExcludeIds = [];
        $clanQuestsInProgress = collect();

        if ($clanMembership) {
            $clanProgressAtNpc = $this->readRepository->getClanProgressAtNpc($clanMembership->clan_id, $npc->id);

            foreach ($clanProgressAtNpc as $cp) {
                if ($cp->status === QuestPlayerStatus::IN_PROGRESS) {
                    $clanQuestExcludeIds[] = $cp->quest_id;
                } elseif ($cp->status === QuestPlayerStatus::COMPLETED
                    && $cp->reset_at
                    && now()->lt($cp->reset_at)) {
                    $clanQuestExcludeIds[] = $cp->quest_id;
                    $questsOnCooldown->push((object) [
                        'quest' => $cp->quest,
                        'reset_at' => $cp->reset_at,
                        'diff' => $cp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2),
                    ]);
                }
            }

            $clanQuestsInProgress = $this->readRepository->getClanInProgressForUser($user->id, $clanMembership->clan_id)
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

        $excludeIds = array_unique(array_merge(
            array_diff(array_merge($completedQuestIds, $inProgressQuestIds), $repeatableReadyIds),
            $clanQuestExcludeIds
        ));

        $quests = $this->readRepository->getAvailableQuests(
            npcId: $npc->id,
            excludeIds: $excludeIds,
            completedQuestIds: $completedQuestIds,
            hasClanMembership: $clanMembership !== null,
        );

        $reputations = $this->readRepository->getNpcReputations($npc->id);

        foreach ($reputations as $reputation) {
            $pr = $this->reputationService->getOrCreate($player, $reputation);
            $tier = $this->reputationService->getCurrentTier($reputation, $pr->points);

            // Подвиг (feat) предлагается независимо от кулдауна ежедневных заданий
            $featQuest = $this->reputationService->getAvailableFeatQuest($player, $reputation, $pr->points);
            if ($featQuest && ! in_array((int) $featQuest->id, array_map('intval', $inProgressQuestIds), true)) {
                $featQuest->is_feat = true;
                $quests->push($featQuest);
            }

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

        $questsInProgress = $this->readRepository->getInProgressQuestPlayers($player->id, $inProgressQuestIds)
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

        // Репутации этого НПС, у которых есть магазин — для ссылки на страницу магазина
        $reputationShops = $reputations->filter(fn ($reputation) => $reputation->shopItems->isNotEmpty())->values();

        return new NpcPageDTO(
            npc: $npc,
            quests: $quests,
            questsInProgress: $questsInProgress,
            questsOnCooldown: $questsOnCooldown,
            message: $message,
            messageType: $messageType,
            player: $player,
            dialogueStartNode: $this->readRepository->getStartDialogueNode($npc->id),
            reputationShops: $reputationShops,
            isClanRegistrar: $npc->name === config('game.clan_registrar_npc_name'),
            hasClanMembership: $clanMembership !== null,
        );
    }
}
