<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ActivateLoginSession
{
    public const SESSION_KEY = 'active_login_token';

    public function handle(Login $event): void
    {
        if ($event->guard !== 'web' || ! request()->hasSession()) {
            return;
        }

        $token = Str::random(64);
        $rememberToken = Str::random(60);
        DB::table('users')->where('id', $event->user->getAuthIdentifier())->update([
            'active_login_token' => $token,
            'remember_token' => $rememberToken,
        ]);
        $event->user->setAttribute('active_login_token', $token);
        $event->user->setRememberToken($rememberToken);
        request()->session()->put(self::SESSION_KEY, $token);
        if ($event->remember) {
            $guard = Auth::guard('web');
            Cookie::queue(Cookie::make(
                $guard->getRecallerName(),
                $event->user->getAuthIdentifier().'|'.$rememberToken.'|'.$guard->hashPasswordForCookie($event->user->getAuthPassword()),
                (int) config('auth.guards.web.remember', 576000),
            ));
        }
    }
}
