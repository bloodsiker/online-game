<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:50', 'unique:users,name'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'race' => ['required', 'exists:races,id'],
            'sex' => ['required', 'in:male,female'],
            'avatar' => Schema::hasTable('race_avatars')
                ? [
                    'required',
                    'integer',
                    Rule::exists('race_avatars', 'id')
                        ->where('race_id', (int) $this->input('race'))
                        ->where('sex', (string) $this->input('sex')),
                ]
                : [
                    'required',
                    'string',
                    Rule::in(array_keys(config('registration_avatars.'.(string) $this->input('sex'), []))),
                ],
            'license' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите nickname.',
            'name.unique' => 'Такой nickname уже занято.',
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен быть не менее :min символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'race.required' => 'Выберите расу.',
            'race.in' => 'Выберите допустимую расу.',
            'sex.required' => 'Выберите пол.',
            'sex.in' => 'Выберите корректный пол.',
            'avatar.required' => 'Выберите аватар персонажа.',
            'avatar.exists' => 'Выберите аватар для выбранной расы и пола.',
            'avatar.in' => 'Выберите допустимый аватар.',
            'license.accepted' => 'Вы должны принять лицензионное соглашение.',
        ];
    }
}
