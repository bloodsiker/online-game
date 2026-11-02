<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\Requests;

use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveItemAssemblyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The existing admin middleware protects these routes.
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'name_uk' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AssemblyType::class)],
            'result_mode' => ['required', Rule::enum(AssemblyResultMode::class)],
            'is_active' => ['boolean'], 'sort_order' => ['nullable', 'integer', 'between:0,1000000'],
            'components' => ['sometimes', 'array', 'max:50'],
            'components.*.share_item_id' => ['required', 'integer', 'distinct', 'exists:share_items,id'],
            'components.*.count' => ['required', 'integer', 'between:1,1000000'],
            'rewards' => ['sometimes', 'array', 'max:100'],
            'rewards.*.share_item_id' => ['required', 'integer', 'distinct', 'exists:share_items,id'],
            'rewards.*.count' => ['required', 'integer', 'between:1,1000'],
            'rewards.*.weight' => ['nullable', 'integer', 'between:1,1000000'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($validator->errors()->isNotEmpty() || $this->input('type') !== AssemblyType::TREASURE_MAP->value) {
                return;
            }
            $components = collect($this->input('components', []));
            $rewards = collect($this->input('rewards', []));
            $items = ShareItem::whereIn('id', $components->concat($rewards)->pluck('share_item_id'))->get()->keyBy('id');
            $map = $items->get($rewards->first()['share_item_id'] ?? 0);
            if ($this->input('result_mode') !== 'fixed' || $components->count() !== 4 || $rewards->count() !== 1
                || (int) ($rewards->first()['count'] ?? 0) !== 1 || $map?->type?->value !== 'treasure_map'
                || $components->contains(fn ($part) => (int) $part['count'] !== 1
                    || $items->get($part['share_item_id'])?->type?->value !== 'treasure_map_fragment'
                    || $items->get($part['share_item_id'])?->rarity !== $map?->rarity)) {
                $validator->errors()->add('components', 'Для карты нужны четыре разных фрагмента одного тира по 1 шт. и один фиксированный результат — карта того же тира, 1 шт.');
            }
        }];
    }
}
