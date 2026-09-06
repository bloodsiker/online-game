<?php

declare(strict_types=1);

namespace App\Modules\Structure\ToolWorkshop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetRarityUpgradePage;
use App\Modules\Structure\Blacksmith\Application\UseCases\UpgradeItemRarity;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Workshop\Application\UseCases\CraftProfessionItem;
use App\Modules\Structure\Workshop\Application\UseCases\GetWorkshopPage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToolWorkshopController extends Controller
{
    private const TAB_UPGRADE = 'upgrade';

    /**
     * Вкладка => имена навыков-профессий. Кулинар с фолбэком на Повар:
     * старое имя навыка из сидов.
     *
     * @var array<string, list<string>>
     */
    private const PROFESSION_TABS = [
        'alchemist' => ['Алхимик'],
        'cook' => ['Кулинар', 'Повар'],
        'sorcerer' => ['Колдун'],
        'jeweler' => ['Ювелир'],
    ];

    private const TAB_LABELS = [
        'upgrade' => 'Улучшение',
        'alchemist' => 'Алхимик',
        'cook' => 'Кулинар',
        'sorcerer' => 'Колдун',
        'jeweler' => 'Ювелир',
    ];

    public function __construct(
        private readonly GetRarityUpgradePage $getRarityUpgradePage,
        private readonly UpgradeItemRarity $upgradeItemRarity,
        private readonly GetWorkshopPage $getWorkshopPage,
        private readonly CraftProfessionItem $craftProfessionItem,
    ) {}

    public function index(Request $request, int $id): View
    {
        /** @var User $user */
        $user = Auth::user();
        $tab = (string) $request->query('tab', self::TAB_UPGRADE);
        abort_unless($tab === self::TAB_UPGRADE || isset(self::PROFESSION_TABS[$tab]), 404);

        if ($tab !== self::TAB_UPGRADE) {
            $show = (string) $request->query('show', 'learned');
            abort_unless(in_array($show, ['learned', 'all'], true), 404);

            $page = $this->getWorkshopPage->execute(
                user: $user,
                structureId: $id,
                expectedStructureType: Structure::TYPE_TOOL_WORKSHOP,
                skillNames: self::PROFESSION_TABS[$tab],
                learnedOnly: $show === 'learned',
            );

            return view('workshop::index', [
                'workshop' => $page['workshop'],
                'user' => $user,
                'recipes' => $page['recipes'],
                'tabs' => $this->tabs($id),
                'activeTab' => $tab,
                'craftRouteName' => 'tool_workshop.craft',
                'isToolWorkshop' => true,
                'showCraftButton' => $show !== 'all',
                'itemTooltipScript' => $page['itemTooltipScript'],
                'showFilter' => [
                    'current' => $show,
                    'learnedUrl' => route('tool_workshop', ['id' => $id, 'tab' => $tab]),
                    'allUrl' => route('tool_workshop', ['id' => $id, 'tab' => $tab, 'show' => 'all']),
                ],
                'tabSuffix' => '?tab='.$tab.($show === 'all' ? '&show=all' : ''),
            ]);
        }

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
            'tabs' => $this->tabs($id),
            'activeTab' => self::TAB_UPGRADE,
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

    public function craft(Request $request, int $id, int $recipe): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $tab = (string) $request->query('tab', self::TAB_UPGRADE);
        abort_unless(isset(self::PROFESSION_TABS[$tab]), 404);

        $result = $this->craftProfessionItem->execute(
            user: $user,
            structureId: $id,
            recipeId: $recipe,
            expectedStructureType: Structure::TYPE_TOOL_WORKSHOP,
        );

        $params = ['id' => $id, 'tab' => $tab];
        if ($request->query('show') === 'all') {
            $params['show'] = 'all';
        }

        return redirect()
            ->route('tool_workshop', $params)
            ->with($result->ok ? 'message' : 'error', $result->message);
    }

    /** @return list<array{key: string, label: string, route: string, width: int}> */
    private function tabs(int $id): array
    {
        $tabs = [[
            'key' => self::TAB_UPGRADE,
            'label' => self::TAB_LABELS[self::TAB_UPGRADE],
            'route' => route('tool_workshop', ['id' => $id]),
            'width' => 90,
        ]];

        foreach (self::PROFESSION_TABS as $key => $skillNames) {
            $tabs[] = [
                'key' => $key,
                'label' => self::TAB_LABELS[$key],
                'route' => route('tool_workshop', ['id' => $id, 'tab' => $key]),
                'width' => 80,
            ];
        }

        return $tabs;
    }
}
