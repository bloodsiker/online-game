<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Modules\Auth\Application\Listeners\ActivateLoginSession;
use App\Modules\Auth\Application\Listeners\RecordLoginHistory;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, ActivateLoginSession::class);
        Event::listen(Login::class, RecordLoginHistory::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'auth');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
