<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangeActionDTO;
use App\Modules\Structure\Exchange\Application\UseCases\ApplyExchange;
use App\Modules\Structure\Exchange\Application\UseCases\GetExchangePage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    public function __construct(
        private readonly GetExchangePage $getExchangePage,
        private readonly ApplyExchange $applyExchange,
    ) {}

    public function index(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $page = $this->getExchangePage->execute($user, $id);
        } catch (DomainException) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');

            return redirect()->back();
        }

        return view('exchange::index', [
            'exchange' => $page->exchange,
            'user' => $user,
            'items' => $page->items,
        ]);
    }

    public function apply(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->applyExchange->execute(
            new ExchangeActionDTO(
                user: $user,
                exchangeId: $id,
                fromShareId: $request->integer('from_id'),
                toShareId: $request->integer('to_id'),
                count: $request->integer('count'),
            ),
        );

        return redirect()->back()->with('message', $result->message);
    }
}
