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
                    $fail('Новый пароль должен занимать не более 72 байт.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.required' => 'Введите новый пароль.',
            'password.min' => 'Новый пароль должен содержать минимум 8 символов.',
            'password.max' => 'Новый пароль должен содержать не более 72 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password.different' => 'Новый пароль должен отличаться от текущего.',
        ];
    }
}
