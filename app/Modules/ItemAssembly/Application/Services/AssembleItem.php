<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\ItemAssembly\Application\DTOs\AssemblyResult;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

final class AssembleItem
{
    public function __construct(private readonly BackpackService $backpack) {}

    public function execute(User $user, int $assemblyId, ?int $blacksmithId = null): AssemblyResult
    {
        return DB::transaction(function () use ($user, $assemblyId, $blacksmithId): AssemblyResult {
            $owner = User::query()->setEagerLoads([])->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $assembly = ItemAssembly::query()->whereKey($assemblyId)->lockForUpdate()->firstOrFail();
            if ($blacksmithId !== null) {
                $structure = Structure::query()->setEagerLoads([])->findOrFail($blacksmithId);
                abort_unless($structure->type === Structure::TYPE_BLACKSMITH && $assembly->type === AssemblyType::ARTIFACT, 404);
                abort_unless((int) $structure->location_id === (int) $owner->location_id, 403);
            }
            $assembly->load(['components', 'rewards']);
            if (! $assembly->is_active || $assembly->components->isEmpty() || $assembly->rewards->isEmpty()
                || ($assembly->result_mode === AssemblyResultMode::FIXED && $assembly->rewards->count() !== 1)
                || $assembly->components->contains(fn ($item) => $item->pivot->count < 1)
                || $assembly->rewards->contains(fn ($item) => ! $item->is_active || $item->pivot->weight < 1 || $item->pivot->count < 1)) {
                return new AssemblyResult(false, __('Эта сборка недоступна.'));
            }
            $slots = Backpack::query()->select('backpacks.*', 'items.share_item_id')
                ->join('items', 'items.id', '=', 'backpacks.item_id')
                ->where('backpacks.user_id', $user->id)->where('backpacks.equipped', 0)
                ->where(fn ($query) => $query->whereNull('items.expires_at')->orWhere('items.expires_at', '>', now()))
                ->whereIn('items.share_item_id', $assembly->components->modelKeys())
                ->where('backpacks.count', '>', 0)->orderBy('backpacks.id')->lockForUpdate()->get();
            foreach ($assembly->components as $component) {
                if ($slots->where('share_item_id', $component->id)->sum('count') < $component->pivot->count) {
                    return new AssemblyResult(false, __('Не хватает компонентов для сборки.'));
                }
            }
            $reward = $assembly->rewards->first();
            if ($assembly->result_mode === AssemblyResultMode::RANDOM) {
                $roll = random_int(1, (int) $assembly->rewards->sum('pivot.weight'));
                foreach ($assembly->rewards as $candidate) {
                    $roll -= (int) $candidate->pivot->weight;
                    if ($roll <= 0) {
                        $reward = $candidate;
                        break;
                    }
                }
            }
            foreach ($assembly->components as $component) {
                $remaining = (int) $component->pivot->count;
                foreach ($slots->where('share_item_id', $component->id) as $slot) {
                    $take = min($remaining, (int) $slot->count);
                    if (! $this->backpack->removeItem($user, $slot->item_id, $take)) {
                        throw new \RuntimeException('Locked assembly component could not be consumed.');
                    }
                    $remaining -= $take;
                    if ($remaining === 0) {
                        break;
                    }
                }
            }
            $count = (int) $reward->pivot->count;
            $this->backpack->giveItemsByShareItem($user, $reward, $count);

            return new AssemblyResult(true, __('Сборка завершена. Получено: :item ×:count.', ['item' => $reward->name, 'count' => $count]), $reward->id, $count);
        });
    }
}
