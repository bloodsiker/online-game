<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Криптоплатежи Heleket (https://doc.heleket.hk/ru). Без merchant/api_key ордера остаются ручными.
    'heleket' => [
        'merchant' => env('HELEKET_MERCHANT_UUID'),
        'api_key' => env('HELEKET_PAYMENT_API_KEY'),
        'base_url' => env('HELEKET_BASE_URL', 'https://api.heleket.hk'),
        'lifetime' => (int) env('HELEKET_INVOICE_LIFETIME', 3600),
        // IP отправителя вебхуков. Пусто — проверяется только подпись (например, за прокси без real-ip).
        'webhook_ips' => array_filter(explode(',', (string) env('HELEKET_WEBHOOK_IPS', '31.133.220.8'))),
    ],

    // Trybit (https://docs.trybit.com/ru) — криптоплатежи. URL уведомлений в кабинете: /payments/trybit/webhook
    'trybit' => [
        'api_key' => env('TRYBIT_API_KEY'),
        'shop_id' => env('TRYBIT_SHOP_ID'),
        'secret_key' => env('TRYBIT_SECRET_KEY'),
        'base_url' => env('TRYBIT_BASE_URL', 'https://api.trybit.com'),
        'lifetime_minutes' => (int) env('TRYBIT_INVOICE_LIFETIME_MINUTES', 60),
    ],

    // plata by mono (https://monobank.ua/api-docs/acquiring). Оплата картой в гривне.
    'monobank' => [
        'token' => env('MONOBANK_TOKEN'),
        'base_url' => env('MONOBANK_BASE_URL', 'https://api.monobank.ua'),
        // Цена бриллианта задаётся в USD, карта списывает гривну по курсу НБУ на день.
        // uah_per_usd — запасной курс, если API НБУ недоступен и прошлого курса нет.
        'nbu_url' => env('NBU_RATE_URL', 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange'),
        'uah_per_usd' => (float) env('MONOBANK_UAH_PER_USD', 42),
        'validity' => (int) env('MONOBANK_INVOICE_VALIDITY', 3600),
    ],

    // Страна и город по IP: локальная база DB-IP Lite (MMDB), обновляется раз в месяц командой geoip:update.
    'geoip' => [
        'path' => env('GEOIP_DATABASE_PATH', storage_path('app/geoip/dbip-city-lite.mmdb')),
        'download_url' => 'https://download.db-ip.com/free/dbip-city-lite-%s.mmdb.gz',
    ],

    // Тег Google (GTM). Пустое значение — тег не выводится.
    'google_tag' => [
        'id' => env('GOOGLE_TAG_ID', 'GTM-M9R2VFHZ'),
        // Локально и в тестах аналитику не шлём.
        'enabled' => env('GOOGLE_TAG_ENABLED', ! in_array(env('APP_ENV'), ['local', 'testing'], true)),
    ],

];
