<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Application\UseCases\DropItem;
use App\Modules\Item\Application\UseCases\EquipItem;
use App\Modules\Item\Application\UseCases\GetChestPage;
use App\Modules\Item\Application\UseCases\GetHandOverPage;
use App\Modules\Item\Application\UseCases\GetItemInfoPage;
use App\Modules\Item\Application\UseCases\GetPickupItemsPage;
use App\Modules\Item\Application\UseCases\HandOverToUser;
use App\Modules\Item\Application\UseCases\OpenChest;
use App\Modules\Item\Application\UseCases\PickUpInChest;
use App\Modules\Item\Application\UseCases\UnequipItem;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemEffect\ItemEffectStrategyFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct(
        private readonly GetPickupItemsPage $getPickupItemsPage,
        private readonly DropItem $dropItem,
        private readonly GetHandOverPage $getHandOverPage,
        private readonly HandOverToUser $handOverToUser,
        private readonly EquipItem $equipItem,
        private readonly UnequipItem $unequipItem,
        private readonly OpenChest $openChest,
        private readonly GetChestPage $getChestPage,
        private readonly PickUpInChest $pickUpInChest,
        private readonly GetItemInfoPage $getItemInfoPage,
        private readonly PlayerStatService $statService,
    ) {}

    public function pickUp(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::pickup_items', [
            'page' => $this->getPickupItemsPage->execute($user, $id),
        ]);
    }

    public function dropItem(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->dropItem->execute(
            $user,
            $id,
            $request->integer('c'),
            $request->integer('qty', 0),
        );

        return redirect()->route('backpack');
    }

    public function handOver(int $id): mixed
    {
        return view('item::hand_over', [
            'page' => $this->getHandOverPage->execute($id),
        ]);
    }

    public function handOverToUser(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::hand_over', [
            'page' => $this->handOverToUser->execute($user, $id, $request->integer('uid') ?: null),
        ]);
    }

    public function putOn(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->equipItem->execute($user, $id);

        if ($result->message !== '') {
            session()->flash('message', $result->message);
        } else {
            session()->flash('equip_changed', true);
        }
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function putOff(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->unequipItem->execute($user, $id);

        session()->flash('equip_changed', true);
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function openChest(int $id): RedirectResponse
    {
        $itemId = $this->openChest->execute($id);
        abort_if($itemId === null, 404);

        return redirect()->route('items.view_chest', ['id' => $itemId]);
    }

    public function viewChest(int $id): mixed
    {
        return view('item::chest_items', [
            'page' => $this->getChestPage->execute($id),
        ]);
    }

    public function pickUpInChest(int $chest, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::chest_items', [
            'page' => $this->pickUpInChest->execute($user, $chest, $id),
        ]);
    }

    public function info(int $id): mixed
    {
        return view('item::info', [
            'page' => $this->getItemInfoPage->execute($id),
        ]);
    }

    public function infoByShareItem(int $id): mixed
    {
        return view('item::info', [
            'page' => $this->getItemInfoPage->executeByShareItemId($id),
        ]);
    }

    public function useItem(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $player = $user->player;

        $backpack = Backpack::with('item.itemInfo.effects')
            ->where('user_id', $user->id)
            ->where('item_id', $id)
            ->where('equipped', 0)
            ->first();

        if (! $backpack) {
            return response()->json(['status' => 'error', 'message' => 'Предмет не найден.'], 422);
        }

        $stats = $this->statService->resolve($player);

        $instantEffects = $backpack->item->itemInfo->effects->filter(
            fn ($e) => $e->effect_type->isInstant()
        );

        foreach ($instantEffects as $effectModel) {
            $effect = $effectModel->toValueObject();
            $strategy = ItemEffectStrategyFactory::make($effect->type);
            $strategy->apply($player, $effect, $stats->getHpMax(), $stats->getMpMax());
        }

        $removed = false;
        $newCount = 0;
        if ($backpack->count <= 1) {
            $backpack->delete();
            $removed = true;
        } else {
            $backpack->decrement('count');
            $newCount = $backpack->count;
        }

        $player->refresh();
        $stats = $this->statService->resolve($player);

        return response()->json([
            'status'  => 'success',
            'removed' => $removed,
            'count'   => $newCount,
            'hp_now'  => $player->hp_now,
            'hp_max'  => $stats->getHpMax(),
            'mp_now'  => $player->mp_now,
            'mp_max'  => $stats->getMpMax(),
        ]);
    }
}
