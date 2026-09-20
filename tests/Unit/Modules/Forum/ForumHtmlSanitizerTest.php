<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Forum;

use App\Modules\Forum\Domain\Services\ForumHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class ForumHtmlSanitizerTest extends TestCase
{
    public function test_it_preserves_only_supported_text_alignment(): void
    {
        $sanitizer = new ForumHtmlSanitizer;

        self::assertSame(
            '<p style="text-align: center;">Текст</p>',
            $sanitizer->clean('<p style="color:red; text-align:center; position:fixed">Текст</p>'),
        );
        self::assertSame(
            '<p>Текст</p>',
            $sanitizer->clean('<p style="position:fixed">Текст</p>'),
        );
    }
}
