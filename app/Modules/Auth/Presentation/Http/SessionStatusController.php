<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SessionStatusController
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(['authenticated' => $request->user() !== null])
            ->header('Cache-Control', 'no-store, private');
    }
}
