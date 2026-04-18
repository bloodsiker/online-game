<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UpdateEquippedMagicSkills
{
    public function __construct(
        private readonly MagicSkillWriteRepository $repository,
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(User $user, array $equippedIds): MagicSkillActionResultDTO
    {
        $player = $user->player;
        $oldSheet = $this->statService->resolve($player);

        $this->repository->syncEquippedSkills($player, $equippedIds);

        $player->refresh();
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        $message = count($equippedIds) > 0
            ? 'Сохранено'
            : 'Сохранено. Не выбрано ни одного скилла';

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: $message,
        );
    }
}
