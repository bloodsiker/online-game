<?php

declare(strict_types=1);

namespace App\Modules\Share\Application\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class ContentTranslationEditor
{
    /** @param list<string> $fields
     * @param  list<string>  $required
     */
    public function fill(Model $model, Request $request, array $fields = ['name', 'description'], array $required = ['name']): void
    {
        $rules = [
            'translations' => ['sometimes', 'array:uk'],
            'translations.uk' => ['sometimes', 'array:'.implode(',', $fields)],
        ];
        foreach ($fields as $field) {
            $rules[$field] = [in_array($field, $required, true) ? 'required' : 'nullable', 'string'];
            $rules['translations.uk.'.$field] = ['nullable', 'string'];
            if (in_array($field, ['name', 'title', 'reply_text', 'button_text'], true) || str_ends_with($field, '_name')) {
                $rules[$field][] = 'max:255';
                $rules['translations.uk.'.$field][] = 'max:255';
                if (! in_array($field, $required, true)) {
                    $rules[$field][] = 'required_with:translations.uk.'.$field;
                }
            }
        }
        $request->validate($rules);
        foreach ($fields as $field) {
            $model->setTranslation($field, 'ru', $request->input($field) ?? '');
            // Removing an optional medal name removes the medal in every language.
            if (str_ends_with($field, '_name') && ! in_array($field, $required, true)
                && trim((string) $request->input($field)) === '' && ! $request->has('translations.uk.'.$field)) {
                $model->forgetTranslation($field, 'uk');
            }
            if ($request->has('translations.uk.'.$field)) {
                $value = $request->input('translations.uk.'.$field);
                if ($value === null || trim($value) === '') {
                    $model->forgetTranslation($field, 'uk');
                } else {
                    $model->setTranslation($field, 'uk', $value);
                }
            }
        }
    }
}
