<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanCharacteristicsPageDTO;
use App\Modules\Clan\Domain\Models\ClanLevel;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanCharacteristicsPage
{
    public function __construct(private readonly ResolveClanContext $resolveClanContext) {}

    public function execute(User $user): ClanCharacteristicsPageDTO
    {
        $context = $this->resolveClanContext->require($user);
        $currentLevel = ClanLevel::query()->where('level', $context->clan->lvl)->first();
        $nextLevel = ClanLevel::query()
            ->where('level', '>', $context->clan->lvl)
            ->orderBy('level')
            ->first();

        $experience = (float) $context->clan->experience;
        $currentLevelExperience = (float) ($currentLevel?->experience_required ?? 0);
        $experienceToNextLevel = $nextLevel === null
            ? 0.0
            : max(0.0, (float) $nextLevel->experience_required - $experience);
        $progressPercent = $nextLevel === null
            ? 100.0
            : min(100.0, max(0.0, ($experience - $currentLevelExperience)
                / max(1.0, (float) $nextLevel->experience_required - $currentLevelExperience) * 100));

        return new ClanCharacteristicsPageDTO(
            clan: $context->clan,
            membership: $context->membership,
            nextLevel: $nextLevel,
            currentLevelExperience: $currentLevelExperience,
            experienceToNextLevel: $experienceToNextLevel,
            progressPercent: $progressPercent,
        );
    }
}
