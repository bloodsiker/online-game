<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Traits\Localizable;

final class TelegramMiniAppController
{
    use Localizable;

    public function show(Request $request): Response
    {
        $locale = $request->query('lang', 'ru');
        $locale = in_array($locale, ['ru', 'uk'], true) ? $locale : 'ru';

        return $this->withLocale($locale, fn (): Response => response()->view('telegram::backpack-app', ['labels' => $this->labels()])
            ->withHeaders(['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer',
                'Content-Security-Policy' => "frame-ancestors 'self' https://web.telegram.org https://*.telegram.org; base-uri 'self'"]));
    }

    public function backpack(Request $request, TelegramReadService $read): JsonResponse
    {
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId),
        ] + $read->allBackpackItems(userId: $userId)));
    }

    /** @return array<string, string> */
    private function labels(): array
    {
        $labels = [];
        foreach (['backpack', 'backpack_empty', 'mini_app_open_from_bot', 'mini_app_session_expired', 'mini_app_loading',
            'mini_app_load_failed', 'mini_app_refresh', 'mini_app_empty', 'mini_app_quantity', 'mini_app_type',
            'mini_app_upgrade', 'mini_app_open', 'previous', 'next', 'account_unavailable', 'not_linked'] as $key) {
            $labels[$key] = __('telegram.'.$key);
        }

        return $labels + ['close' => __('ui.close'), 'connection_failed' => __('ui.connection_failed'), 'too_many_attempts' => __('ui.too_many_attempts')];
    }
}
