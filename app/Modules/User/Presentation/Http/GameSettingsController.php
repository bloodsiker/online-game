<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Presentation\Http\Requests\UpdateGameSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GameSettingsController
{
    public function show(Request $request, GameSettingsService $service): View
    {
        if ($request->session()->has('reload_game_after_settings')) {
            $request->session()->keep('message');
        }

        return view('interface::settings', ['tab' => 'game', 'gameSettings' => $service->get($request->user()->id)]);
    }

    public function update(UpdateGameSettingsRequest $request, GameSettingsService $service): JsonResponse|RedirectResponse
    {
        $settings = $service->update(userId: $request->user()->id, values: $request->validated());
        $request->session()->put('language', $settings['language']);
        $message = __('ui.settings_saved', [], $settings['language']);
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'settings' => $settings, 'message' => $message]);
        }

        return redirect()->route('settings')->with('message', $message)->with('reload_game_after_settings', true);
    }
}
