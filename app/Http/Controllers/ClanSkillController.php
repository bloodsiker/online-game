<?php

namespace App\Http\Controllers;

use App\Enums\ClanPermission;
use App\Models\Backpack;
use App\Models\Clan\ClanSkillDefinition;
use App\Services\ClanSkillService;
use Illuminate\Support\Facades\Auth;

class ClanSkillController extends Controller
{
    public function __construct(
        protected readonly ClanSkillService $skillService,
    ) {}

    public function index()
    {
        $user       = Auth::user();
        $membership = $user->clanMembership;

        if (!$membership) {
            return redirect()->route('clan');
        }

        $clan    = $membership->clan;
        $canLearn = $membership->role->hasPermission(ClanPermission::LEARN_SKILL);

        $definitions = ClanSkillDefinition::with(['levels.stoneItem'])
            ->orderBy('sort_order')
            ->get();

        $learnedMap = $clan->learnedSkills()
            ->pluck('current_level', 'clan_skill_definition_id');

        // Check stone availability in player's backpack
        $player = $user->player;
        $backpackShareItemIds = Backpack::where('user_id', $user->id)
            ->with('item')
            ->get()
            ->map(fn($b) => $b->item->share_item_id)
            ->unique()
            ->values();

        return view('clan.skills', compact(
            'clan', 'membership', 'definitions', 'learnedMap',
            'canLearn', 'backpackShareItemIds'
        ));
    }

    public function learn(int $id)
    {
        $user       = Auth::user();
        $membership = $user->clanMembership;

        if (!$membership) {
            session()->flash('message', 'Вы не состоите в клане.');
            return redirect()->route('clan');
        }

        if (!$membership->role->hasPermission(ClanPermission::LEARN_SKILL)) {
            session()->flash('message', 'У вас нет прав изучать навыки клана.');
            return redirect()->route('clan.skills');
        }

        $clan       = $membership->clan;
        $definition = ClanSkillDefinition::findOrFail($id);
        $player     = $user->player;

        $error = $this->skillService->learn($clan, $definition, $player);

        if ($error) {
            session()->flash('message', $error);
        } else {
            session()->flash('success', "Навык «{$definition->name}» успешно улучшен!");
        }

        return redirect()->route('clan.skills');
    }
}