<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\ReputationExchange\Application\UseCases\ApplyReputationExchange;
use App\Modules\Structure\ReputationExchange\Application\UseCases\GetReputationExchangePage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReputationExchangeController extends Controller
{
    public function __construct(
        private readonly GetReputationExchangePage $getReputationExchangePage,
        private readonly ApplyReputationExchange $applyReputationExchange,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $page = $this->getReputationExchangePage->execute($user, $id);
        } catch (DomainException) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');

            return redirect()->back();
        }

        $shareItemIds = array_column($page->items, 'shareItemId');
        $shareItems = ShareItem::whereIn('id', $shareItemIds)->get();
        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($shareItems));

        return view('reputation_exchange::index', [
            'page' => $page,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }

    public function apply(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->applyReputationExchange->execute(
            $user,
            $id,
            $request->integer('share_item_id'),
            $request->integer('count', 1),
        );

        return redirect()->back()->with('message', $result->message);
    }
}
