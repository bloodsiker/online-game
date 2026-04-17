<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Structure\Blacksmith\Application\DTOs\BreakItemDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\CraftItemDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradeItemDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\BreakItem;
use App\Modules\Structure\Blacksmith\Application\UseCases\CraftItem;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetBreakPage;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetKraftPage;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetUpgradePage;
use App\Modules\Structure\Blacksmith\Application\UseCases\UpgradeItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlacksmithController extends Controller
{
    public function __construct(
        private readonly GetKraftPage $getKraftPage,
        private readonly CraftItem $craftItem,
        private readonly GetBreakPage $getBreakPage,
        private readonly BreakItem $breakItem,
        private readonly GetUpgradePage $getUpgradePage,
        private readonly UpgradeItem $upgradeItem,
    ) {}

    public function index(Request $request, mixed $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getKraftPage->execute($user, (int) $id);

        return view('blacksmith::kraft', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'recipes' => $page->recipes,
            'resources' => $page->resources,
            'itemTooltipScript' => $page->itemTooltipScript,
        ]);
    }

    public function kraftItem(Request $request, mixed $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->craftItem->execute(new CraftItemDTO($user, (int) $id));
        session()->flash('message', $result->message);

        return redirect()->back();
    }

    public function breakItem(mixed $id, Request $request): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($request->has('iid')) {
            $result = $this->breakItem->execute(
                new BreakItemDTO(
                    user: $user,
                    blacksmithId: (int) $id,
                    itemId: (int) $request->get('iid'),
                ),
            );
            session()->flash('message', $result->message);

            return redirect()->back();
        }

        $page = $this->getBreakPage->execute($user, (int) $id);

        return view('blacksmith::break', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'items' => $page->items,
            'crystal' => $page->crystal,
            'itemTooltipScript' => $page->itemTooltipScript,
        ]);
    }

    public function upgrade(Request $request, int $id): View
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getUpgradePage->execute($user, $id);

        return view('blacksmith::upgrade', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'player' => $page->player,
            'playerDecorator' => $page->playerDecorator,
            'items' => $page->items,
            'baseScrolls' => $page->baseScrolls,
            'bonusScrolls' => $page->bonusScrolls,
            'itemTooltipScript' => $page->itemTooltipScript,
        ]);
    }

    public function upgradeProcess(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'base_scroll_id' => 'required|integer',
            'bonus_scroll_id' => 'nullable|integer',
        ]);

        $result = $this->upgradeItem->execute(
            new UpgradeItemDTO(
                user: $user,
                itemId: $request->integer('item_id'),
                baseScrollId: $request->integer('base_scroll_id'),
                bonusScrollId: $request->filled('bonus_scroll_id') ? $request->integer('bonus_scroll_id') : null,
            ),
        );

        session()->flash('message', $result->message);
        session()->flash('upgrade_success', $result->success);
        session()->flash('upgrade_destroyed', $result->destroyed);

        return redirect()->route('blacksmith.upgrade', ['id' => $id]);
    }
}
