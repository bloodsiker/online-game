<?php

declare(strict_types=1);

namespace App\Modules\Player\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Application\ItemEffect\ItemEffectStrategyFactory;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Item\Domain\Services\ItemUsagePolicyService;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSlot;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\TreasureHunt\Application\Services\TreasureHuntService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotbarController extends Controller
{
    public function __construct(
        private readonly HotbarService $hotbarService,
        private readonly PlayerStatService $statService,
        private readonly TreasureHuntService $treasureHunt,
        private readonly ItemUsagePolicyService $usagePolicy,
    ) {}

    public function index(): JsonResponse
    {
        $player = Auth::user()->player;

        return response()->json($this->hotbarService->getSlotsData($player));
    }

    public function set(Request $request): JsonResponse
    {
        $request->validate([
            'slot' => ['required', 'integer', 'min:1'],
            'entity_type' => ['required', 'in:item,skill'],
            'entity_id' => ['required', 'integer'],
        ]);

        $player = Auth::user()->player;
        $error = $this->hotbarService->setSlot(
            $player,
            $request->integer('slot'),
            $request->input('entity_type'),
            $request->integer('entity_id'),
        );

        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    }

    public function clear(int $slot): JsonResponse
    {
        $player = Auth::user()->player;
        $this->hotbarService->clearSlot($player, $slot);

        return response()->json(['status' => 'success']);
    }

    public function use(Request $request): JsonResponse
    {
        $request->validate(['slot' => ['required', 'integer', 'min:1']]);

        try {
            return DB::transaction(fn (): JsonResponse => $this->performUse($request));
        } catch (ItemUseBlockedException $exception) {
            return response()->json([
                'status' => 'error',
                'code' => $exception->errorCode,
                'message' => $exception->getMessage(),
                'cooldown_until' => $exception->availableAt?->getTimestamp(),
            ], 422);
        }
    }

    private function performUse(Request $request): JsonResponse
    {

        $user = Auth::user();
        $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
        $playerSlot = PlayerSlot::where('player_id', $player->id)
            ->where('slot_number', $request->integer('slot'))
            ->lockForUpdate()
            ->first();

        if (! $playerSlot || $playerSlot->entity_type !== 'item') {
            return response()->json(['status' => 'error', 'message' => __('Слот пуст.')], 422);
        }

        $backpack = Backpack::with('item.itemInfo')
            ->where('user_id', $user->id)
            ->where('item_id', $playerSlot->entity_id)
            ->where('equipped', 0)
            ->lockForUpdate()
            ->first();

        if (! $backpack) {
            $this->hotbarService->clearSlot($player, $playerSlot->slot_number);

            return response()->json(['status' => 'error', 'message' => __('Предмет закончился.'), 'slot_cleared' => true], 422);
        }

        if ($backpack->item->itemInfo->type === ShareItemType::TREASURE_MAP) {
            if (! $backpack->item->itemInfo->is_slot_usable) {
                return response()->json(['status' => 'error', 'action' => 'treasure_check', 'message' => __('Этот предмет нельзя добавить на панель.')], 422);
            }
            try {
                $result = $this->treasureHunt->check($user, (int) $backpack->item_id);

                return response()->json([
                    'status' => 'success', 'action' => 'treasure_check', 'message' => $result['message'],
                    'slot_cleared' => false, 'count' => (int) $backpack->count,
                ]);
            } catch (ValidationException $error) {
                return response()->json([
                    'status' => 'error', 'action' => 'treasure_check',
                    'message' => collect($error->errors())->flatten()->first(),
                ], 422);
            }
        }

        $backpack->item->itemInfo->loadMissing('effects');

        // Currency certificates must use the transactional inventory endpoint, not the legacy hotbar path.
        if ($backpack->item->itemInfo->effects->contains(fn ($effect) => $effect->effect_type === ItemEffectType::GRANT_DIAMONDS)) {
            return response()->json(['status' => 'error', 'message' => __('Используйте этот предмет из рюкзака.')], 422);
        }

        $stats = $this->statService->resolve($player);
        $this->usagePolicy->reserveUse($player, $backpack->item->itemInfo);
        $instantEffects = $backpack->item->itemInfo->effects->filter(
            fn ($effect) => $effect->effect_type->isInstant(),
        );

        foreach ($instantEffects as $effectModel) {
            $effect = $effectModel->toValueObject();
            $strategy = ItemEffectStrategyFactory::make($effect->type);
            $strategy->apply($player, $effect, $stats->getHpMax(), $stats->getMpMax());
        }

        $slotCleared = false;
        $newCount = 0;
        if ($backpack->count <= 1) {
            $backpack->delete();
            $this->hotbarService->clearSlot($player, $playerSlot->slot_number);
            $slotCleared = true;
        } else {
            $backpack->decrement('count');
            $newCount = $backpack->count;
        }

        $player->refresh();
        $stats = $this->statService->resolve($player);

        return response()->json([
            'status' => 'success',
            'cooldown_until' => $this->usagePolicy->cooldownUntil($player, $backpack->item->itemInfo)?->getTimestamp(),
            'slot_cleared' => $slotCleared,
            'count' => $newCount,
            'hp_now' => $player->hp_now,
            'hp_max' => $stats->getHpMax(),
            'mp_now' => $player->mp_now,
            'mp_max' => $stats->getMpMax(),
        ]);
    }
}
