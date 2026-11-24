<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth;

use App\Modules\Auth\Infrastructure\GeoIp\GeoIpLocator;
use Tests\TestCase;

class GeoIpLocatorTest extends TestCase
{
    public function test_private_invalid_and_missing_ips_return_empty_result(): void
    {
        $locator = new GeoIpLocator;
        $empty = ['country_code' => null, 'country' => null, 'region' => null, 'city' => null];

        $this->assertSame($empty, $locator->locate(null));
        $this->assertSame($empty, $locator->locate('172.18.0.1'));
        $this->assertSame($empty, $locator->locate('127.0.0.1'));
        $this->assertSame($empty, $locator->locate('not-an-ip'));
    }

    public function test_missing_database_file_does_not_break_lookup(): void
    {
        config()->set('services.geoip.path', '/nonexistent/dbip.mmdb');

        $this->assertNull((new GeoIpLocator)->locate('8.8.8.8')['country_code']);
    }

    public function test_public_ip_is_resolved_from_the_local_database_when_present(): void
    {
        $locator = new GeoIpLocator;
        if (! is_file($locator->path())) {
            $this->markTestSkipped('База DB-IP не скачана (storage/app/geoip).');
        }

        $result = $locator->locate('8.8.8.8');

        $this->assertSame('US', $result['country_code']);
        $this->assertNotNull($result['city']);
    }
}
