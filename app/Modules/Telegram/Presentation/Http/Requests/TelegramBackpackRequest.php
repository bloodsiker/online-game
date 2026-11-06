<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http\Requests;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TelegramBackpackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['category' => ['sometimes', Rule::enum(BackpackCategory::class)],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']];
    }

    public function category(): BackpackCategory
    {
        return BackpackCategory::from($this->validated('category', 'main'));
    }

    public function pageNumber(): int
    {
        return (int) $this->validated('page', 1);
    }

    public function pageSize(): int
    {
        return (int) $this->validated('per_page', 8);
    }
}
