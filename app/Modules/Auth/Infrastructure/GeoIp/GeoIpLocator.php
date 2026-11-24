<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure\GeoIp;

use MaxMind\Db\Reader;
use Throwable;

/**
 * Страна, регион и город по IP из локальной базы DB-IP Lite (формат MMDB, без обращений к сети).
 * Нет файла, IP приватный или ошибка чтения: возвращается пустой результат, вход не ломается.
 */
final class GeoIpLocator
{
    private ?Reader $reader = null;

    private bool $readerFailed = false;

    public function path(): string
    {
        return (string) config('services.geoip.path', storage_path('app/geoip/dbip-city-lite.mmdb'));
    }

    /** @return array{country_code: ?string, country: ?string, region: ?string, city: ?string} */
    public function locate(?string $ip): array
    {
        $empty = ['country_code' => null, 'country' => null, 'region' => null, 'city' => null];

        if ($ip === null || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $empty;
        }

        try {
            $record = $this->reader()?->get($ip);
        } catch (Throwable) {
            return $empty;
        }

        if (! is_array($record)) {
            return $empty;
        }

        return [
            'country_code' => $this->limit($record['country']['iso_code'] ?? null, 2),
            'country' => $this->name($record['country']['names'] ?? null, 80),
            'region' => $this->name($record['subdivisions'][0]['names'] ?? null, 100),
            'city' => $this->name($record['city']['names'] ?? null, 100),
        ];
    }

    private function reader(): ?Reader
    {
        if ($this->reader !== null || $this->readerFailed) {
            return $this->reader;
        }

        try {
            $path = $this->path();
            $this->reader = is_file($path) ? new Reader($path) : null;
        } catch (Throwable) {
            $this->reader = null;
        }
        $this->readerFailed = $this->reader === null;

        return $this->reader;
    }

    /** Названия в базе есть на нескольких языках: берём русский, иначе английский. */
    private function name(mixed $names, int $length): ?string
    {
        if (! is_array($names)) {
            return null;
        }

        return $this->limit($names['ru'] ?? $names['en'] ?? null, $length);
    }

    private function limit(mixed $value, int $length): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr($value, 0, $length) : null;
    }
}
