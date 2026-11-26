<?php

declare(strict_types=1);

namespace App\Modules\Weather\Application\Services;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\Weather\Domain\Enums\WeatherRules;
use App\Modules\Weather\Domain\Enums\WeatherType;
use App\Modules\Weather\Infrastructure\Persistence\Models\MapWeather;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Погода по картам: смена по расписанию (tick) и эффекты для игроков.
 * Эффекты применяются в момент действия игрока (вход на локацию, удар в бою), без рассылки всем.
 * Города и подземелья без погоды: для них нет весов в map_weather_weights / у локации есть dungeon_id.
 */
final class WeatherService
{
    /** @var array<int, MapWeather|null> */
    private array $byMap = [];

    /** @var array<int, int|null> */
    private array $mapByLocation = [];

    public function currentForMap(int $mapId): ?MapWeather
    {
        if (! array_key_exists($mapId, $this->byMap)) {
            try {
                $row = MapWeather::query()->where('map_id', $mapId)->first();
            } catch (QueryException) {
                // Таблицы погоды ещё нет (до миграции или в тестовой схеме) — погода просто не действует.
                $row = null;
            }
            $this->byMap[$mapId] = $row !== null && $row->isActive() ? $row : null;
        }

        return $this->byMap[$mapId];
    }

    public function currentForLocation(int $locationId): ?MapWeather
    {
        $mapId = $this->mapIdForLocation($locationId);

        return $mapId === null ? null : $this->currentForMap($mapId);
    }

    public function typeForUser(int $userId): ?WeatherType
    {
        $locationId = (int) DB::table('users')->where('id', $userId)->value('location_id');

        return $locationId > 0 ? $this->currentForLocation($locationId)?->weather : null;
    }

    /** Туман: множитель «точности» ударов игрока (противник уворачивается чаще), 1.0 — без влияния. Крит и уворот самого игрока снижает дебафф «Туман». */
    public function combatChanceFactor(int $userId): float
    {
        return $this->typeForUser($userId) === WeatherType::FOG
            ? (100 - WeatherRules::FOG_PERCENT) / 100
            : 1.0;
    }

    /** Эффекты при входе игрока на локацию: гроза — шанс «Простуды», радуга — бафф. */
    /**
     * Открытие страницы локации: те же эффекты, что и при входе, чтобы их получали и те, кто стоял на месте,
     * когда началась погода. Бросок «Простуды» — один раз за грозу (иначе каждое обновление страницы давало бы шанс).
     */
    public function applyOnView(int $userId): void
    {
        $this->applyOnArrival($userId, onView: true);
    }

    public function applyOnArrival(int $userId, bool $onView = false): void
    {
        $player = Player::query()->where('user_id', $userId)->where('is_main', true)->first()
            ?? Player::query()->where('user_id', $userId)->first();
        if ($player === null) {
            return;
        }

        $locationId = (int) DB::table('users')->where('id', $userId)->value('location_id');
        $weather = $locationId > 0 ? $this->currentForLocation($locationId) : null;
        $type = $weather?->weather;

        // «Туман» действует, только пока игрок на карте с туманом.
        if ($type !== WeatherType::FOG) {
            $this->removeEffect($player, WeatherRules::FOG_EFFECT_SLUG);
        }

        match ($type) {
            WeatherType::STORM => (! $onView || $this->firstStormRoll($userId, $weather))
                && random_int(1, 100) <= WeatherRules::COLD_CHANCE
                ? $this->applyEffect($player, WeatherRules::COLD_EFFECT_SLUG, now()->addMinutes(WeatherRules::EFFECT_MINUTES))
                : null,
            WeatherType::RAINBOW => $this->applyEffect($player, WeatherRules::RAINBOW_EFFECT_SLUG, now()->addMinutes(WeatherRules::EFFECT_MINUTES)),
            WeatherType::FOG => $this->applyEffect($player, WeatherRules::FOG_EFFECT_SLUG, $weather->ends_at, refresh: true),
            default => null,
        };
    }

    /** Множитель скорости регенерации HP/MP игрока (зной), 1.0 — обычная. */
    public function regenSpeed(int $userId): float
    {
        return $this->typeForUser($userId) === WeatherType::HEAT ? WeatherRules::HEAT_REGEN_SPEED : 1.0;
    }

    /** Длительность перехода с текущей локации, мс; null — обычная (без погодного влияния). */
    public function moveCooldownMs(int $locationId): ?int
    {
        return match ($this->currentForLocation($locationId)?->weather) {
            WeatherType::BLIZZARD => WeatherRules::BLIZZARD_MOVE_MS,
            WeatherType::TAILWIND => WeatherRules::TAILWIND_MOVE_MS,
            default => null,
        };
    }

    /**
     * Ураган: после успешного перехода с шансом уносит игрока на случайную соседнюю локацию той же карты
     * (без закрытых квестом локаций, данжей и проходов с воротами). Возвращает сообщение для игрока или null.
     */
    public function hurricaneKnockback(User $user): ?string
    {
        $fromId = (int) $user->location_id;
        if ($this->currentForLocation($fromId)?->weather !== WeatherType::HURRICANE
            || random_int(1, 100) > WeatherRules::HURRICANE_CHANCE) {
            return null;
        }

        $from = DB::table('locations')->where('id', $fromId)->first(['map_id', 'north', 'south', 'east', 'west']);
        if ($from === null) {
            return null;
        }
        $candidateIds = array_values(array_filter(
            [(int) $from->north, (int) $from->south, (int) $from->east, (int) $from->west],
            static fn (int $id): bool => $id > 0 && $id !== (int) $user->prev_location_id,
        ));
        if ($candidateIds === []) {
            return null;
        }
        $gated = DB::table('dungeon_gates')->where('from_location_id', $fromId)->pluck('to_location_id')
            ->merge(DB::table('location_gates')->where('from_location_id', $fromId)->pluck('to_location_id'))
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $targets = DB::table('locations')
            ->whereIn('id', $candidateIds)
            ->where('map_id', $from->map_id)
            ->whereNull('dungeon_id')
            ->where('is_locked', false)
            ->whereNotIn('id', $gated)
            ->pluck('id')
            ->all();
        if ($targets === []) {
            return null;
        }
        $targetId = (int) $targets[array_rand($targets)];

        $moved = DB::transaction(function () use ($user, $fromId, $targetId): bool {
            $fresh = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if ($fresh === null || (int) $fresh->location_id !== $fromId) {
                return false;
            }
            $fresh->prev_location_id = $fromId;
            $fresh->location_id = $targetId;
            $fresh->save();

            return true;
        });
        if (! $moved) {
            return null;
        }

        $user->prev_location_id = $fromId;
        $user->location_id = $targetId;
        Cache::forget('who:users_on_location:'.$fromId);
        Cache::forget('who:users_on_location:'.$targetId);

        return __('Порыв урагана сбил вас с пути и унёс на соседнюю локацию!');
    }

    /** Смена погоды на картах, где время текущей фазы истекло. Возвращает число изменённых карт. */
    public function tick(): int
    {
        $weights = DB::table('map_weather_weights')
            ->whereIn('weather', WeatherRules::ACTIVE_TYPES)
            ->where('weight', '>', 0)
            ->get()
            ->groupBy('map_id');

        $changed = 0;
        foreach ($weights as $mapId => $rows) {
            $changed += DB::transaction(function () use ($mapId, $rows): int {
                $row = MapWeather::query()->where('map_id', $mapId)->lockForUpdate()->first();
                if ($row !== null && $row->ends_at->isFuture()) {
                    return 0;
                }

                $row ??= new MapWeather(['map_id' => (int) $mapId]);
                $finished = $row->weather;
                [$next, $minutes] = match (true) {
                    $finished === WeatherType::STORM && random_int(1, 100) <= WeatherRules::RAINBOW_AFTER_STORM_CHANCE => [WeatherType::RAINBOW, WeatherRules::RAINBOW_MINUTES],
                    $finished !== null => [null, random_int(WeatherRules::CLEAR_MIN_MINUTES, WeatherRules::CLEAR_MAX_MINUTES)],
                    default => [$this->pickWeighted($rows), random_int(WeatherRules::WEATHER_MIN_MINUTES, WeatherRules::WEATHER_MAX_MINUTES)],
                };

                $row->fill([
                    'previous_weather' => $finished,
                    'weather' => $next,
                    'started_at' => now(),
                    'ends_at' => now()->addMinutes($minutes),
                ])->save();
                unset($this->byMap[(int) $mapId]);

                return 1;
            });
        }

        return $changed;
    }

    /** Включить явление на карте вручную (админка/тесты). */
    public function force(int $mapId, ?WeatherType $type, int $minutes): void
    {
        $row = MapWeather::query()->firstOrNew(['map_id' => $mapId]);
        $row->fill([
            'previous_weather' => $row->weather,
            'weather' => $type,
            'started_at' => now(),
            'ends_at' => now()->addMinutes(max(1, $minutes)),
        ])->save();
        unset($this->byMap[$mapId]);
    }

    private function applyEffect(Player $player, string $slug, CarbonInterface $expiresAt, bool $refresh = false): void
    {
        $effect = Effect::query()->where('slug', $slug)->first();
        if ($effect === null) {
            return;
        }

        $active = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('effect_id', $effect->id)
            ->whereNull('battle_id')
            ->first();
        // Уже действует — не продлеваем: иначе каждый переход в грозу обновлял бы «Простуду».
        if (! $refresh && $active !== null && ($active->expires_at === null || $active->expires_at->isFuture())) {
            return;
        }

        ($active ?? new PlayerActiveEffect)->fill([
            'player_id' => $player->id, 'effect_id' => $effect->id, 'battle_id' => null, 'type' => null,
            'source_player_id' => null, 'source_magic_skill_id' => null,
            'applied_at' => now(), 'last_tick_at' => now(), 'next_tick_at' => null,
            'expires_at' => $expiresAt,
            'stacks' => 0, 'current_value' => null, 'tick_remainder' => 0,
        ])->save();

        app(PlayerStatService::class)->invalidate($player);
        app(PlayerEffectStateBroadcaster::class)->broadcast($player);
    }

    /** true — бросок «Простуды» для этой грозы при просмотре локации ещё не делался. */
    private function firstStormRoll(int $userId, MapWeather $weather): bool
    {
        $key = 'weather:storm-roll:'.$userId.':'.$weather->map_id.':'.$weather->started_at?->timestamp;

        return Cache::add($key, 1, $weather->ends_at);
    }

    private function removeEffect(Player $player, string $slug): void
    {
        $effectId = Effect::query()->where('slug', $slug)->value('id');
        if ($effectId === null) {
            return;
        }

        $deleted = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('effect_id', $effectId)
            ->whereNull('battle_id')
            ->delete();
        if ($deleted > 0) {
            app(PlayerStatService::class)->invalidate($player);
            app(PlayerEffectStateBroadcaster::class)->broadcast($player);
        }
    }

    private function mapIdForLocation(int $locationId): ?int
    {
        if (! array_key_exists($locationId, $this->mapByLocation)) {
            $location = DB::table('locations')->where('id', $locationId)->first(['map_id', 'dungeon_id']);
            $this->mapByLocation[$locationId] = $location === null || $location->dungeon_id !== null || $location->map_id === null
                ? null
                : (int) $location->map_id;
        }

        return $this->mapByLocation[$locationId];
    }

    /** @param  Collection<int, object>  $rows */
    private function pickWeighted($rows): WeatherType
    {
        $total = (int) $rows->sum('weight');
        $roll = random_int(1, max(1, $total));
        foreach ($rows as $row) {
            $roll -= (int) $row->weight;
            if ($roll <= 0) {
                return WeatherType::from((string) $row->weather);
            }
        }

        return WeatherType::from((string) $rows->last()->weather);
    }
}
