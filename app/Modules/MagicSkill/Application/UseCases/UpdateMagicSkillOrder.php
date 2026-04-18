<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UpdateMagicSkillOrder
{
    public function __construct(
        private readonly MagicSkillWriteRepository $repository,
    ) {}

    public function execute(User $user, array $skillIds): MagicSkillActionResultDTO
    {
        $this->repository->updateSortOrder($user->player, $skillIds);

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: 'Сохранено',
        );
    }
}
