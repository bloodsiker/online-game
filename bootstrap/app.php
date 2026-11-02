<?php

use App\Http\Middleware\AdminMiddleware;
use App\Modules\Auth\Presentation\Http\Middleware\EnsureEmailIsVerified;
use App\Modules\Auth\Presentation\Http\Middleware\EnsureSingleLoginSession;
use App\Modules\Auth\Presentation\Http\Responses\ExpiredSessionResponse;
use App\Modules\Structure\Prison\Presentation\Http\Middleware\EnsurePrisonContext;
use App\Modules\User\Presentation\Http\Middleware\ApplyGameLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'isAdmin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('index'));
        $middleware->validateCsrfTokens(except: [
            'clan-boss/*/attack',
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            EnsureSingleLoginSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            EnsureEmailIsVerified::class,
            ApplyGameLocale::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            'isAdmin' => AdminMiddleware::class,
            'prison.context' => EnsurePrisonContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return ExpiredSessionResponse::make($request);
        });
        $exceptions->respond(function (Response $response): Response {
            if ($response->getStatusCode() === 401) {
                if ($response instanceof JsonResponse) {
                    $data = $response->getData(true);
                    if (is_array($data)) {
                        $response->setData($data + ['redirect' => route('index')]);
                    }
                    $response->headers->set('X-Game-Session-Expired', '1');
                    $response->headers->set('Cache-Control', 'no-store, private');

                    return $response;
                }

                return ExpiredSessionResponse::make(request());
            }
            if (! in_array($response->getStatusCode(), [405, 419, 500], true)
                || ! str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
                return $response;
            }
            $content = $response->getContent();
            if (! is_string($content)) {
                return $response;
            }
            $guard = '<script src="'.e(asset('js/game-session.js')).'?v='.filemtime(public_path('js/game-session.js')).'"'
                .' data-home-url="'.e(route('index')).'" data-session-status-url="'.e(route('auth.session.status')).'" data-check-session="1"></script>';
            $position = strripos($content, '</body>');
            $response->setContent($position === false ? $content.$guard : substr_replace($content, $guard, $position, 0));
            $response->headers->set('Cache-Control', 'no-store, private');

            return $response;
        });
    })->create();
