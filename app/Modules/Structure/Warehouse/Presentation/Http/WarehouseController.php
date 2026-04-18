<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Warehouse\Application\UseCases\GetPutPage;
use App\Modules\Structure\Warehouse\Application\UseCases\GetTakePage;
use App\Modules\Structure\Warehouse\Application\UseCases\PutItems;
use App\Modules\Structure\Warehouse\Application\UseCases\TakeItems;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly PutItems $putItems,
        private readonly TakeItems $takeItems,
        private readonly GetPutPage $getPutPage,
        private readonly GetTakePage $getTakePage,
    ) {}

    public function index(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $warehouse = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->putItems->execute($user, $warehouse, (array) $request->input('item', []));
            if (! $result->ok || $result->message !== '') {
                session()->flash('message', $result->message);
            }
            if (! $result->ok) {
                return redirect()->back();
            }
        }

        return view('warehouse::put', [
            'page' => $this->getPutPage->execute($user, $warehouse),
        ]);
    }

    public function takeItem(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $warehouse = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->takeItems->execute($user, $warehouse, (array) $request->input('item', []));
            if (! $result->ok) {
                session()->flash('message', $result->message);

                return redirect()->back();
            }
        }

        return view('warehouse::take', [
            'page' => $this->getTakePage->execute($user, $warehouse),
        ]);
    }
}
