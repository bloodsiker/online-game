<?php

declare(strict_types=1);

namespace App\Modules\Npc\Application\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcMovement;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class NpcMovementService
{
    public function configure(Npc $npc, array $input): void
    {
        $enabled = filter_var($input['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $mode = $input['mode'] ?? NpcMovement::MODE_MAP;
        $data = Validator::make($input, [
            'enabled' => ['sometimes', 'boolean'],
            'mode' => ['required', Rule::in([NpcMovement::MODE_MAP, NpcMovement::MODE_LOCATIONS])],
            'interval_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'map_id' => [Rule::excludeIf($mode !== NpcMovement::MODE_MAP), Rule::requiredIf($enabled), 'nullable', 'integer', 'exists:maps,id'],
            'location_ids' => [Rule::excludeIf($mode !== NpcMovement::MODE_LOCATIONS), Rule::requiredIf($enabled), 'nullable', 'array', 'min:2', 'max:500'],
            'location_ids.*' => ['integer', 'distinct', Rule::exists('locations', 'id')->whereNull('dungeon_id')],
        ], [
            'location_ids.min' => 'Выберите минимум две локации для перемещения.',
            'location_ids.*.exists' => 'Выбранная локация не существует или находится в данже.',
        ])->validate();

        DB::transaction(function () use ($npc, $data, $enabled, $mode): void {
            // Единый порядок блокировок с планировщиком: NPC, затем настройки.
            Npc::query()->whereKey($npc->id)->lockForUpdate()->firstOrFail();
            NpcMovement::query()->updateOrCreate(['npc_id' => $npc->id], [
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

        NpcMovement::query()
            ->where('enabled', true)
            ->where('next_move_at', '<=', $now)
            ->whereHas('npc', fn ($query) => $query->where('is_active', true))
            ->chunkById(100, function ($settings) use ($now, &$moved): void {
                foreach ($settings as $setting) {
                    $moved += (int) $this->moveOne($setting, $now);
                }
            });

        return $moved;
    }

    private function moveOne(NpcMovement $setting, CarbonImmutable $now): bool
    {
        return DB::transaction(function () use ($setting, $now): bool {
            $npc = Npc::query()->whereKey($setting->npc_id)->lockForUpdate()->first();
            $movement = NpcMovement::query()->whereKey($setting->id)->lockForUpdate()->first();

            if (! $npc?->is_active || ! $movement?->enabled
                || $movement->next_move_at === null || $movement->next_move_at->gt($now)) {
                return false;
            }

            $currentLocation = $npc->location_id !== null ? Location::query()->find($npc->location_id) : null;
            $neighbourIds = $currentLocation === null ? [] : array_values(array_unique(array_filter([
                $currentLocation->north,
                $currentLocation->south,
                $currentLocation->east,
                $currentLocation->west,
                $currentLocation->up,
                $currentLocation->down,
            ])));

            // Карта/список ограничивают соседние клетки, а не задают точки телепортации.
            $locations = Location::query()
                ->whereIn('id', $neighbourIds)
                ->whereNull('dungeon_id')
                ->where('is_locked', false);
            if ($movement->mode === NpcMovement::MODE_MAP && $movement->map_id !== null) {
                $locations->where('map_id', $movement->map_id);
            } elseif ($movement->mode === NpcMovement::MODE_LOCATIONS) {
                $locations->whereIn('id', $movement->location_ids ?? []);
            } else {
                // Удалённая карта не должна превращать маршрут во весь мир.
                $locations->whereRaw('1 = 0');
            }

            if ($npc->location_id !== null) {
                $locations->where('id', '!=', $npc->location_id);
            }

            $destination = $locations->inRandomOrder()->value('id');
            if ($destination !== null) {
                $npc->location_id = $destination;
                $npc->save();
            }

            // Не догоняем пропущенные шаги после простоя планировщика.
            $movement->next_move_at = $now->addMinutes(max(1, $movement->interval_minutes));
            $movement->save();

            return $destination !== null;
        }, attempts: 3);
    }
}
