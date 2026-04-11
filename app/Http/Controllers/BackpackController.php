<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use App\Services\BackpackService;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\BackpackItemTooltipStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackpackController extends Controller
{
    public function __construct(
        protected readonly BackpackService $backpackService,
        protected readonly ItemTooltipCollector $collector,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'sid' => $request->get('sid'),
            'group' => $request->get('group', 'main'),
        ];

        $data = $this->backpackService->getBackpackData($user, $filters);

        $playerEquip = $user->player->playerEquip;

        $this->collector->collectFrom(new BackpackItemTooltipStrategy($data->getBackpack()));

        $itemTooltipScript = $this->collector->renderScript();

        //        $playerDecorator = new EquipmentDecorator($user->player);
        //        dd($playerDecorator->getArmor());

        return view('backpack.index', compact('data', 'user', 'playerEquip', 'itemTooltipScript'));
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $ids    = $request->input('ids', []);

        foreach ($ids as $index => $backpackId) {
            Backpack::where('id', (int) $backpackId)
                ->where('user_id', $userId)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }
}
