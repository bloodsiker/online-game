<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Listeners\RecordLoginHistory;
use App\Modules\Auth\Infrastructure\Persistence\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Принимает данные браузера (экран, часовой пояс, сеть) один раз за сессию и дописывает их в запись входа. */
final class LoginClientInfoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'timezone' => ['nullable', 'string', 'max:64'],
            'language' => ['nullable', 'string', 'max:16'],
            'languages' => ['nullable', 'string', 'max:120'],
            'screen_width' => ['nullable', 'integer', 'between:0,20000'],
            'screen_height' => ['nullable', 'integer', 'between:0,20000'],
            'viewport_width' => ['nullable', 'integer', 'between:0,20000'],
            'viewport_height' => ['nullable', 'integer', 'between:0,20000'],
            'pixel_ratio' => ['nullable', 'numeric', 'between:0,20'],
            'touch_points' => ['nullable', 'integer', 'between:0,255'],
            'color_scheme' => ['nullable', 'in:light,dark'],
            'display_mode' => ['nullable', 'string', 'max:16'],
            'connection_type' => ['nullable', 'in:slow-2g,2g,3g,4g'],
            'connection_kind' => ['nullable', 'string', 'max:16'],
            'downlink' => ['nullable', 'numeric', 'between:0,100000'],
            'rtt' => ['nullable', 'integer', 'between:0,65535'],
            'save_data' => ['nullable', 'boolean'],
            'device_memory' => ['nullable', 'numeric', 'between:0,1024'],
            'cpu_cores' => ['nullable', 'integer', 'between:0,255'],
        ]);

        $id = $request->session()->get(RecordLoginHistory::SESSION_KEY);
        if ($id === null) {
            return response()->json(status: 204);
        }

        // Только своя запись входа и только один раз за сессию.
        LoginHistory::query()
            ->whereKey($id)
            ->where('user_id', Auth::id())
            ->whereNull('client_reported_at')
            ->update($data + ['client_reported_at' => now()]);

        return response()->json(status: 204);
    }
}
