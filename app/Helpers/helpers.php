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
