<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillRequirement;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MagicSkillController extends Controller
{
    public function list()
    {
        $list = MagicSkill::withCount('skillEffects')->orderByDesc('id')->get();

        return view('admin.magic_skill.list', compact('list'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $magicSkill = new MagicSkill;
            $this->fillMagicSkill($magicSkill, $request);
            $magicSkill->save();

            return redirect()->route('admin.magic_skill.info', $magicSkill->id)
                ->with('success', 'Скилл создан.');
        }

        return view('admin.magic_skill.create');
    }

    public function info(Request $request, MagicSkill $magic_skill): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillMagicSkill($magic_skill, $request);
            $magic_skill->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $magic_skill->load('skillEffects', 'requirements.skill');
        $effects = Effect::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        return view('admin.magic_skill.info', [
            'magicSkill' => $magic_skill,
            'effects' => $effects,
            'skills' => $skills,
        ]);
    }

    public function addEffect(Request $request, MagicSkill $magic_skill): RedirectResponse
    {
        $magic_skill->skillEffects()->attach($request->input('effect_id'), [
            'chance' => (int) $request->input('chance', 100),
        ]);

        return redirect()->back()->with('success', 'Эффект добавлен.');
    }

    public function deleteEffect(MagicSkill $magic_skill, Effect $effect): RedirectResponse
    {
        $magic_skill->skillEffects()->detach($effect->id);

        return redirect()->back()->with('success', 'Эффект удалён.');
    }

    public function addRequirement(Request $request, MagicSkill $magic_skill): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:stat,skill'],
            'stat_key' => ['nullable', 'string', 'in:'.implode(',', array_column(PlayerStatKey::cases(), 'value'))],
            'skill_id' => ['nullable', 'integer', 'exists:skills,id'],
            'min_value' => ['required', 'integer', 'min:1'],
        ]);

        $type = MagicSkillRequirementType::from($validated['type']);

        if ($type === MagicSkillRequirementType::STAT && empty($validated['stat_key'])) {
            return redirect()->back()->withErrors(['stat_key' => 'Выберите характеристику.'])->withInput();
        }

        if ($type === MagicSkillRequirementType::SKILL && empty($validated['skill_id'])) {
            return redirect()->back()->withErrors(['skill_id' => 'Выберите навык.'])->withInput();
        }

        $magic_skill->requirements()->create([
            'type' => $type,
            'stat_key' => $type === MagicSkillRequirementType::STAT ? $validated['stat_key'] : null,
            'skill_id' => $type === MagicSkillRequirementType::SKILL ? $validated['skill_id'] : null,
            'min_value' => $validated['min_value'],
        ]);

        return redirect()->back()->with('success', 'Требование добавлено.');
    }

    public function deleteRequirement(MagicSkill $magic_skill, MagicSkillRequirement $requirement): RedirectResponse
    {
        abort_unless($requirement->magic_skill_id === $magic_skill->id, 404);

        $requirement->delete();

        return redirect()->back()->with('success', 'Требование удалено.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillMagicSkill(MagicSkill $magicSkill, Request $request): void
    {
        $magicSkill->name = $request->input('name');
        $magicSkill->slug = $request->input('slug');
        $magicSkill->description = $request->input('description');
        $magicSkill->level = (int) $request->input('level', 1);
        $magicSkill->type = $request->input('type');
        $magicSkill->target_type = $request->input('target_type');
        $magicSkill->mana_cost = (int) $request->input('mana_cost', 0);
        $magicSkill->min_damage = (int) $request->input('min_damage', 0);
        $magicSkill->max_damage = (int) $request->input('max_damage', 0);
        $magicSkill->power_coefficient = (float) $request->input('power_coefficient', 0);
        $magicSkill->base_healing = (int) $request->input('base_healing', 0);
        $magicSkill->cooldown = (int) $request->input('cooldown', 0);
        $magicSkill->is_passive = (bool) $request->input('is_passive', false);
    }
}
