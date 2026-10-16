<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureEmailIsVerified
{
    /** @var array<int, string> */
    private const ALLOWED_ROUTES = [
        'login',
        'register',
        'register.check',
        'verification.notice',
        'verification.verify',
        'verification.complete',
        'verification.resend',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->hasVerifiedEmail() || $request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth.email_verification.required'),
                'redirect' => route('verification.notice'),
            ], Response::HTTP_FORBIDDEN);
        }

        return redirect()->route('verification.notice');
    }
}
