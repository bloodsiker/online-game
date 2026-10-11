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
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'settings' => $settings, 'message' => 'Настройки сохранены.']);
        }

        return redirect()->route('settings')->with('message', 'Настройки сохранены.')->with('reload_game_after_settings', true);
    }
}
