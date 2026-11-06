<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http;

use App\Modules\News\Application\UseCases\SubscribeToLaunch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LaunchSubscriptionController
{
    public function __invoke(Request $request, SubscribeToLaunch $subscribe): JsonResponse|RedirectResponse
    {
        abort_unless(config('game.prelaunch'), 404);
        $request->merge(['email' => is_string($request->input('email')) ? trim($request->input('email')) : $request->input('email')]);
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:254']]);
        $subscribe->execute(email: $data['email'], locale: app()->getLocale());
        $message = __('prelaunch.subscribed');

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : redirect()->route('index', ['launch' => 1])->with('launch_subscription_message', $message);
    }
}
