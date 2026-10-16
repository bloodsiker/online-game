<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Services;

use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Interface\Domain\Events\PlayerCameOnline;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class PlayerOnlinePresenceService
{
    public function touch(int $userId, CarbonInterface $seenAt): void
    {
        DB::transaction(function () use ($userId, $seenAt): void {
            $offlineBefore = CarbonImmutable::instance($seenAt)->subMinutes((int) config('game.online_timeout_minutes', 30));
            $becameOnline = User::query()
                ->whereKey($userId)
                ->where(function ($query) use ($offlineBefore): void {
                    $query->whereNull('last_online_at')
                        ->orWhere('last_online_at', '<', $offlineBefore);
                })
                ->update(['last_online_at' => $seenAt]);

            if ($becameOnline > 0) {
                $clanId = ClanMember::query()->where('user_id', $userId)->value('clan_id');

                if ($clanId !== null) {
                    DB::afterCommit(static fn () => PlayerCameOnline::dispatch($userId, (int) $clanId));
                }

                return;
            }

            User::query()
                ->whereKey($userId)
                ->where(function ($query) use ($seenAt): void {
                    $query->whereNull('last_online_at')
                        ->orWhere('last_online_at', '<', $seenAt);
                })
                ->update(['last_online_at' => $seenAt]);
        }, attempts: 3);
    }
}
