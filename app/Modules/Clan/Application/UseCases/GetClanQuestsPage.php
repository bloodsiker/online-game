<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanQuestsPageDTO;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanQuestsPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user): ClanQuestsPageDTO
    {
        $context = $this->resolveClanContext->require($user);

        $activeQuests = QuestClanProgress::where('clan_id', $context->clan->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective', 'quest.rewards', 'user')
            ->get();

        $availableQuests = Quest::where('type', 'clan')
            ->where('is_active', true)
            ->with('objectives', 'rewards')
            ->get();

        $history = QuestClanProgress::where('clan_id', $context->clan->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->with('quest', 'user')
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get();

        return new ClanQuestsPageDTO(
            clan: $context->clan,
            membership: $context->membership,
            isLeader: (int) $context->clan->owner_id === $user->id,
            activeQuests: $activeQuests,
            availableQuests: $availableQuests,
            history: $history,
        );
    }
}
