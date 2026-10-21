<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStageMonster;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final readonly class ClanBossPageDTO
{
    /**
     * @param  Collection<int, array{user: mixed, damage: int, hits: int, rewarded: bool}>  $damageRows
     */
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public ?ClanBossRun $run,
        public ?ClanBossRunStage $currentState,
        public Collection $previousBosses,
        public ?DungeonStageMonster $nextBoss,
        public Collection $upcomingBosses,
        public Collection $damageRows,
        public ?ClanBossContribution $myContribution,
        public ?int $hitsLeft,
        public ?CarbonInterface $personalAvailableAt,
        public LengthAwarePaginator $archiveRuns,
        public ?ClanBossRun $archivedRun,
        public Collection $archivedStages,
        public Collection $archivedDamageRows,
        public Collection $archivedStageDamage,
        public Collection $rewardReceipts,
        public Collection $archivedRewardReceipts,
    ) {}
}
