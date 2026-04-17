<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use Illuminate\Support\Facades\Auth;

class ClanSkillController extends Controller
{
    public function __construct(
        protected readonly ClanSkillService $skillService,
        protected readonly PlayerStatService $statService,
    ) {}

    public function index(): mixed
    {
        $user = Auth::user();
        $membership = $user->clanMembership;

        if (! $membership) {
            return redirect()->route('clan');
        }

        $clan = $membership->clan;
        $canLearn = $membership->role->hasPermission(ClanPermission::LEARN_SKILL);

        $definitions = ClanSkillDefinition::with(['levels.stoneItem', 'levels.magicSkill'])
            ->orderBy('sort_order')
            ->get();

        $learnedMap = $clan->learnedSkills()
            ->pluck('current_level', 'clan_skill_definition_id');

        $player = $user->player;
        $backpackShareItemIds = Backpack::where('user_id', $user->id)
            ->with('item')
            ->get()
            ->map(fn ($b) => $b->item->share_item_id)
            ->unique()
            ->values();

        $playerDecorator = $this->statService->resolve($player);

        return view('clan::skills', compact(
            'clan', 'membership', 'definitions', 'learnedMap',
            'canLearn', 'backpackShareItemIds', 'player', 'playerDecorator'
        ));
    }

    public function learn(int $id): mixed
    {
        $user = Auth::user();
        $membership = $user->clanMembership;

        if (! $membership) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        if (! $membership->role->hasPermission(ClanPermission::LEARN_SKILL)) {
            session()->flash('message', 'У вас нет прав изучать навыки клана.');

            return redirect()->route('clan.skills');
        }

        $clan = $membership->clan;
        $definition = ClanSkillDefinition::findOrFail($id);
        $player = $user->player;

        $error = $this->skillService->learn($clan, $definition, $player);

        if ($error) {
            session()->flash('message', $error);
        } else {
            session()->flash('success', "Навык «{$definition->name}» успешно улучшен!");
        }

        return redirect()->route('clan.skills');
    }
}
