<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class LearnClanSkill
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly ClanSkillService $skillService,
    ) {}

    public function execute(User $user, int $definitionId): ?string
    {
        $context = $this->resolveClanContext->requirePermission(
            $user,
            ClanPermission::LEARN_SKILL,
            'У вас нет прав изучать навыки клана.'
        );

        $player = $user->player;

        if ($player === null) {
            throw new RuntimeException('Персонаж не найден.');
        }

        $definition = ClanSkillDefinition::findOrFail($definitionId);

        return $this->skillService->learn($context->clan, $definition, $player, $user);
    }
}
