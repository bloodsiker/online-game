<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Jobs;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class UpdatePlayerOnlinePresence implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $playerId,
        public readonly DateTimeInterface $seenAt,
    ) {}

    public function handle(): void
    {
        $seenAt = CarbonImmutable::instance($this->seenAt);

        User::query()
            ->where('player_id', $this->playerId)
            ->where(function ($query) use ($seenAt): void {
                $query->whereNull('last_online_at')
                    ->orWhere('last_online_at', '<', $seenAt);
            })
            ->update(['last_online_at' => $seenAt]);
    }
}
