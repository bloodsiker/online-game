<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth;

use App\Modules\Auth\Domain\Services\UserAgentParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserAgentParserTest extends TestCase
{
    /** @return array<string, array{0: string, 1: array<string, ?string>}> */
    public static function agents(): array
    {
        return [
            'chrome windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.109 Safari/537.36',
                ['browser' => 'Chrome', 'browser_version' => '120.0', 'os' => 'Windows', 'os_version' => '10/11', 'device_type' => 'desktop'],
            ],
            'edge windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.2210.91',
                ['browser' => 'Edge', 'browser_version' => '120.0', 'os' => 'Windows', 'os_version' => '10/11', 'device_type' => 'desktop'],
            ],
            'firefox linux' => [
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
                ['browser' => 'Firefox', 'browser_version' => '121.0', 'os' => 'Linux', 'os_version' => null, 'device_type' => 'desktop'],
            ],
            'safari iphone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
                ['browser' => 'Safari', 'browser_version' => '17.2', 'os' => 'iOS', 'os_version' => '17.2', 'device_type' => 'mobile'],
            ],
            'chrome android phone' => [
                'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36',
                ['browser' => 'Chrome', 'browser_version' => '120.0', 'os' => 'Android', 'os_version' => '13', 'device_type' => 'mobile'],
            ],
            'ipad safari' => [
                'Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
                ['browser' => 'Safari', 'browser_version' => '16.6', 'os' => 'iOS', 'os_version' => '16.6', 'device_type' => 'tablet'],
            ],
            'yandex browser' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 YaBrowser/23.11.0 Yowser/2.5 Safari/537.36',
                ['browser' => 'Yandex Browser', 'browser_version' => '23.11', 'os' => 'Windows', 'os_version' => '10/11', 'device_type' => 'desktop'],
            ],
            'googlebot' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                ['browser' => null, 'browser_version' => null, 'os' => null, 'os_version' => null, 'device_type' => 'bot'],
            ],
            'empty' => [
                '',
                ['browser' => null, 'browser_version' => null, 'os' => null, 'os_version' => null, 'device_type' => 'unknown'],
            ],
        ];
    }

    /** @param array<string, ?string> $expected */
    #[DataProvider('agents')]
    public function test_parses_browser_os_and_device(string $userAgent, array $expected): void
    {
        $this->assertSame($expected, (new UserAgentParser)->parse($userAgent));
    }
}
