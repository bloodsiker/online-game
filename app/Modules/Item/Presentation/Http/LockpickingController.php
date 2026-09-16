<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Item\Application\Services\LockpickingService;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LockpickingController extends Controller
{
    public function __construct(
        private readonly LockpickingService $lockpickingService,
        private readonly ItemTooltipCollector $itemTooltipCollector,
    ) {}

    public function show(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        $page = $this->lockpickingService->page($user, $id);
        $possibleLoot = $page['item']->itemInfo->itemHasItems;
        $lockpickItems = ShareItem::query()
            ->whereIn('id', collect($page['lockpicks'])->pluck('shareItemId'))
            ->get();
        $this->itemTooltipCollector->collectFrom(new ShareItemTooltipStrategy($possibleLoot->concat($lockpickItems)));

        return view('item::lockpicking', [
            'page' => $page,
            'itemTooltipScript' => $this->itemTooltipCollector->renderScript(),
        ]);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(['lockpick_share_item_id' => ['nullable', 'integer', 'min:1']]);
        $result = $this->lockpickingService->start($user, $id, isset($validated['lockpick_share_item_id']) ? (int) $validated['lockpick_share_item_id'] : null);

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function complete(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->lockpickingService->complete($user, $id);

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function cancel(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->lockpickingService->cancel($user, $id);

        return response()->json($result->toArray(), $result->httpCode);
    }
}
