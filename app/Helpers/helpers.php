<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('format_cooldown')) {
    function format_cooldown(int $seconds): string
    {
        if ($seconds <= 0) {
            return '—';
        }

        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        if ($minutes === 0) {
            return $secs.'с';
        }

        return $secs > 0 ? $minutes.'м '.$secs.'с' : $minutes.'м';
    }
}

if (! function_exists('format_game_duration')) {
    function format_game_duration(int $seconds): string
    {
        $remaining = max(0, $seconds);
        $units = [
            [86400, ['день', 'дня', 'дней']],
            [3600, ['час', 'часа', 'часов']],
            [60, ['минута', 'минуты', 'минут']],
        ];
        $parts = [];

        foreach ($units as [$unitSeconds, $forms]) {
            $count = intdiv($remaining, $unitSeconds);
            $remaining %= $unitSeconds;

            if ($count > 0) {
                $lastTwoDigits = $count % 100;
                $lastDigit = $count % 10;
                $formIndex = $lastTwoDigits >= 11 && $lastTwoDigits <= 14
                    ? 2
                    : match ($lastDigit) {
                        1 => 0,
                        2, 3, 4 => 1,
                        default => 2,
                    };
                $parts[] = $count.' '.$forms[$formIndex];
            }
        }

        if ($remaining > 0 && $parts === []) {
            $parts[] = $remaining.' сек.';
        }

        return $parts === [] ? '0 минут' : implode(' ', $parts);
    }
}

if (! function_exists('format_money')) {
    function format_money($money, $decimal = 0, $decimalSeparator = '', $thousandsSeparator = ' '): string
    {
        return number_format($money, $decimal, $decimalSeparator, $thousandsSeparator);
    }
}

if (! function_exists('resolve_storage_image_url')) {
    /**
     * Приводит путь картинки, сохранённый в БД, к готовому URL.
     *
     * Новые загрузки хранят ЧИСТЫЙ относительный путь диска 'public' (например
     * 'npc/xxx.png') — URL строится через Storage::url(), единственное место,
     * где фигурирует префикс '/storage': поменяется диск/CDN — правится только
     * здесь. Старые записи (до этого accessor'а) уже содержат готовый абсолютный
     * путь (/storage/..., /img/..., http...) — такие значения возвращаются как есть.
     */
    function resolve_storage_image_url(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}

if (! function_exists('public_media_url')) {
    /**
     * URL картинки, путь которой хранится в БД относительно сайта: статика из public/
     * ('main/images/...', 'img/...') или загрузка на диск 'public' ('race-avatars/...', 'library/...').
     * Сначала ищем статический файл в public/, иначе — диск 'public' (/storage/...).
     * Готовые адреса (http..., /...) возвращаются как есть; пустой путь — $fallback из public/.
     */
    function public_media_url(?string $value, ?string $fallback = null): ?string
    {
        if ($value === null || $value === '') {
            return $fallback !== null ? asset($fallback) : null;
        }
        if (preg_match('~^(?:https?:)?//|^/~', $value) === 1) {
            return $value;
        }
        if (str_starts_with($value, 'storage/')) {
            return asset($value);   // старый вид пути: уже с префиксом ссылки public/storage
        }

        return is_file(public_path($value)) ? asset($value) : Storage::disk('public')->url($value);
    }
}

if (! function_exists('public_media_path')) {
    /**
     * Обратное к public_media_url(): из сохранённого полного адреса этого сайта делает относительный путь
     * ('http://host/storage/race-avatars/x.jpg' → 'race-avatars/x.jpg', 'https://host/main/a.jpg' → 'main/a.jpg').
     * Домен снимается любой (localhost, dev, prod): в БД остаётся путь, не зависящий от окружения.
     * Значение без домена возвращается как есть.
     */
    function public_media_path(string $value): string
    {
        if (preg_match('~^(?:https?:)?//[^/]+(/.*)$~', $value, $match) !== 1) {
            return $value;
        }
        $path = ltrim((string) parse_url($match[1], PHP_URL_PATH), '/');

        return str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
    }
}

if (! function_exists('map_transition_url')) {
    /**
     * Формирует ссылку перехода между картами с учётом текущего режима просмотра.
     */
    function map_transition_url(string $slug, int $locationId): string
    {
        if (request()->routeIs('map.public')) {
            return route('map.public', ['slug' => $slug]).'#'.$locationId;
        }

        return route('on_map', array_merge(
            ['s' => $slug],
            request()->except(['s']),
        )).'#'.$locationId;
    }
}
