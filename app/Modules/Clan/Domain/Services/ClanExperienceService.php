<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\DB;

class ClanExperienceService
{
    public function __construct(private ClanLevelService $clanLevelService) {}

    public function awardForMonsterExperience(Player $player, Monster $monster, int $playerExperience): float
    {
        if ($playerExperience <= 0) {
            return 0.0;
        }

        $percent = $this->percentForLevelDifference($monster->lvl - $player->lvl);
        if ($percent === 0) {
            return 0.0;
        }

        $player->loadMissing('user.clanMembership');
        $membership = $player->user?->clanMembership;
        if ($membership === null) {
            return 0.0;
        }

        $experience = round($playerExperience * $percent / 100, 2);
        if ($experience <= 0) {
            return 0.0;
        }

        DB::transaction(function () use ($membership, $experience): void {
            $clan = DB::table('clans')
                ->select(['id', 'experience', 'lvl'])
                ->where('id', $membership->clan_id)
                ->lockForUpdate()
                ->first();

            if ($clan === null) {
                return;
            }

            $newExperience = round((float) $clan->experience + $experience, 2);
            $newLevel = $this->clanLevelService->levelForExperience($newExperience);

            DB::table('clans')->where('id', $membership->clan_id)->update([
                'experience' => $newExperience,
                'lvl' => $newLevel,
            ]);
            DB::table('clan_members')->where('id', $membership->id)->increment('experience_contributed', $experience);
        });

        return $experience;
    }

    public function percentForLevelDifference(int $monsterLevelDifference): int
    {
        return match (true) {
            $monsterLevelDifference < -10 => 0,
            $monsterLevelDifference <= 0 => 1,
            $monsterLevelDifference < 10 => 3,
            default => 5,
        };
    }
}
