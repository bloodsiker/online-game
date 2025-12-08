<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|min:3|max:50|unique:users,name',
            'email'    => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'race'     => 'required|exists:races,id',
            'sex'      => 'required|in:male,female',
            'license'  => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Введите nickname.',
            'name.unique'       => 'Такой nickname уже занято.',
            'email.required'    => 'Введите email.',
            'email.email'       => 'Введите корректный email.',
            'email.unique'      => 'Этот email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min'      => 'Пароль должен быть не менее :min символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'race.required'     => 'Выберите расу.',
            'race.in'           => 'Выберите допустимую расу.',
            'sex.required'      => 'Выберите пол.',
            'sex.in'            => 'Выберите корректный пол.',
            'license.accepted'  => 'Вы должны принять лицензионное соглашение.',
        ];
    }
}
