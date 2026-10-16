<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Middleware;

use App\Modules\User\Application\Services\GameSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ApplyGameLocale
{
    public function __construct(private GameSettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user() !== null
            ? $this->settings->get((int) $request->user()->id)['language']
            : $request->session()->get('language', 'ru');
        $locale = in_array($locale, ['ru', 'uk'], true) ? $locale : 'ru';
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);
        app('translator')->setFallback('ru');

        try {
            return $next($request);
        } finally {
            // Long-running application workers must not retain another player's language.
            app()->setLocale($previousLocale);
        }
    }
}
