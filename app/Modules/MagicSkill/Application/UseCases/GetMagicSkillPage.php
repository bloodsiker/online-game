<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillPageDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetMagicSkillPage
{
    public function __construct(
        private readonly MagicSkillReadRepository $repository,
    ) {}

    public function execute(User $user, string $group = 'magic_skill'): MagicSkillPageDTO
    {
        $player = $user->player;
        [
            'passiveSkills' => $passiveSkills,
            'activeSkills' => $activeSkills,
            'allyTargets' => $allyTargets,
            'runePassives' => $runePassives,
        ] = $this->repository->getPlayerPageData($player);

        return new MagicSkillPageDTO(
            user: $user,
            player: $player,
            group: $group,
            passiveSkills: $passiveSkills,
            activeSkills: $activeSkills,
            allyTargets: $allyTargets,
            runePassives: $runePassives,
        );
    }
}
