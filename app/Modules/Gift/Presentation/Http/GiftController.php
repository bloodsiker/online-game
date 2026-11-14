<?php

declare(strict_types=1);

namespace App\Modules\Gift\Presentation\Http;

use App\Modules\Gift\Application\ItemTooltip\GiftTooltipStrategy;
use App\Modules\Gift\Application\UseCases\SendGift;
use App\Modules\Gift\Domain\Contracts\PlayerGiftRepository;
use App\Modules\Gift\Presentation\Http\Requests\SendGiftRequest;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

final class GiftController
{
    public function send(SendGiftRequest $request, SendGift $sendGift): JsonResponse
    {
        $result = $sendGift->execute(
            sender: $request->user(),
            backpackId: (int) $request->validated('backpack_id'),
            recipientName: (string) $request->validated('recipient'),
            message: $request->validated('message'),
            isAnonymous: $request->boolean('anonymous'),
        );

        return response()->json([
            'ok' => $result->ok,
            'message' => $result->message,
            'remaining' => $result->remainingCount,
        ], $result->ok ? 200 : 422);
    }

    /** Все подарки игрока — публичная страница, как и его информация. */
    public function index(int $id, PlayerGiftRepository $gifts, ItemTooltipCollector $tooltips): View
    {
        $user = User::query()->without('player')->select(['id', 'name'])->findOrFail($id);
        $page = $gifts->paginateFor((int) $user->id, 40);

        return view('gift::index', [
            'user' => $user,
            'gifts' => $page,
            'itemTooltipScript' => $tooltips->collectFrom(new GiftTooltipStrategy($page->items()))->renderScript(),
        ]);
    }
}
