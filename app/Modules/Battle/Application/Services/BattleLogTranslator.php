<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services;

use Illuminate\Database\Eloquent\Model;

final class BattleLogTranslator
{
    /** Snapshot all name translations without adding database queries when a log is viewed. */
    public static function contentName(Model $model): string
    {
        $names = array_map(
            static fn ($name) => htmlspecialchars((string) $name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $model->getTranslations('name'),
        );
        $payload = base64_encode(json_encode($names, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return '<!--battle-name:'.$payload.'-->'.($names['ru'] ?? '').'<!--/battle-name-->';
    }

    /** Snapshot a translated content field in the existing battle log without adding read queries. */
    public static function contentText(Model $model, string $field): string
    {
        $translations = array_map(
            static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $model->getTranslations($field),
        );
        $payload = base64_encode(json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return '<!--battle-content:'.$payload.'-->'.($translations['ru'] ?? '').'<!--/battle-content-->';
    }

    /** Store a Russian fallback and language-neutral parameters in the existing HTML log. */
    public static function format(string $template, mixed ...$parameters): string
    {
        $payload = base64_encode(json_encode(
            ['template' => $template, 'parameters' => $parameters],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ));
        $id = substr(hash('sha256', $payload), 0, 16);
        $fallback = sprintf(__($template, [], 'ru'), ...$parameters);

        return '<!--battle-i18n:'.$id.':'.$payload.'-->'.$fallback.'<!--/battle-i18n:'.$id.'-->';
    }

    /** Existing unmarked records remain untouched; new records render for their viewer. */
    public static function render(?string $html): string
    {
        $html = preg_replace_callback('/<!--battle-name:([A-Za-z0-9+\/=]+)-->(.*?)<!--\/battle-name-->/s', static function (array $match): string {
            $names = json_decode(base64_decode($match[1], true) ?: '', true);
            if (! is_array($names)) {
                return $match[2];
            }

            return (string) ($names[app()->getLocale()] ?? $names['ru'] ?? $match[2]);
        }, $html ?? '') ?? ($html ?? '');

        $html = preg_replace_callback('/<!--battle-content:([A-Za-z0-9+\/=]+)-->(.*?)<!--\/battle-content-->/s', static function (array $match): string {
            $translations = json_decode(base64_decode($match[1], true) ?: '', true);
            if (! is_array($translations)) {
                return $match[2];
            }

            return (string) ($translations[app()->getLocale()] ?? $translations['ru'] ?? $match[2]);
        }, $html) ?? $html;

        return preg_replace_callback(
            '/<!--battle-i18n:([a-f0-9]{16}):([A-Za-z0-9+\/=]+)-->(.*?)<!--\/battle-i18n:\1-->/s',
            static function (array $match): string {
                $data = json_decode(base64_decode($match[2], true) ?: '', true);
                if (! is_array($data) || ! is_string($data['template'] ?? null) || ! is_array($data['parameters'] ?? null)) {
                    return $match[3];
                }

                $parameters = array_map(
                    static fn ($value) => is_string($value) ? self::render($value) : $value,
                    $data['parameters'],
                );

                return sprintf(__($data['template']), ...$parameters);
            },
            $html ?? '',
        ) ?? ($html ?? '');
    }
}
