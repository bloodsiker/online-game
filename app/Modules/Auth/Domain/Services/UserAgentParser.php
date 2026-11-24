<?php

declare(strict_types=1);

namespace App\Modules\Auth\Domain\Services;

/**
 * Лёгкий разбор User-Agent без внешних библиотек: браузер, ОС и тип устройства.
 * Работает за доли миллисекунды и не ходит в сеть.
 */
final class UserAgentParser
{
    /** @return array{browser: ?string, browser_version: ?string, os: ?string, os_version: ?string, device_type: string} */
    public function parse(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);
        if ($ua === '') {
            return ['browser' => null, 'browser_version' => null, 'os' => null, 'os_version' => null, 'device_type' => 'unknown'];
        }

        [$browser, $browserVersion] = $this->browser($ua);
        [$os, $osVersion] = $this->os($ua);

        return [
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'os_version' => $osVersion,
            'device_type' => $this->deviceType($ua),
        ];
    }

    /** @return array{0: ?string, 1: ?string} */
    private function browser(string $ua): array
    {
        // Порядок важен: Chromium-браузеры тоже содержат «Chrome/» и «Safari/».
        $rules = [
            'Edge' => '/\b(?:Edg|EdgA|EdgiOS|Edge)\/([\d.]+)/',
            'Opera' => '/\b(?:OPR|OPiOS)\/([\d.]+)/',
            'Yandex Browser' => '/\bYaBrowser\/([\d.]+)/',
            'Samsung Internet' => '/\bSamsungBrowser\/([\d.]+)/',
            'Firefox' => '/\b(?:Firefox|FxiOS)\/([\d.]+)/',
            'Chrome' => '/(?:\b|Headless)(?:Chrome|CriOS)\/([\d.]+)/',
            'Safari' => '/\bVersion\/([\d.]+).*Safari\//',
        ];

        foreach ($rules as $name => $pattern) {
            if (preg_match($pattern, $ua, $match) === 1) {
                return [$name, $this->shortVersion($match[1])];
            }
        }

        return [null, null];
    }

    /** @return array{0: ?string, 1: ?string} */
    private function os(string $ua): array
    {
        if (preg_match('/Windows NT ([\d.]+)/', $ua, $m) === 1) {
            $names = ['10.0' => '10/11', '6.3' => '8.1', '6.2' => '8', '6.1' => '7', '6.0' => 'Vista', '5.1' => 'XP'];

            return ['Windows', $names[$m[1]] ?? $m[1]];
        }
        if (preg_match('/Android ([\d.]+)/', $ua, $m) === 1) {
            return ['Android', $this->shortVersion($m[1])];
        }
        if (preg_match('/(?:iPhone|CPU) OS ([\d_]+)/', $ua, $m) === 1 || preg_match('/iPad.*OS ([\d_]+)/', $ua, $m) === 1) {
            return ['iOS', $this->shortVersion(str_replace('_', '.', $m[1]))];
        }
        if (str_contains($ua, 'CrOS')) {
            return ['ChromeOS', null];
        }
        if (preg_match('/Mac OS X ([\d_.]+)/', $ua, $m) === 1) {
            // Safari и Chrome замораживают версию macOS в User-Agent, поэтому её не показываем.
            return ['macOS', null];
        }
        if (str_contains($ua, 'Linux') || str_contains($ua, 'X11')) {
            return ['Linux', null];
        }

        return [null, null];
    }

    private function deviceType(string $ua): string
    {
        if (preg_match('/bot|crawl|spider|slurp|headless|facebookexternalhit|preview/i', $ua) === 1) {
            return 'bot';
        }
        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $ua) === 1 || (str_contains($ua, 'Android') && ! str_contains($ua, 'Mobile'))) {
            return 'tablet';
        }
        if (preg_match('/Mobi|iPhone|iPod|Android/i', $ua) === 1) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function shortVersion(string $version): string
    {
        // Мажорная и минорная часть, без длинных хвостов вроде 120.0.6099.109.
        return implode('.', array_slice(explode('.', $version), 0, 2));
    }
}
