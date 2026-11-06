<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramLinkService;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TelegramSettingsController
{
    public function create(Request $request, TelegramLinkService $service): JsonResponse|RedirectResponse
    {
        $link = $service->createLink(userId: $request->user()->id);
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'url' => $link], headers: ['Cache-Control' => 'no-store, private']);
        }

        return redirect()->route('settings')->with('telegram_link', $link);
    }

    public function destroy(Request $request, TelegramLinkRepository $links): RedirectResponse
    {
        $links->unlink(userId: $request->user()->id);
        $request->session()->forget('telegram_link');

        return redirect()->route('settings')->with('message', __('telegram.unlinked'));
    }
}
