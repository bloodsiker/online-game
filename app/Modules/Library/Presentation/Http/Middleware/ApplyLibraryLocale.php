<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApplyLibraryLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');
        abort_unless(in_array($locale, ['ru', 'uk'], true), 404);
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $response = $next($request);
            $response->headers->set('Content-Language', $locale);

            return $response;
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
