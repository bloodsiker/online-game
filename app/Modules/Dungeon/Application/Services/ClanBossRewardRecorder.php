<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Dungeon\Domain\Events\ClanBossRewardGranted;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRewardReceipt;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

final class ClanBossRewardRecorder
{
    private ?bool $available = null;

    public function record(ClanBossRun $run, ClanBossRunStage $stage, ?User $user, array $rewards, string $reason = 'boss_killed'): void
    {
        if (! $this->isAvailable()) {
            // Переходный период до применения миграции не должен ломать выдачу наград.
            Log::warning('Clan boss reward receipts migration is missing.', ['run_id' => $run->id]);

            return;
        }
        $stage->loadMissing('bossInstance.monster', 'stage');
        $rewards = collect($rewards)->filter(fn (array $entry): bool => $entry['amount'] > 0)
            ->groupBy(fn (array $entry): string => $entry['type'].':'.($entry['item_id'] ?? ''))
            ->map(function (Collection $entries): array {
                $entry = $entries->first();
                $entry['amount'] = $entries->sum('amount');

                return $entry;
            })->values()->all();
        $receipt = ClanBossRewardReceipt::query()->firstOrCreate([
            'clan_boss_run_stage_id' => $stage->id,
            'recipient_type' => $user !== null ? 'player' : 'clan',
            'recipient_id' => $user?->id ?? $run->clan_id,
        ], [
            'clan_boss_run_id' => $run->id,
            'user_id' => $user?->id,
            'recipient_name' => $user?->name ?? (string) $run->clan->name,
            'boss_name' => $stage->bossInstance?->monster?->getTranslations('name') ?? $stage->stage?->getTranslations('name') ?? [],
            'reason' => $reason,
            'rewards' => $rewards,
        ]);

        if ($receipt->wasRecentlyCreated && $receipt->rewards !== []) {
            ClanBossRewardGranted::dispatch($receipt->id);
        }
    }

    public function history(int $runId): Collection
    {
        if (! $this->isAvailable()) {
            return collect();
        }

        return ClanBossRewardReceipt::query()->with(['user.player', 'user.clanMembership.clan', 'stageState.stage'])
            ->where('clan_boss_run_id', $runId)->orderBy('id')->get();
    }

    private function isAvailable(): bool
    {
        return $this->available ??= Schema::hasTable('clan_boss_reward_receipts');
    }
}
