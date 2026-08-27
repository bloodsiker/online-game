<?php

declare(strict_types=1);

namespace App\Modules\Structure\ClanSkillHall\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Application\UseCases\GetClanSkillsPage;
use App\Modules\Clan\Application\UseCases\LearnClanSkill;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ClanSkillHallController extends Controller
{
    public function __construct(
        private readonly GetClanSkillsPage $getClanSkillsPage,
        private readonly LearnClanSkill $learnClanSkill,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(int $id): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $hall = $this->findHall($id);

        try {
            $page = $this->getClanSkillsPage->execute($user);
        } catch (RuntimeException $e) {
            return redirect()->route('location')->with('message', $e->getMessage());
        }

        return view('clan_skill_hall::index', [
            'hall' => $hall,
            'clan' => $page->clan,
            'definitions' => $page->definitions,
            'learnedMap' => $page->learnedMap,
            'canLearn' => $page->canLearn,
            'backpackShareItemCounts' => $page->backpackShareItemCounts,
            'itemTooltipScript' => $this->tooltipCollector
                ->collectFrom(new ShareItemTooltipStrategy($this->requirementItems($page->definitions)))
                ->renderScript(),
        ]);
    }

    public function learn(int $id, int $skillId): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->findHall($id);

        try {
            $error = $this->learnClanSkill->execute($user, $skillId);

            return redirect()->route('clan_skill_hall', ['id' => $id])
                ->with($error === null ? 'success' : 'message', $error ?? 'Навык клана успешно улучшен!');
        } catch (RuntimeException $e) {
            return redirect()->route('clan_skill_hall', ['id' => $id])->with('message', $e->getMessage());
        }
    }

    private function findHall(int $id): Structure
    {
        $hall = Structure::query()->findOrFail($id);
        abort_unless($hall->isClanSkillHall(), 404);

        return $hall;
    }

    private function requirementItems(iterable $definitions): array
    {
        $items = [];

        foreach ($definitions as $definition) {
            foreach ($definition->levels as $level) {
                foreach ($level->itemRequirements as $requirement) {
                    if ($requirement->shareItem !== null) {
                        $items[$requirement->shareItem->id] = $requirement->shareItem;
                    }
                }

                if ($level->stoneItem !== null) {
                    $items[$level->stoneItem->id] = $level->stoneItem;
                }
            }
        }

        return array_values($items);
    }
}
