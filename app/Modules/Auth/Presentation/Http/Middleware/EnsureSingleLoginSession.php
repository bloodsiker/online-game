<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http\Middleware;

use App\Modules\Auth\Application\Listeners\ActivateLoginSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSingleLoginSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $token = $request->session()->get(ActivateLoginSession::SESSION_KEY);
        $activeToken = $user->getAttribute('active_login_token');
        if (is_string($token) && is_string($activeToken) && hash_equals($activeToken, $token)) {
            return $next($request);
        }

        // Не вращаем remember_token: он уже принадлежит новому входу.
        Auth::guard('web')->logoutCurrentDevice();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = __('Сессия завершена: выполнен вход в аккаунт в другом браузере или на другом устройстве.');
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return response('<script>window.top.location.href='.json_encode(route('index'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).';</script>');
    }
}
