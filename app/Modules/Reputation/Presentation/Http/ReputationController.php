<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Reputation\Application\UseCases\BuyReputationShopItem;
use App\Modules\Reputation\Application\UseCases\DeclineReputationQuest;
use App\Modules\Reputation\Application\UseCases\GetReputationListPage;
use App\Modules\Reputation\Application\UseCases\GetReputationPage;
use App\Modules\Reputation\Application\UseCases\GetReputationShopPage;
use App\Modules\Reputation\Application\UseCases\TakeReputationQuest;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReputationController extends Controller
{
    public function __construct(
        private readonly GetReputationListPage $getReputationListPage,
        private readonly GetReputationPage $getReputationPage,
        private readonly TakeReputationQuest $takeReputationQuest,
        private readonly DeclineReputationQuest $declineReputationQuest,
        private readonly GetReputationShopPage $getReputationShopPage,
        private readonly BuyReputationShopItem $buyReputationShopItem,
    ) {}

    public function list(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('reputation::list', [
            'page' => $this->getReputationListPage->execute($user->player),
        ]);
    }

    public function index(int $id): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('reputation::index', [
            'page' => $this->getReputationPage->execute($user, $id),
        ]);
    }

    public function take(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->takeReputationQuest->execute($user, $id);

        return redirect()->route('reputation.index', $id)
            ->with($result->ok ? 'rep_success' : 'rep_error', $result->message);
    }

    public function decline(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $npcId = $this->declineReputationQuest->execute($user, $id);

        return redirect()->route('npc', $npcId);
    }

    public function shop(int $id): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('reputation::shop', [
            'page' => $this->getReputationShopPage->execute($user, $id),
        ]);
    }

    public function buy(int $id, int $itemId): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->buyReputationShopItem->execute($user, $id, $itemId);

        return redirect()->route('reputation.shop', $id)
            ->with($result->ok ? 'shop_success' : 'shop_error', $result->message);
    }
}
