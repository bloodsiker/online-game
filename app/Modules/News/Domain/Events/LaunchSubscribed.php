<?php

declare(strict_types=1);

namespace App\Modules\News\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

/** Новый e-mail в списке предрегистрации (пока регистрация закрыта). */
final class LaunchSubscribed
{
    use Dispatchable;

    public function __construct(
        public readonly string $email,
        public readonly string $locale,
        public readonly int $subscribersTotal,
    ) {}
}
