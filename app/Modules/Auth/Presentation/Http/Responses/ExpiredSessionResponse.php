<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http\Responses;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ExpiredSessionResponse
{
    public static function make(Request $request): Response
    {
        $response = $request->expectsJson()
            ? response()->json(['message' => 'Unauthenticated.', 'redirect' => route('index')], 401)
            : response()->view('auth::session-expired', ['homeUrl' => route('index')], 401);

        return $response->header('X-Game-Session-Expired', '1')->header('Cache-Control', 'no-store, private');
    }
}
