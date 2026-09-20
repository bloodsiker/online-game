<?php

declare(strict_types=1);

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

final class EchoConfigurationTest extends TestCase
{
    public function test_it_reuses_parent_echo_and_does_not_fallback_between_ws_protocols(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3).'/resources/js/echo.js');

        $this->assertIsString($source);
        $this->assertStringContainsString('window.parent.Echo || null', $source);
        $this->assertStringContainsString('window.Echo = parentEcho() || new Echo(', $source);
        $this->assertStringContainsString("enabledTransports: [secure ? 'wss' : 'ws']", $source);
        $this->assertStringNotContainsString("enabledTransports: ['ws', 'wss']", $source);
    }
}
