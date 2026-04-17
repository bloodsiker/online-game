<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\User;
use App\Modules\Structure\Warehouse\Application\UseCases\GetBackpackItems;
use App\Modules\Structure\Warehouse\Application\UseCases\GetWarehouseCount;
use App\Modules\Structure\Warehouse\Application\UseCases\GetWarehouseItems;
use App\Modules\Structure\Warehouse\Application\UseCases\PutItems;
use App\Modules\Structure\Warehouse\Application\UseCases\TakeItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly PutItems $putItems,
        private readonly TakeItems $takeItems,
        private readonly GetBackpackItems $getBackpackItems,
        private readonly GetWarehouseItems $getWarehouseItems,
        private readonly GetWarehouseCount $getWarehouseCount,
    ) {}

    public function index(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user      = Auth::user();
        $warehouse = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->putItems->execute($user, $warehouse, (array) $request->input('item', []));
            if (!$result->ok || $result->message !== '') {
                session()->flash('message', $result->message);
            }
            if (!$result->ok) {
                return redirect()->back();
            }
        }

        $putItems         = $this->getBackpackItems->execute($user->id);
        $countInWarehouse = $this->getWarehouseCount->execute($user->id, $warehouse->id);

        return view('warehouse::put', compact('warehouse', 'user', 'putItems', 'countInWarehouse'));
    }

    public function takeItem(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user      = Auth::user();
        $warehouse = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->takeItems->execute($user, $warehouse, (array) $request->input('item', []));
            if (! $result->ok) {
                session()->flash('message', $result->message);
                return redirect()->back();
            }
        }

        $itemsToTake      = $this->getWarehouseItems->execute($user->id, $warehouse->id);
        $countInWarehouse = $this->getWarehouseCount->execute($user->id, $warehouse->id);

        return view('warehouse::take', compact('warehouse', 'user', 'itemsToTake', 'countInWarehouse'));
    }
}
