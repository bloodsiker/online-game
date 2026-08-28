<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Models\ClanSkillLevel;
use App\Modules\Clan\Domain\Models\ClanSkillLevelItemRequirement;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClanSkillController extends Controller
{
    public function index()
    {
        $skills = ClanSkillDefinition::withCount('levels')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.clan_skill.index', compact('skills'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $skill = ClanSkillDefinition::create($this->definitionData($request));

            return redirect()->route('admin.clan_skill.edit', $skill)->with('success', 'Клановый навык создан.');
        }

        return view('admin.clan_skill.create');
    }

    public function edit(Request $request, ClanSkillDefinition $clanSkill): mixed
    {
        if ($request->isMethod('POST')) {
            $clanSkill->update($this->definitionData($request));

            return back()->with('success', 'Основные параметры сохранены.');
        }

        $clanSkill->load('levels.itemRequirements.shareItem', 'levels.magicSkill');

        return view('admin.clan_skill.edit', [
            'skill' => $clanSkill,
            'magicSkills' => MagicSkill::orderBy('name')->get(['id', 'name']),
            'items' => ShareItem::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function addLevel(Request $request, ClanSkillDefinition $clanSkill): RedirectResponse
    {
        $data = $this->levelData($request);
        $data['clan_skill_definition_id'] = $clanSkill->id;

        if ($clanSkill->levels()->where('level', $data['level'])->exists()) {
            return back()->withErrors(['level' => 'Такой уровень уже существует.']);
        }

        ClanSkillLevel::create($data);

        return back()->with('success', 'Уровень навыка добавлен.');
    }

    public function updateLevel(Request $request, ClanSkillDefinition $clanSkill, ClanSkillLevel $level): RedirectResponse
    {
        $this->ensureLevelBelongsToSkill($clanSkill, $level);
        $data = $this->levelData($request);

        if ($clanSkill->levels()->where('level', $data['level'])->where('id', '!=', $level->id)->exists()) {
            return back()->withErrors(['level' => 'Такой уровень уже существует.']);
        }

        $level->update($data);

        return back()->with('success', 'Уровень навыка сохранён.');
    }

    public function deleteLevel(ClanSkillDefinition $clanSkill, ClanSkillLevel $level): RedirectResponse
    {
        $this->ensureLevelBelongsToSkill($clanSkill, $level);
        $level->delete();

        return back()->with('success', 'Уровень навыка удалён.');
    }

    public function addItemRequirement(Request $request, ClanSkillDefinition $clanSkill, ClanSkillLevel $level): RedirectResponse
    {
        $this->ensureLevelBelongsToSkill($clanSkill, $level);
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'count' => ['required', 'integer', 'min:1'],
        ]);

        $level->itemRequirements()->updateOrCreate(
            ['share_item_id' => $data['share_item_id']],
            ['count' => $data['count']],
        );

        return back()->with('success', 'Требование предмета сохранено.');
    }

    public function deleteItemRequirement(ClanSkillDefinition $clanSkill, ClanSkillLevel $level, ClanSkillLevelItemRequirement $requirement): RedirectResponse
    {
        $this->ensureLevelBelongsToSkill($clanSkill, $level);
        abort_unless($requirement->clan_skill_level_id === $level->id, 404);
        $requirement->delete();

        return back()->with('success', 'Требование предмета удалено.');
    }

    private function definitionData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'max_level' => ['required', 'integer', 'min:1', 'max:99'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function levelData(Request $request): array
    {
        return $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:99'],
            'required_clan_level' => ['required', 'integer', 'min:1', 'max:99'],
            'required_bonus_points' => ['required', 'integer', 'min:0'],
            'required_money' => ['required', 'integer', 'min:0'],
            'magic_skill_id' => ['nullable', 'integer', 'exists:magic_skills,id'],
        ]);
    }

    private function ensureLevelBelongsToSkill(ClanSkillDefinition $skill, ClanSkillLevel $level): void
    {
        abort_unless($level->clan_skill_definition_id === $skill->id, 404);
    }
}
