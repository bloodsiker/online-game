<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

/**
 * Карты, на которых можно скрыть других игроков (настройка hidden_players_maps).
 * Только карты с собственным видом maps.{folder}.frame — подземелья рисуются отдельно и игроков не показывают.
 */
final class PlayerVisibilityMaps
{
    /** @return array<int, string> id карты => название */
    public function options(): array
    {
        if (! Schema::hasTable('maps')) {
            return [];
        }

        return Map::query()->whereNotNull('folder')->where('folder', '!=', 'dungeon')->orderBy('id')->get()
            ->filter(fn (Map $map): bool => View::exists(sprintf('maps.%s.frame', str_replace('/', '.', (string) $map->folder))))
            ->mapWithKeys(fn (Map $map): array => [(int) $map->id => (string) $map->name])
            ->all();
    }

    /** Карта, которую сейчас рисует фрейм /on-map: по слагу ?s= или по локации игрока (как GetOnMapPage). */
    public function currentMapId(?string $slug, ?int $locationMapId): ?int
    {
        if ($slug !== null && $slug !== '') {
            $id = Map::query()->where('slug', $slug)->value('id');

            return $id !== null ? (int) $id : null;
        }

        return $locationMapId;
    }
}
