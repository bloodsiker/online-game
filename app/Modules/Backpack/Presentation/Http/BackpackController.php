<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Application\UseCases\GetBackpack;
use App\Modules\Backpack\Application\UseCases\UpdateOrder;
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

    public function equip(): View
    {
        return view('backpack::equip', [
            'playerEquip' => Auth::user()->player->playerEquip,
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
