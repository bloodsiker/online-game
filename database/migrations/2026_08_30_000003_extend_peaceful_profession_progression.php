<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MAX_LEVEL = 300;

    private const MAX_EXPERIENCE = 1_100_000;

    private const CURVE_POWER = 1.6;

    /** @var array<string, array{level: int, experience: int, seconds: int}> */
    private const RESOURCE_PROFILES = [
        'common' => ['level' => 1, 'experience' => 2, 'seconds' => 8],
        'uncommon' => ['level' => 50, 'experience' => 3, 'seconds' => 12],
        'rare' => ['level' => 100, 'experience' => 5, 'seconds' => 18],
        'epic' => ['level' => 150, 'experience' => 8, 'seconds' => 27],
        'legendary' => ['level' => 200, 'experience' => 11, 'seconds' => 40],
        'heroic' => ['level' => 300, 'experience' => 17, 'seconds' => 60],
    ];

    public function up(): void
    {
        $skillIds = DB::table('skills')
            ->where('type', 'peaceful')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($skillIds === []) {
            return;
        }

        foreach ($skillIds as $skillId) {
            $requirements = $this->requirementsFor($skillId);

            DB::table('skill_level_requirements')->upsert(
                $requirements,
                ['skill_id', 'lvl'],
                ['exp_required', 'exp_diff'],
            );

            $byLevel = collect($requirements)->keyBy('lvl');
            DB::table('player_skills')
                ->where('skill_id', $skillId)
                ->orderBy('id')
                ->eachById(function (object $playerSkill) use ($byLevel): void {
                    $requirement = $byLevel->get(min(self::MAX_LEVEL, max(1, (int) $playerSkill->lvl)));

                    DB::table('player_skills')->where('id', $playerSkill->id)->update([
                        'exp_up' => $requirement['exp_required'],
                        'exp_diff' => $requirement['exp_diff'],
                        'updated_at' => now(),
                    ]);
                });
        }

        DB::table('share_items')
            ->whereIn('skill_id', $skillIds)
            ->whereIn('type', ['resource', 'fish', 'precious_gem', 'plant', 'wood'])
            ->orderBy('id')
            ->eachById(function (object $resource): void {
                $profile = self::RESOURCE_PROFILES[$resource->rarity] ?? self::RESOURCE_PROFILES['common'];

                DB::table('share_items')->where('id', $resource->id)->update([
                    'skill_lvl' => $profile['level'],
                    'skill_exp' => $profile['experience'],
                    'gathering_time_seconds' => $profile['seconds'],
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Прогресс игроков и значения ресурсов меняются осознанно; откат не
        // должен стирать или искажать уже полученный опыт.
    }

    /** @return list<array{skill_id: int, lvl: int, exp_required: int, exp_diff: int}> */
    private function requirementsFor(int $skillId): array
    {
        $requirements = [];
        $previousExperience = 0;

        for ($level = 1; $level <= self::MAX_LEVEL; $level++) {
            $experience = (int) round(
                100 + (self::MAX_EXPERIENCE - 100) * (($level - 1) / (self::MAX_LEVEL - 1)) ** self::CURVE_POWER,
            );

            $requirements[] = [
                'skill_id' => $skillId,
                'lvl' => $level,
                'exp_required' => $experience,
                'exp_diff' => $experience - $previousExperience,
            ];
            $previousExperience = $experience;
        }

        return $requirements;
    }
};
