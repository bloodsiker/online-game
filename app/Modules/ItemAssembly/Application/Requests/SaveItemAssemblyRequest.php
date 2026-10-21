<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\Requests;

use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
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
}
