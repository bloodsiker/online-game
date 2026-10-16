<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Settings;

final class GameSettingsDefinition
{
    /** Only implemented settings belong here. Add each new key with its type, default and validation. */
    private const DEFINITIONS = [
        'language' => ['default' => 'ru', 'type' => 'language', 'rules' => ['sometimes', 'required', 'string', 'in:ru,uk']],
        'show_map' => ['default' => true, 'type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean']],
        'map_side' => ['default' => 'right', 'type' => 'map_side', 'rules' => ['sometimes', 'required', 'string', 'in:left,right']],
        'show_hotbar' => ['default' => true, 'type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean']],
        'show_item_tooltips' => ['default' => true, 'type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean']],
        'chat_sound_enabled' => ['default' => false, 'type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean']],
        'volume' => ['default' => 50, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,100']],
        'sound_mention' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_private' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_clan' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_trade' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'mute_open_chat' => ['default' => false, 'type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean']],
        'sound_battle' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_dungeon' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_mail' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
        'sound_gathering' => ['default' => 0, 'type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'between:0,6']],
    ];

    public static function defaults(): array
    {
        return array_map(fn (array $definition): mixed => $definition['default'], self::DEFINITIONS);
    }

    public static function rules(): array
    {
        return array_map(fn (array $definition): array => $definition['rules'], self::DEFINITIONS);
    }

    public static function normalize(array $values): array
    {
        $result = [];
        foreach (self::DEFINITIONS as $key => $definition) {
            if (array_key_exists($key, $values)) {
                $result[$key] = match ($definition['type']) {
                    'bool' => (bool) $values[$key],
                    'int' => self::normalizeInteger(key: $key, value: $values[$key]),
                    'language' => in_array($values[$key], ['ru', 'uk'], true) ? $values[$key] : 'ru',
                    'map_side' => in_array($values[$key], ['left', 'right'], true) ? $values[$key] : 'right',
                };
            }
        }

        return $result;
    }

    private static function normalizeInteger(string $key, mixed $value): int
    {
        // Preserve the sounds previously enabled by checkboxes in stored JSON settings.
        $legacyEventSounds = ['sound_battle' => 1, 'sound_dungeon' => 2, 'sound_mail' => 3, 'sound_gathering' => 4];
        if (is_bool($value) && isset($legacyEventSounds[$key])) {
            return $value ? $legacyEventSounds[$key] : 0;
        }

        return (int) $value;
    }
}
