<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http\Middleware;

use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\Telegram\Domain\Services\TelegramInitDataValidator;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateTelegramMiniApp
{
    public function __construct(private readonly TelegramInitDataValidator $validator, private readonly TelegramLinkRepository $links) {}

    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        try {
            $telegramId = $this->validator->userId(initData: (string) $request->header('X-Telegram-Init-Data'),
                botToken: (string) config('telegram.bot_token'), now: now()->timestamp, maxAge: (int) config('telegram.mini_app_auth_max_age_seconds', 3600));
        } catch (InvalidArgumentException) {
            return response()->json(['ok' => false, 'code' => 'invalid_init_data', 'message' => __('telegram.mini_app_session_expired')],
                401, ['Cache-Control' => 'no-store, private']);
        }
        $account = $this->links->forTelegram(telegramUserId: $telegramId);
        if (! $account) {
            return response()->json(['ok' => false, 'code' => 'telegram_not_linked', 'message' => __('telegram.not_linked')],
                403, ['Cache-Control' => 'no-store, private']);
        }
        $request->attributes->set('telegram.account', $account);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
