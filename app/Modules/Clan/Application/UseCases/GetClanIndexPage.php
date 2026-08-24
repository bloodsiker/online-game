<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanIndexPageDTO;
use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanIndexPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly NpcReadRepository $npcReadRepository,
    ) {}

    public function execute(User $user): ClanIndexPageDTO
    {
        $context = $this->resolveClanContext->optional($user);
        $registrarNpc = $this->npcReadRepository->findNpcByNameOrFail(
            config('game.clan_registrar_npc_name'),
        );

        if ($context->clan === null) {
            return new ClanIndexPageDTO(false, collect(), false, $registrarNpc);
        }

        return new ClanIndexPageDTO(
            inClan: true,
            activeQuests: $context->clan->activeQuestProgress,
            isLeader: (int) $context->clan->owner_id === $user->id,
            registrarNpc: $registrarNpc,
        );
    }
}
