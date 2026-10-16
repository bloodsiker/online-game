<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class EmailVerificationController
{
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('game');
        }

        return view('auth::verify-email', [
            'email' => $user->getEmailForVerification(),
            'onlineCount' => User::query()
                ->where('last_online_at', '>=', now()->subMinutes((int) config('game.online_timeout_minutes', 30)))
                ->count(),
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(
            $request->hasValidSignature()
                && hash_equals(sha1($user->getEmailForVerification()), $hash),
            403,
        );

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('verification.complete')->with('status', 'email-verified');
    }

    public function complete(): View
    {
        return view('auth::email-verified');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('game');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('status', 'verification-email-failed');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
