<?php

declare(strict_types=1);

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    'bot_username' => env('TELEGRAM_BOT_USERNAME', ''),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),
    'api_secret' => env('TELEGRAM_API_SECRET', ''),
    'link_ttl_minutes' => 10,
    'mini_app_auth_max_age_seconds' => 3600,
];
