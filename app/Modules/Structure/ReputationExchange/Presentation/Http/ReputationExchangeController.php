<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\GambleExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Domain\Services\ReputationExchangeStrategyResolver;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReputationExchangeController extends Controller
{
    public function __construct(
        private readonly ReputationExchangeStrategyResolver $strategyResolver,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $structure = Structure::with('npc')->findOrFail($id);

        if ($user->location_id !== $structure->npc->location_id) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');

            return redirect()->back();
        }

        $strategy = $this->strategyResolver->resolve($structure);

        try {
            $data = $strategy->getPageData($user, $structure);
        } catch (DomainException) {
            session()->flash('message', 'Вы находитесь не в том месте для обмена.');

            return redirect()->back();
        }

        $page = $data['page'];
        $shareItemIds = $page instanceof ReputationExchangePageDTO
            ? array_column($page->items, 'shareItemId')
            : ($page instanceof GambleExchangePageDTO ? array_column($page->resources, 'shareItemId') : []);

        $shareItems = ShareItem::whereIn('id', $shareItemIds)->get();
        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($shareItems));

        return view($strategy->viewName(), [
            'page' => $page,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }

    public function apply(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $structure = Structure::with('npc')->findOrFail($id);

        if ($user->location_id !== $structure->npc->location_id) {
            return redirect()->back()->with('message', 'Вы находитесь не в том месте для обмена.');
        }

        $strategy = $this->strategyResolver->resolve($structure);
        $result = $strategy->perform($user, $structure, $request->all());

        return redirect()->back()->with('message', $result->message);
    }
}
