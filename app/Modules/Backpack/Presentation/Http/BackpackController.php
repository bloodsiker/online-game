<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Application\UseCases\GetBackpack;
use App\Modules\Backpack\Application\UseCases\UpdateOrder;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BackpackController extends Controller
{
    public function __construct(
        private readonly GetBackpack $getBackpack,
        private readonly UpdateOrder $updateOrder,
    ) {}

    public function index(): View
    {
        return view('backpack::index');
    }

    public function equip(ItemTooltipCollector $tooltipCollector): View
    {
        $playerEquip = Auth::user()->player->playerEquip;
        $equippedItems = collect([
            $playerEquip->helmetSlot,
            $playerEquip->shoulderSlot,
            $playerEquip->forearmSlot,
            $playerEquip->handLeft,
            $playerEquip->handRight,
            $playerEquip->armorSlot,
            $playerEquip->leggingSlot,
            $playerEquip->chainArmorSlot,
            $playerEquip->shoesSlot,
            $playerEquip->beltFirstSlot,
            $playerEquip->beltSecondSlot,
            $playerEquip->bagFirstSlot,
            $playerEquip->bagSecondSlot,
        ])->filter();

        $tooltipCollector->collectFrom(new ItemModelTooltipStrategy($equippedItems));

        return view('backpack::equip', [
            'playerEquip' => $playerEquip,
            'itemTooltipScript' => $tooltipCollector->renderScript(),
        ]);
    }

    public function bag(Request $request): View
    {
        $filters = [
            'sid' => $request->get('sid'),
            'group' => $request->get('group', 'main'),
        ];

        $viewData = $this->getBackpack->execute(Auth::user(), $filters);

        return view('backpack::bag', $viewData);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $this->updateOrder->execute(Auth::id(), $request->input('ids', []));

        return response()->json(['status' => 'success']);
    }
}
