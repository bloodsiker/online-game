<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\Contracts;

interface TelegramCallbackResponder
{
    public function acknowledge(string $callbackId): void;
}
