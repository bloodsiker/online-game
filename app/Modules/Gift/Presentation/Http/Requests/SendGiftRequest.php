<?php

declare(strict_types=1);

namespace App\Modules\Gift\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'backpack_id' => ['required', 'integer', 'min:1'],
            'recipient' => ['required', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:150'],
            'anonymous' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['recipient' => __('gift.recipient'), 'message' => __('gift.message')];
    }
}
