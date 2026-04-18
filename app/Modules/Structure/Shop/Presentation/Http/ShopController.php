<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Shop\Application\UseCases\BuyItem;
use App\Modules\Structure\Shop\Application\UseCases\GetBuyPage;
use App\Modules\Structure\Shop\Application\UseCases\GetSellPage;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function __construct(
        private readonly BuyItem $buyItem,
        private readonly SellItems $sellItems,
        private readonly GetBuyPage $getBuyPage,
        private readonly GetSellPage $getSellPage,
    ) {}

    public function index(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('shop::buy', [
            'page' => $this->getBuyPage->execute($user, $id),
        ]);
    }

    public function buyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->buyItem->execute(
            user: $user,
            shareItemId: $itemId,
            count: $request->integer('count', 1),
        );

        session()->flash('message', $result->message);

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($request->isMethod('POST')) {
            $result = $this->sellItems->execute($user, (array) $request->input('item', []));
            session()->flash('message', $result->message);

            if (! $result->ok) {
                return redirect()->back();
            }
        }

        return view('shop::sell', [
            'page' => $this->getSellPage->execute($user, $id),
        ]);
    }
}
