<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->last_online_at === null || $user->last_online_at->lt(now()->subSeconds(30))) {
                $user->forceFill(['last_online_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}
