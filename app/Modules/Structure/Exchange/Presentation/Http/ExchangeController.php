<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Modules\Structure\Exchange\Domain\Services\ExchangeItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    public function __construct(
        private readonly ExchangeItemService $exchangeItemService,
    ) {}

    public function index(int $id): mixed
    {
        $user     = Auth::user();
        $exchange = Structure::query()->find($id);

        if (! $exchange) {
            abort(404);
        }

        if ($user->location_id !== $exchange->npc->location_id) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');
            return redirect()->back();
        }

        $items = $this->exchangeItemService->getItemsForExchange($exchange, $user);

        return view('exchange::index', compact('exchange', 'user', 'items'));
    }

    public function apply(Request $request, int $id): RedirectResponse
    {
        $user     = Auth::user();
        $exchange = Structure::query()->find($id);

        if ($user->location_id !== $exchange->npc->location_id) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');
            return redirect()->back();
        }

        try {
            $this->exchangeItemService->performExchange(
                user: $user,
                exchange: $exchange,
                fromShareId: $request->integer('from_id'),
                toShareId: $request->integer('to_id'),
                count: $request->integer('count'),
            );

            return redirect()->back()->with('message', 'Обмен успешно выполнен!');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', $e->getMessage());
        }
    }
}