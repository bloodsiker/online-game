<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\MagicSkillRequirementService;

class UpdateEquippedMagicSkills
{
    public function __construct(
        private readonly MagicSkillWriteRepository $repository,
        private readonly PlayerStatService $statService,
        private readonly MagicSkillRequirementService $requirementService,
    ) {}

    public function execute(User $user, array $equippedIds): MagicSkillActionResultDTO
    {
        $player = $user->player;
        $oldSheet = $this->statService->resolve($player);

        $rejected = [];
        $allowedIds = [];

        $ownedSkills = MagicSkill::whereIn('id', $equippedIds)
            ->whereHas('players', fn ($q) => $q->where('player_id', $player->id))
            ->get();

        foreach ($ownedSkills as $skill) {
            $unmet = $this->requirementService->check($player, $skill);

            if ($unmet === null) {
                $allowedIds[] = $skill->id;
            } else {
                $rejected[] = $skill->name.' ('.$unmet.')';
            }
        }

        $this->repository->syncEquippedSkills($player, $allowedIds);

        $player->refresh();
        $this->statService->invalidate($player);
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        $message = match (true) {
            $rejected !== [] => 'Сохранено. Не экипированы (не выполнены требования): '.implode('; ', $rejected),
            count($allowedIds) > 0 => 'Сохранено',
            default => 'Сохранено. Не выбрано ни одного скилла',
        };

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: $message,
        );
    }
}
