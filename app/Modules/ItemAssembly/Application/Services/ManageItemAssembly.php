<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\Services;

use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageItemAssembly
{
    public function save(ItemAssembly $assembly, array $data): ItemAssembly
    {
        return DB::transaction(function () use ($assembly, $data): ItemAssembly {
            if ($assembly->exists) {
                $assembly = ItemAssembly::query()->whereKey($assembly->id)->lockForUpdate()->firstOrFail();
            }
            $components = collect($data['components'] ?? [])->keyBy('share_item_id');
            $rewards = collect($data['rewards'] ?? [])->keyBy('share_item_id');
            $fixed = $data['result_mode'] === AssemblyResultMode::FIXED->value;
            if ($fixed && $rewards->count() > 1) {
                throw ValidationException::withMessages(['rewards' => 'Для фиксированного результата укажите только один предмет.']);
            }
            if (($data['is_active'] ?? false) && ($components->isEmpty() || $rewards->isEmpty())) {
                throw ValidationException::withMessages(['is_active' => 'Для включения сборки нужны компоненты и хотя бы одна награда.']);
            }
            $assembly->setTranslation('name', 'ru', $data['name']);
            if (filled($data['name_uk'] ?? null)) {
                $assembly->setTranslation('name', 'uk', $data['name_uk']);
            } else {
                $assembly->forgetTranslation('name', 'uk');
            }
            $assembly->fill(['type' => $data['type'], 'result_mode' => $data['result_mode'], 'is_active' => $data['is_active'] ?? false, 'sort_order' => $data['sort_order'] ?? 0])->save();
            $assembly->components()->sync($components->map(fn ($part) => ['count' => (int) $part['count']])->all());
            $assembly->rewards()->sync($rewards->map(fn ($reward) => ['count' => (int) $reward['count'], 'weight' => $fixed ? 1 : (int) ($reward['weight'] ?? 1)])->all());

            return $assembly;
        });
    }
}
