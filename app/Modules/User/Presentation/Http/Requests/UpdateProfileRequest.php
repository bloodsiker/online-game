<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'real_name' => ['nullable', 'string', 'max:32'],
            'residence' => ['nullable', 'string', 'max:100'],
            'motto' => ['nullable', 'string', 'max:150'],
            'about' => ['nullable', 'string', 'max:5000'],
            'birth_day' => ['nullable', 'required_with:birth_month,birth_year', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'required_with:birth_day,birth_year', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'required_with:birth_day,birth_month', 'integer', 'between:'.(now()->year - 100).','.now()->year],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('birth_year')) {
                return;
            }

            $day = $this->integer('birth_day');
            $month = $this->integer('birth_month');
            $year = $this->integer('birth_year');
            if (! checkdate($month, $day, $year) || sprintf('%04d-%02d-%02d', $year, $month, $day) > now()->toDateString()) {
                $validator->errors()->add('birth_day', 'Укажите корректную дату рождения, не позднее сегодняшней.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['real_name' => 'Ваше имя', 'residence' => 'Место проживания', 'motto' => 'Девиз', 'about' => 'О себе', 'birth_day' => 'День рождения', 'birth_month' => 'Месяц рождения', 'birth_year' => 'Год рождения'];
    }
}
