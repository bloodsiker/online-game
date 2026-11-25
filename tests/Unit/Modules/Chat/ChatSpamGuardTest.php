<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Services\ChatSpamGuard;
use PHPUnit\Framework\TestCase;

class ChatSpamGuardTest extends TestCase
{
    public function test_spam_with_different_trailing_symbols_has_the_same_fingerprint(): void
    {
        $base = ChatSpamGuard::fingerprint('antineverfate_c_o_m  с нами играйте +');

        foreach (['antineverfate_c_o_m  с нами играйте *-*-*-', 'ANTINEVERFATE_C_O_M с нами играйте/-/-/+', 'antineverfate com с нами играйте ++++'] as $variant) {
            $this->assertSame($base, ChatSpamGuard::fingerprint($variant));
        }
    }

    public function test_different_messages_and_symbol_only_messages_differ(): void
    {
        $this->assertNotSame(ChatSpamGuard::fingerprint('идём на босса'), ChatSpamGuard::fingerprint('жду у ворот'));
        $this->assertSame('', ChatSpamGuard::fingerprint('+++ --- ***'));
    }
}
