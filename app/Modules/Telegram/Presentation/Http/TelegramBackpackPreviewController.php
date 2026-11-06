<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramBackpackPreview;
use Illuminate\Http\Response;

final class TelegramBackpackPreviewController
{
    public function __invoke(string $token, TelegramBackpackPreview $preview): Response
    {
        $png = $preview->image(token: $token);
        abort_if($png === null, 404);

        return response($png, headers: ['Content-Type' => 'image/png', 'Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff']);
    }
}
