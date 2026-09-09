<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\Requests\Admin;

use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInjuryTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'delete_image' => $this->boolean('delete_image'),
        ]);
    }

    public function rules(): array
    {
        $injuryType = $this->route('injuryType');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/',
                Rule::unique('injury_types', 'slug')->ignore($injuryType),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'body_part' => ['required', Rule::enum(InjuryBodyPart::class)],
            'severity' => ['required', Rule::enum(InjurySeverity::class)],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:43200'],
            'drop_weight' => ['required', 'integer', 'min:0', 'max:100000'],
            'stat_modifiers' => ['nullable', 'array', 'max:20'],
            'stat_modifiers.*.stat' => ['required', Rule::enum(PlayerStatKey::class)],
            'stat_modifiers.*.value' => ['required', 'numeric', 'between:-100000,100000'],
            'stat_modifiers.*.is_percent' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'delete_image' => ['required', 'boolean'],
        ];
    }
}
