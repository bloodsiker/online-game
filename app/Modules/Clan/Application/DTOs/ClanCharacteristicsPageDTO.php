<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLevel;
use App\Modules\Clan\Domain\Models\ClanMember;

final readonly class ClanCharacteristicsPageDTO
{
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public ?ClanLevel $nextLevel,
        public float $currentLevelExperience,
        public float $experienceToNextLevel,
        public float $progressPercent,
    ) {}
}
