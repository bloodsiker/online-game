<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\Services;

use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AssemblyCatalog
{
    /** @return array{assemblies: array, items: Collection} */
    public function execute(User $user, AssemblyType $type): array
    {
        if (! Schema::hasTable('item_assemblies')) {
            return ['assemblies' => [], 'items' => collect()];
        }
        $models = ItemAssembly::query()->where('is_active', true)->where('type', $type->value)
            ->with(['components', 'rewards'])->orderBy('sort_order')->orderBy('id')->get();
        $componentIds = $models->flatMap(fn ($assembly) => $assembly->components->modelKeys())->unique();
        $counts = DB::table('backpacks')->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', $user->id)->where('backpacks.equipped', 0)
            ->where(fn ($query) => $query->whereNull('items.expires_at')->orWhere('items.expires_at', '>', now()))
            ->whereIn('items.share_item_id', $componentIds)->groupBy('items.share_item_id')
            ->selectRaw('items.share_item_id, SUM(backpacks.count) as total')->pluck('total', 'items.share_item_id');
        $assemblies = $models->map(function (ItemAssembly $assembly) use ($counts): array {
            $components = $assembly->components->map(function ($item) use ($counts): array {
                $available = (int) ($counts[$item->id] ?? 0);

                return ['id' => $item->id, 'name' => $item->name, 'image' => $item->image,
                    'requiredCount' => (int) $item->pivot->count, 'availableCount' => $available,
                    'active' => $available >= $item->pivot->count];
            })->all();

            return [
                'id' => $assembly->id, 'name' => $assembly->name, 'type' => $assembly->type->value,
                'resultMode' => $assembly->result_mode->value,
                'rarityColor' => $assembly->rewards->first()?->rarity?->color() ?? '#666',
                'ingredients' => $components,
                'resultPool' => $assembly->rewards->map(fn ($item): array => [
                    'id' => $item->id, 'name' => $item->name, 'image' => $item->image,
                    'rarityColor' => $item->rarity?->color() ?? '#666', 'count' => (int) $item->pivot->count,
                ])->all(),
                'canCraft' => count($components) > 0 && collect($components)->every(fn ($part) => $part['active'])
                    && $assembly->rewards->isNotEmpty()
                    && ($assembly->result_mode !== AssemblyResultMode::FIXED || $assembly->rewards->count() === 1)
                    && $assembly->rewards->every(fn ($item) => $item->is_active && $item->pivot->weight > 0 && $item->pivot->count > 0),
            ];
        })->all();

        return ['assemblies' => $assemblies, 'items' => $models->flatMap(fn ($assembly) => $assembly->components->concat($assembly->rewards))->unique('id')->values()];
    }
}
