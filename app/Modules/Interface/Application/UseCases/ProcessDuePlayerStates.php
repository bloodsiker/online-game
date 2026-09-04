<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Interface\Domain\Events\PlayerStateUpdated;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class ProcessDuePlayerStates
{
    public function __construct(private HeartbeatPlayer $heartbeatPlayer) {}

    public function execute(CarbonInterface $now): int
    {
        $effectPlayerIds = $this->dueEffectPlayerIds($now);
        $regenerationPlayerIds = $this->dueRegenerationPlayerIds($now);
        $playerIds = $effectPlayerIds
            ->merge($regenerationPlayerIds)
            ->unique()
            ->values();

        if ($playerIds->isEmpty()) {
            return 0;
        }

        $processed = 0;
        $players = Player::query()
            ->with('user')
            ->whereIn('id', $playerIds)
            ->get();

        foreach ($players as $player) {
            if ($player->user === null) {
                continue;
            }

            $hpBefore = (int) $player->hp_now;
            $mpBefore = (int) $player->mp_now;
            $heartbeat = $this->heartbeatPlayer->execute($player->user, touchOnline: false);
            $state = $heartbeat->toArray();

            if ($heartbeat->dead) {
                $state['death_url'] = route('location');
            }

            if ($effectPlayerIds->contains((int) $player->id)
                || $hpBefore !== $heartbeat->hp['current']
                || $mpBefore !== $heartbeat->mp['current']) {
                PlayerStateUpdated::dispatch((int) $player->id, $state);
            }

            $processed++;
        }

        return $processed;
    }

    /** @return Collection<int, int> */
    private function dueEffectPlayerIds(CarbonInterface $now): Collection
    {
        $damageTypes = collect(ActiveEffectType::cases())
            ->filter(static fn (ActiveEffectType $type): bool => $type->isDoT())
            ->map(static fn (ActiveEffectType $type): string => $type->value)
            ->all();

        return PlayerActiveEffect::query()
            ->whereIn('type', $damageTypes)
            ->where(function ($query): void {
                $query->whereNull('battle_id')
                    ->orWhereHas('battle', fn ($battleQuery) => $battleQuery
                        ->where('status', BattleStatus::ACTIVE));
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('next_tick_at')
                    ->orWhere('next_tick_at', '<=', $now)
                    ->orWhere('expires_at', '<=', $now);
            })
            ->distinct()
            ->limit(2000)
            ->pluck('player_id')
            ->map(static fn (mixed $playerId): int => (int) $playerId);
    }

    /** @return Collection<int, int> */
    private function dueRegenerationPlayerIds(CarbonInterface $now): Collection
    {
        return Player::query()
            ->where('hp_now', '>', 0)
            ->where(function ($query) use ($now): void {
                $query->whereColumn('hp_now', '<', 'hp_max')
                    ->orWhereColumn('mp_now', '<', 'mp_max')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('last_online_at', '>=', $now->copy()->subMinutes(10)));
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('last_regen_at')
                    ->orWhere('last_regen_at', '<=', $now->copy()->subSeconds(Player::REGEN_INTERVAL));
            })
            ->limit(2000)
            ->pluck('id')
            ->map(static fn (mixed $playerId): int => (int) $playerId);
    }
}
