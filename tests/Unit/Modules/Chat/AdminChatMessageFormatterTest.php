<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Services\AdminChatMessageFormatter;
use PHPUnit\Framework\TestCase;

final class AdminChatMessageFormatterTest extends TestCase
{
    public function test_it_preserves_supported_styles_and_normalizes_colors(): void
    {
        $formatter = new AdminChatMessageFormatter;

        self::assertSame(
            '<b>Важно</b> <span style="color: #ff0000;">красным</span>',
            $formatter->clean('<b onclick="alert(1)">Важно</b> <span style="color: rgb(255, 0, 0); position:fixed">красным</span>'),
        );
    }

    public function test_it_removes_dangerous_html(): void
    {
        $formatter = new AdminChatMessageFormatter;

        self::assertSame(
            'Безопасный текст',
            $formatter->clean('<script>alert(1)</script><a href="javascript:alert(2)">Безопасный текст</a>'),
        );
    }
}
