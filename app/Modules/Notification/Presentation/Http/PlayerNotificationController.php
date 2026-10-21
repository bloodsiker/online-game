<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Notification\Application\Services\PlayerNotificationService;
use App\Modules\Notification\Infrastructure\Persistence\Models\PlayerNotification;
use App\Modules\User\Application\Services\GameSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlayerNotificationController extends Controller
{
    public function pending(Request $request, PlayerNotificationService $notifications, GetMessages $messages, GameSettingsService $settings): JsonResponse
    {
        $user = $request->user();
        $locale = $settings->get((int) $user->id)['language'];

        return response()->json($notifications->pending(user: $user)->map(fn (PlayerNotification $notification) => [
            'id' => $notification->id,
            'message' => $messages->mapToDTO(msg: $notification->chatMessage, user: $user, viewChannel: ChatChannel::Main, locale: $locale),
        ])->values());
    }

    public function acknowledge(Request $request, PlayerNotificationService $notifications): JsonResponse
    {
        $data = $request->validate([
            'receipts' => ['required', 'array', 'min:1', 'max:50'],
            'receipts.*.id' => ['required', 'integer', 'min:1'],
            'receipts.*.message_id' => ['required', 'integer', 'min:1'],
        ]);
        $notifications->acknowledge(userId: (int) $request->user()->id, receipts: collect($data['receipts']));

        return response()->json(['ok' => true]);
    }
}
