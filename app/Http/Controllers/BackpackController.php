<?php

namespace App\Http\Controllers;

use App\Services\BackpackService;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipRenderer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BackpackController extends Controller
{

    public function __construct(
        protected readonly BackpackService $backpackService,
        protected readonly ItemTooltipCollector $collector,
        protected readonly ItemTooltipRenderer $renderer,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'sid' => $request->get('sid'),
            'group' => $request->get('group', 'main'),
        ];

        $data = $this->backpackService->getBackpackData($user, $filters);

        $playerEquip = $user->player->playerEquip;

        foreach ($data->getBackpack() as $item) {
            $this->collector->addFromBackpack($item);
        }

        $itemTooltipScript = $this->renderer->render($this->collector->all());

        return view('backpack.index', compact('data', 'user', 'playerEquip', 'itemTooltipScript'));
    }
}
