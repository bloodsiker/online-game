<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateClanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', 'unique:clans,name'],
            'logo' => ['required', 'file', 'mimes:gif', 'max:10', 'dimensions:width=13,height=13'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название клана.',
            'name.max' => 'Название клана не может превышать 30 символов.',
            'name.unique' => 'Клан с таким названием уже существует.',
            'logo.required' => 'Загрузите значок клана.',
            'logo.mimes' => 'Значок должен быть в формате .gif.',
            'logo.max' => 'Размер значка не должен превышать 2 КБ.',
            'logo.dimensions' => 'Разрешение значка должно быть 13x13 пикселей.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateClanName($validator);
            $this->validateLogoTransparency($validator);
        });
    }

    private function validateClanName(Validator $validator): void
    {
        // Пустое поле глобальный middleware ConvertEmptyStringsToNull делает null,
        // поэтому приводим к строке, чтобы preg_match не падал с TypeError.
        $name = (string) ($this->input('name') ?? '');

        if ($name === '') {
            return; // ошибку об обязательности добавит правило name.required
        }

        $hasCyrillic = (bool) preg_match('/[а-яёА-ЯЁ]/u', $name);
        $hasLatin = (bool) preg_match('/[a-zA-Z]/', $name);

        if ($hasCyrillic && $hasLatin) {
            $validator->errors()->add(
                'name',
                'Название не может содержать одновременно русские и английские буквы.'
            );
        }
    }

    private function validateLogoTransparency(Validator $validator): void
    {
        $file = $this->file('logo');

        if ($file === null || ! $file->isValid()) {
            return;
        }

        $image = @imagecreatefromgif($file->getRealPath());

        if ($image === false) {
            $validator->errors()->add('logo', 'Не удалось прочитать файл значка.');

            return;
        }

        $hasTransparency = imagecolortransparent($image) >= 0;
        imagedestroy($image);

        if (! $hasTransparency) {
            $validator->errors()->add('logo', 'Значок должен иметь прозрачный фон.');
        }
    }
}
