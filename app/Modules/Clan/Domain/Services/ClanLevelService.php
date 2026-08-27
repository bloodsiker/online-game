<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Clan\Domain\Models\ClanLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClanLevelService
{
    private const CACHE_KEY = 'clan_level_thresholds';

    public function levelForExperience(float $experience): int
    {
        $level = 1;

        foreach ($this->thresholds() as $threshold) {
            if ($experience < $threshold['experience_required']) {
                break;
            }

            $level = $threshold['level'];
        }

        return $level;
    }

    public function forgetThresholds(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function synchronizeAllClans(): int
    {
        $updated = 0;

        DB::table('clans')
            ->select(['id', 'experience', 'lvl'])
            ->orderBy('id')
            ->eachById(function (object $clan) use (&$updated): void {
                $level = $this->levelForExperience((float) $clan->experience);
                if ((int) $clan->lvl === $level) {
                    return;
                }

                DB::table('clans')->where('id', $clan->id)->update(['lvl' => $level]);
                $updated++;
            });

        return $updated;
    }

    /** @return list<array{level: int, experience_required: float}> */
    private function thresholds(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return ClanLevel::query()
                ->orderBy('experience_required')
                ->orderBy('level')
                ->get(['level', 'experience_required'])
                ->map(fn (ClanLevel $level): array => [
                    'level' => $level->level,
                    'experience_required' => (float) $level->experience_required,
                ])
                ->all();
        });
    }
}
