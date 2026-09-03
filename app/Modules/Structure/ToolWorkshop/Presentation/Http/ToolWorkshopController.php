<?php

declare(strict_types=1);

namespace App\Modules\Structure\ToolWorkshop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetRarityUpgradePage;
use App\Modules\Structure\Blacksmith\Application\UseCases\UpgradeItemRarity;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToolWorkshopController extends Controller
{
    public function __construct(
        private readonly GetRarityUpgradePage $getRarityUpgradePage,
        private readonly UpgradeItemRarity $upgradeItemRarity,
    ) {}

    public function index(int $id): View
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getRarityUpgradePage->execute(
            user: $user,
            structureId: $id,
            expectedStructureType: Structure::TYPE_TOOL_WORKSHOP,
            itemType: ShareItemType::TOOL,
        );

        return view('blacksmith::rarity-upgrade', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'items' => $page->items,
            'itemTooltipScript' => $page->itemTooltipScript,
            'buildingTitle' => 'Мастерская инструментов',
            'isToolWorkshop' => true,
            'upgradeHint' => 'Выберите инструмент для повышения ранга. Инструмент должен находиться в рюкзаке.',
            'upgradeProcessUrl' => route('tool_workshop.upgrade.process', ['id' => $id]),
            'emptyListMessage' => 'В рюкзаке нет инструментов с настроенным апгрейдом ранга.',
        ]);
    }

    public function upgrade(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $request->validate(['item_id' => ['required', 'integer']]);

        $result = $this->upgradeItemRarity->execute(
            user: $user,
            structureId: $id,
            itemId: $request->integer('item_id'),
            expectedStructureType: Structure::TYPE_TOOL_WORKSHOP,
            expectedItemType: ShareItemType::TOOL,
        );

        session()->flash('message', $result->message);
        session()->flash('rarity_upgrade_success', $result->success);

        return redirect()->route('tool_workshop', ['id' => $id]);
    }
}
