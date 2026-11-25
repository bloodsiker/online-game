<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Player\Application\Services\AdminPlayerTeleportService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
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

    public function detain(Request $request, DetentionService $detentionService): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'prison_structure_id' => ['required', 'integer', Rule::exists('structures', 'id')->where('type', Structure::TYPE_PRISON)->whereNotNull('location_id')],
            'indefinite' => ['nullable', 'boolean'],
            'duration_minutes' => ['exclude_if:indefinite,1,true', 'required', 'integer', 'min:1', 'max:525600'],
            'reason' => ['required', 'string', 'max:1000'],
            'fine_money' => ['nullable', 'integer', 'min:0', 'max:1000000000000'],
            'fine_diamond' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000000'],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        $prison = Structure::query()->findOrFail((int) $validated['prison_structure_id']);
        $detention = $detentionService->impose(
            $user,
            $prison,
            $request->user(),
            $request->boolean('indefinite') ? null : (int) $validated['duration_minutes'],
            $validated['reason'],
            (int) ($validated['fine_money'] ?? 0),
            (float) ($validated['fine_diamond'] ?? 0),
        );

        return response()->json([
            'ok' => true,
            'user_id' => (int) $user->id,
            'message' => $detention->isIndefinite()
                ? sprintf('%s помещён в Темницу бессрочно.', $user->name)
                : sprintf('%s помещён в Темницу до %s.', $user->name, $detention->releaseLabel()),
        ]);
    }

    /** Снять наказание: выпустить из Темницы или снять молчание в чате/на форуме. */
    public function pardon(Request $request, DetentionService $detentionService, CommunicationMuteService $muteService): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'kind' => ['required', Rule::in(['detention', CommunicationScope::Chat->value, CommunicationScope::Forum->value])],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);

        if ($validated['kind'] === 'detention') {
            $released = $detentionService->releaseByAdmin($user);

            return response()->json([
                'ok' => $released,
                'message' => $released
                    ? sprintf('%s освобождён из Темницы.', $user->name)
                    : sprintf('%s не находится в Темнице.', $user->name),
            ]);
        }

        $scope = CommunicationScope::from($validated['kind']);
        $mute = $muteService->activeMute($user, $scope);
        if ($mute !== null) {
            $muteService->revoke($mute, $request->user());
        }

        return response()->json([
            'ok' => $mute !== null,
            'message' => $mute !== null
                ? sprintf('%s: молчание «%s» снято.', $user->name, $scope->label())
                : sprintf('%s: активного молчания «%s» нет.', $user->name, $scope->label()),
        ]);
    }
}
