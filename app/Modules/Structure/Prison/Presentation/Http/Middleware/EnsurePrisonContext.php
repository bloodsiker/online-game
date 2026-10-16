<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Presentation\Http\Middleware;

use App\Modules\Structure\Prison\Application\Services\DetentionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrisonContext
{
    public function __construct(private readonly DetentionService $detentionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        // The prison page and the map overview remain available while detained.
        if ($request->routeIs('prison.index', 'prison.buyout', 'maps')) {
            return $next($request);
        }

        $this->detentionService->releaseIfExpired($user);
        $detention = $this->detentionService->activeFor($user);
        if ($detention === null) {
            return $next($request);
        }

        $url = route('prison.index', ['id' => $detention->prison_structure_id]);
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => 'Вы находитесь в Темнице до '.$detention->release_at->format('d.m.Y H:i').'.',
                'redirect_url' => $url,
            ], 423);
        }

        return redirect()->to($url);
    }
}
