<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain;

final class ChatMessageLifetime
{
    public const SYSTEM_MINUTES = 30;

    public const CLAN_SYSTEM_MINUTES = 15;

    public const PERSONAL_MINUTES = 10;
}
