<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Application\UseCases\GetBackpack;
use App\Modules\Backpack\Application\UseCases\UpdateOrder;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BackpackController extends Controller
{
    public function __construct(
        private readonly GetBackpack $getBackpack,
        private readonly UpdateOrder $updateOrder,
        private readonly PlayerStatService $statService,
    ) {}

    public function index(): View
    {
        return view('backpack::index');
    }

    public function equip(ItemTooltipCollector $tooltipCollector): View
    {
        /** @var User $user */
        $user = Auth::user();
        $playerEquip = $user->player->playerEquip;
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
            'hpMp' => $this->buildHpMp($user),
        ]);
    }

    public function bag(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $filters = [
            'sid' => $request->get('sid'),
            'group' => $request->get('group', 'main'),
        ];

        $viewData = $this->getBackpack->execute($user, $filters);
        $viewData['hpMp'] = $this->buildHpMp($user);

        return view('backpack::bag', $viewData);
    }

    /**
     * HP/MP с учётом бонусов экипировки (в т.ч. камней и рун) — рассылается
     * во «character-frame» после действий со снаряжением, иначе хиро-фрейм
     * показывает устаревшие значения до перезагрузки страницы.
     *
     * @return array{hp: array{current: int, max: int}, mp: array{current: int, max: int}}
     */
    private function buildHpMp(User $user): array
    {
        $player = $user->player;
        $sheet = $this->statService->resolve($player);

        return [
            'hp' => ['current' => $player->hp_now, 'max' => $sheet->getHpMax()],
            'mp' => ['current' => $player->mp_now, 'max' => $sheet->getMpMax()],
        ];
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $this->updateOrder->execute(Auth::id(), $request->input('ids', []));

        return response()->json(['status' => 'success']);
    }
}
