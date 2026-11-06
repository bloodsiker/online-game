<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\Services;

use InvalidArgumentException;
use JsonException;

final class TelegramInitDataValidator
{
    public function userId(string $initData, string $botToken, int $now, int $maxAge): int
    {
        if ($initData === '' || strlen($initData) > 16384 || $botToken === '') {
            throw new InvalidArgumentException('Invalid Telegram initData.');
        }
        $fields = [];
        foreach (explode('&', $initData) as $pair) {
            $parts = explode('=', $pair, 2);
            $key = urldecode($parts[0]);
            if (count($parts) !== 2 || ! preg_match('/^[a-zA-Z0-9_]+$/D', $key) || array_key_exists($key, $fields)) {
                throw new InvalidArgumentException('Invalid Telegram initData fields.');
            }
            $fields[$key] = urldecode($parts[1]);
        }
        $hash = $fields['hash'] ?? '';
        unset($fields['hash']);
        if (! preg_match('/^[a-f0-9]{64}$/D', $hash)) {
            throw new InvalidArgumentException('Invalid Telegram initData hash.');
        }
        ksort($fields, SORT_STRING);
        $check = implode("\n", array_map(static fn (string $key, string $value): string => $key.'='.$value, array_keys($fields), array_values($fields)));
        $secret = hash_hmac('sha256', $botToken, 'WebAppData', true);
        if (! hash_equals(hash_hmac('sha256', $check, $secret), $hash)) {
            throw new InvalidArgumentException('Invalid Telegram initData signature.');
        }
        $date = $fields['auth_date'] ?? '';
        if (! preg_match('/^[0-9]{1,12}$/D', $date) || (int) $date > $now + 30 || (int) $date < $now - $maxAge) {
            throw new InvalidArgumentException('Expired Telegram initData.');
        }
        try {
            $user = json_decode($fields['user'] ?? '', true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidArgumentException('Invalid Telegram user.');
        }
        if (! is_array($user) || ! is_int($user['id'] ?? null) || $user['id'] < 1 || $user['id'] > 4503599627370495
            || ($user['is_bot'] ?? false) !== false) {
            throw new InvalidArgumentException('Invalid Telegram user.');
        }

        return $user['id'];
    }
}
