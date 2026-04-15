<?php

namespace App\Http\Middleware;

use App\Modules\Player\Domain\Services\PlayerStatService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __construct(private PlayerStatService $statService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->last_online_at = Carbon::now();
            $user->save();

            $sheet = $this->statService->resolve($user->player);
            $user->player->regenerate($sheet->getHpMax(), $sheet->getMpMax());
        }

        return $next($request);
    }
}
