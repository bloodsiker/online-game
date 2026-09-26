<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Player\Application\Services\AdminPlayerTeleportService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class GameToolsController extends Controller
{
    public function teleport(Request $request, AdminPlayerTeleportService $teleportService): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        $location = Location::query()->findOrFail((int) $validated['location_id']);

        $teleportService->teleport($user, $location);

        return response()->json([
            'ok' => true,
            'message' => sprintf('%s телепортирован на локацию №%d.', $user->name, $location->id),
        ]);
    }

    public function mute(Request $request, CommunicationMuteService $muteService): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'scope' => ['required', Rule::enum(CommunicationScope::class)],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        $scope = CommunicationScope::from($validated['scope']);
        $mute = $muteService->mute(
            $user,
            $request->user(),
            $scope,
            (int) $validated['duration_minutes'],
            $validated['reason'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'message' => sprintf(
                '%s: молчание «%s» наложено до %s.',
                $user->name,
                $scope->label(),
                $mute->expires_at->format('d.m.Y H:i'),
            ),
        ]);
    }
}
