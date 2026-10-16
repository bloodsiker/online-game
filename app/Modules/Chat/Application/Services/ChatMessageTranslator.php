<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use Illuminate\Contracts\Translation\Translator;

final readonly class ChatMessageTranslator
{
    public function __construct(private Translator $translator) {}

    public function render(LocalizedChatMessage $message, string $locale): string
    {
        $locale = in_array($locale, ['ru', 'uk'], true) ? $locale : 'ru';
        // Only application-owned chat templates may provide markup.
        if (! str_starts_with($message->key, 'chat.')) {
            return '';
        }
        $template = $this->translator->get($message->key, [], $locale, false);
        if ($template === $message->key) {
            $template = $this->translator->get($message->key, [], 'ru', false);
        }
        if (! is_string($template) || $template === $message->key) {
            return '';
        }
        $replacements = [];
        foreach ($message->payload()['params'] as $key => $value) {
            $replacements[':'.$key] = $this->parameter($value, $locale);
        }

        return strtr($template, $replacements);
    }

    private function parameter(mixed $value, string $locale): string
    {
        if (is_array($value)) {
            if (isset($value['translations']) && is_array($value['translations'])) {
                $text = $value['translations'][$locale] ?? '';
                if (! is_string($text) || $text === '') {
                    $text = $value['translations']['ru'] ?? '';
                }

                return e(is_string($text) ? $text : '');
            }
            if (isset($value['key'], $value['params'])) {
                return $this->render(new LocalizedChatMessage($value['key'], $value['params']), $locale);
            }

            return implode('', array_map(fn (mixed $part): string => $this->parameter($part, $locale), $value));
        }

        return e(is_scalar($value) ? (string) $value : '');
    }
}
