<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Localization;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;

/** Compatibility with plain-text rows while content migrations are being deployed. */
trait HasLegacyTranslations
{
    use HasTranslations {
        getTranslations as private spatieTranslations;
        setAttribute as private spatieSetAttribute;
    }

    public function setAttribute($key, $value): static
    {
        // Existing importers and game services write Russian strings, regardless of the viewer's language.
        if ($this->isTranslatableAttribute($key) && ! is_array($value)) {
            $this->setTranslation($key, 'ru', $value ?? '');

            return $this;
        }

        $this->spatieSetAttribute($key, $value);

        return $this;
    }

    /** @return array<string, mixed> */
    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array
    {
        if ($key !== null && $this->isTranslatableAttribute($key)) {
            $raw = $this->getAttributes()[$key] ?? null;
            if (is_string($raw) && ! is_array(json_decode($raw, true))) {
                return $allowedLocales === null || in_array('ru', $allowedLocales, true)
                    ? ['ru' => $raw]
                    : [];
            }
        }

        return $this->spatieTranslations($key, $allowedLocales);
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        $translations = $this->getTranslations($key);
        if (array_key_exists($locale, $translations) && $translations[$locale] !== null && $translations[$locale] !== '') {
            return $translations[$locale];
        }

        return $useFallbackLocale ? ($translations['ru'] ?? '') : '';
    }

    /** @param list<string> $attributes */
    public function scopeSearchTranslations(Builder $query, string $pattern, array $attributes = ['name']): Builder
    {
        foreach ($attributes as $attribute) {
            $this->guardAgainstNonTranslatableAttribute($attribute);
        }

        return $query->where(function (Builder $names) use ($pattern, $attributes): void {
            $grammar = $names->getQuery()->getGrammar();
            foreach ($attributes as $attribute) {
                foreach (['ru', 'uk'] as $locale) {
                    $column = $grammar->wrap($names->getModel()->qualifyColumn($attribute).'->'.$locale);
                    $names->orWhereRaw('LOWER('.$column.') LIKE ?', [mb_strtolower($pattern)]);
                }
            }
        });
    }
}
