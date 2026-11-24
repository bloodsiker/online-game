<?php

declare(strict_types=1);

namespace App\Modules\Monster\Application\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Domain\Events\BossMoved;
use App\Modules\Monster\Domain\Services\MapMonstersCache;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterMovement;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class MonsterMovementService
{
    public function configure(Monster $monster, array $input): void
    {
        abort_unless($monster->is_boss, 422, 'Автоматическое перемещение доступно только для боссов.');

        $enabled = filter_var($input['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $mode = $input['mode'] ?? MonsterMovement::MODE_MAP;
        $data = Validator::make($input, [
            'enabled' => ['sometimes', 'boolean'],
            'mode' => ['required', Rule::in([MonsterMovement::MODE_MAP, MonsterMovement::MODE_LOCATIONS])],
            'interval_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'map_id' => [Rule::excludeIf($mode !== MonsterMovement::MODE_MAP), Rule::requiredIf($enabled), 'nullable', 'integer', 'exists:maps,id'],
            'location_ids' => [Rule::excludeIf($mode !== MonsterMovement::MODE_LOCATIONS), Rule::requiredIf($enabled), 'nullable', 'array', 'min:2', 'max:500'],
            'location_ids.*' => ['integer', 'distinct', Rule::exists('locations', 'id')->whereNull('dungeon_id')],
        ], [
            'location_ids.min' => 'Выберите минимум две локации для перемещения.',
            'location_ids.*.exists' => 'Выбранная локация не существует или находится в данже.',
        ])->validate();

        DB::transaction(function () use ($monster, $data, $enabled, $mode): void {
            Monster::query()->whereKey($monster->id)->lockForUpdate()->firstOrFail();
            MonsterMovement::query()->updateOrCreate(['monster_id' => $monster->id], [
                'enabled' => $enabled,
                'mode' => $mode,
                'interval_minutes' => (int) $data['interval_minutes'],
                'map_id' => isset($data['map_id']) ? (int) $data['map_id'] : null,
                'location_ids' => array_map('intval', $data['location_ids'] ?? []),
                'next_move_at' => $enabled ? now()->addMinutes((int) $data['interval_minutes']) : null,
            ]);
        }, attempts: 3);
    }

    public function moveDue(CarbonInterface $now): int
    {
        $moved = 0;
        $now = CarbonImmutable::instance($now);

        MonsterMovement::query()
            ->where('enabled', true)
            ->where('next_move_at', '<=', $now)
            ->whereHas('monster', fn ($query) => $query->where('is_boss', true))
            ->chunkById(100, function ($settings) use ($now, &$moved): void {
                foreach ($settings as $setting) {
                    $moved += $this->moveOne($setting, $now);
                }
            });

        if ($moved > 0) {
            MapMonstersCache::flush();
        }

        return $moved;
    }

    private function moveOne(MonsterMovement $setting, CarbonImmutable $now): int
    {
        return DB::transaction(function () use ($setting, $now): int {
            $movement = MonsterMovement::query()->whereKey($setting->id)->lockForUpdate()->first();
            $monster = Monster::query()->whereKey($setting->monster_id)->lockForUpdate()->first();

            if (! $movement?->enabled || ! $monster?->is_boss
                || $movement->next_move_at === null || $movement->next_move_at->gt($now)) {
                return 0;
            }

            $spawns = MonsterOnLocation::query()
                ->where('monster_id', $monster->id)
                ->where('active', true)
                ->where('hp_now', '>', 0)
                ->whereNull('dungeon_session_id')
                ->whereNull('world_event_run_id')
                ->whereNull('clan_boss_stage_state_id')
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('battle_details')
                        ->join('battles', 'battles.id', '=', 'battle_details.battle_id')
                        ->whereColumn('battle_details.location_monster_id', 'monster_on_locations.id')
                        ->where('battles.status', 1);
                })
                ->lockForUpdate()
                ->get();

            $moved = 0;
            foreach ($spawns as $spawn) {
                $currentLocation = Location::query()->find($spawn->location_id);
                if ($currentLocation === null) {
                    continue;
                }

                $neighbourIds = array_values(array_unique(array_filter([
                    $currentLocation->north,
                    $currentLocation->south,
                    $currentLocation->east,
                    $currentLocation->west,
                    $currentLocation->up,
                    $currentLocation->down,
                ])));

                $destinations = Location::query()
                    ->whereIn('id', $neighbourIds)
                    ->whereNull('dungeon_id')
                    ->where('is_locked', false);

                if ($movement->mode === MonsterMovement::MODE_MAP && $movement->map_id !== null) {
                    $destinations->where('map_id', $movement->map_id);
                } elseif ($movement->mode === MonsterMovement::MODE_LOCATIONS) {
                    $destinations->whereIn('id', $movement->location_ids ?? []);
                } else {
                    $destinations->whereRaw('1 = 0');
                }

                $destinationId = $destinations->inRandomOrder()->value('id');
                if ($destinationId === null) {
                    continue;
                }

                $spawn->location_id = $destinationId;
                $spawn->save();
                $moved++;

                $this->announce($monster, $currentLocation, Location::query()->find($destinationId));
            }

            $movement->next_move_at = $now->addMinutes(max(1, $movement->interval_minutes));
            $movement->save();

            return $moved;
        }, attempts: 3);
    }

    /** Оповещает только игроков на локации, которую босс покинул, и на локации, куда он пришёл. */
    private function announce(Monster $monster, Location $from, ?Location $to): void
    {
        if ($to === null) {
            return;
        }

        $names = $this->names($monster);

        BossMoved::dispatch($from->id, BossMoved::LEFT, $names, $this->directionTo($from, $to->id));
        BossMoved::dispatch($to->id, BossMoved::ARRIVED, $names, $this->directionTo($to, $from->id));
    }

    /** В какую сторону от $origin находится локация $targetId. */
    private function directionTo(Location $origin, int $targetId): ?string
    {
        foreach (['north', 'south', 'east', 'west', 'up', 'down'] as $side) {
            if ((int) $origin->{$side} === $targetId) {
                return $side;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    private function names(Monster $monster): array
    {
        $raw = json_decode((string) $monster->getRawOriginal('name'), true);

        return is_array($raw) ? array_map('strval', $raw) : ['ru' => (string) $monster->name];
    }
}
