<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password', function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && strlen($value) > 72) {
                    $fail(__('ui.password_bytes'));
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('ui.current_password_required'),
            'current_password.current_password' => __('ui.current_password_invalid'),
            'password.required' => __('ui.password_required'),
            'password.min' => __('ui.password_min'),
            'password.max' => __('ui.password_max'),
            'password.confirmed' => __('ui.password_confirmed'),
            'password.different' => __('ui.password_different'),
        ];
    }
}
