<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Settings;

/**
 * Действия игры, на которые игрок может назначить свою клавишу.
 * Клавиши хранятся как KeyboardEvent.code (физическая клавиша, не зависит от раскладки).
 */
final class HotkeyActions
{
    /** Действие => клавиша по умолчанию. */
    private const DEFAULTS = [
        'move_north' => 'ArrowUp',
        'move_south' => 'ArrowDown',
        'move_west' => 'ArrowLeft',
        'move_east' => 'ArrowRight',
        'move_up' => 'PageUp',
        'move_down' => 'PageDown',
        'take_item' => 'KeyF',
        'weapon_attack' => 'KeyQ',
        'attack' => 'Space',
        'backpack' => 'KeyI',
        'magic_skill' => 'KeyB',
        'character' => 'KeyC',
        'map' => 'KeyM',
        'hotbar_1' => 'Digit1',
        'hotbar_2' => 'Digit2',
        'hotbar_3' => 'Digit3',
        'hotbar_4' => 'Digit4',
        'hotbar_5' => 'Digit5',
        'hotbar_6' => 'Digit6',
        'hotbar_7' => 'Digit7',
        'hotbar_8' => 'Digit8',
        'hotbar_9' => 'Digit9',
        'hotbar_10' => 'Digit0',
    ];

    /** Клавиши, которые назначать нельзя: служебные для форм, окон и самой настройки. */
    private const FORBIDDEN = [
        'Escape', 'Tab', 'Enter', 'NumpadEnter', 'Backspace', 'Delete',
        'ShiftLeft', 'ShiftRight', 'ControlLeft', 'ControlRight', 'AltLeft', 'AltRight',
        'MetaLeft', 'MetaRight', 'OSLeft', 'OSRight', 'CapsLock', 'ContextMenu',
    ];

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    /** @return list<string> */
    public static function actions(): array
    {
        return array_keys(self::DEFAULTS);
    }

    /** @return list<string> */
    public static function forbidden(): array
    {
        return self::FORBIDDEN;
    }

    public static function isValidCode(mixed $code): bool
    {
        return is_string($code)
            && preg_match('/^[A-Za-z0-9]{1,20}$/', $code) === 1
            && ! in_array($code, self::FORBIDDEN, true);
    }

    /**
     * Полная карта действие => код: неизвестные действия отбрасываются, пустые и
     * недопустимые значения заменяются клавишей по умолчанию.
     *
     * @return array<string, string>
     */
    public static function normalize(mixed $value): array
    {
        $value = is_array($value) ? $value : [];
        $result = [];
        foreach (self::DEFAULTS as $action => $default) {
            $result[$action] = self::isValidCode($value[$action] ?? null) ? $value[$action] : $default;
        }

        return $result;
    }

    /**
     * Клавиши заклинаний: id заклинания => код. Пустые и недопустимые значения (нет клавиши) отбрасываются.
     *
     * @return array<int, string>
     */
    public static function normalizeMagic(mixed $value): array
    {
        $result = [];
        foreach (is_array($value) ? $value : [] as $skillId => $code) {
            if (is_numeric($skillId) && (int) $skillId > 0 && self::isValidCode($code)) {
                $result[(int) $skillId] = $code;
            }
        }
        ksort($result);

        return $result;
    }

    /**
     * Клавиши, назначенные больше чем на одно действие или заклинание.
     *
     * @return array<string, list<string>> код => список («действие» или «magic:id»)
     */
    public static function conflicts(array $hotkeys, array $magicHotkeys = []): array
    {
        $byCode = [];
        foreach (self::normalize($hotkeys) as $action => $code) {
            $byCode[$code][] = $action;
        }
        foreach (self::normalizeMagic($magicHotkeys) as $skillId => $code) {
            $byCode[$code][] = 'magic:'.$skillId;
        }

        return array_filter($byCode, static fn (array $actions): bool => count($actions) > 1);
    }

    /** Подпись клавиши для интерфейса (та же логика, что в настройках). */
    public static function label(string $code): string
    {
        $names = [
            'ArrowUp' => '↑', 'ArrowDown' => '↓', 'ArrowLeft' => '←', 'ArrowRight' => '→',
            'PageUp' => 'PgUp', 'PageDown' => 'PgDn', 'Space' => 'Space', 'Minus' => '-', 'Equal' => '=',
            'BracketLeft' => '[', 'BracketRight' => ']', 'Semicolon' => ';', 'Quote' => "'", 'Backquote' => '`',
            'Backslash' => '\\', 'Comma' => ',', 'Period' => '.', 'Slash' => '/',
        ];
        if (isset($names[$code])) {
            return $names[$code];
        }
        if (preg_match('/^Key([A-Z])$/', $code, $m) === 1 || preg_match('/^Digit([0-9])$/', $code, $m) === 1) {
            return $m[1];
        }
        if (str_starts_with($code, 'Numpad')) {
            return 'Num '.substr($code, 6);
        }

        return $code;
    }
}
