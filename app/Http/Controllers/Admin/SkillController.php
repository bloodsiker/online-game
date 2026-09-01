<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Skill\Infrastructure\Persistence\Models\SkillLevelRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SkillController extends Controller
{
    private const PEACEFUL_PROFESSION_MAX_LEVEL = 300;

    public function list()
    {
        $list = Skill::orderByDesc('id')->get();

        return view('admin.skill.list', compact('list'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $skill = new Skill;
            $this->fillSkill($skill, $request);
            $skill->save();

            return redirect()->route('admin.skill.info', $skill->id)
                ->with('success', 'Навык создан.');
        }

        return view('admin.skill.create');
    }

    public function info(Request $request, Skill $skill): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillSkill($skill, $request);
            $skill->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $requirements = $skill->type === 'peaceful'
            ? SkillLevelRequirement::query()
                ->where('skill_id', $skill->id)
                ->orderBy('lvl')
                ->get()
                ->keyBy('lvl')
            : collect();

        return view('admin.skill.info', compact('skill', 'requirements'));
    }

    public function updatePeacefulProfessionRequirements(Request $request, Skill $skill): RedirectResponse
    {
        abort_unless($skill->type === 'peaceful', 404);

        $data = $request->validate([
            'requirements' => ['required', 'array', 'size:'.self::PEACEFUL_PROFESSION_MAX_LEVEL],
            'requirements.*' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        $requirements = [];
        $previousExperience = 0;
        for ($level = 1; $level <= self::PEACEFUL_PROFESSION_MAX_LEVEL; $level++) {
            if (! array_key_exists($level, $data['requirements'])) {
                throw ValidationException::withMessages([
                    'requirements' => 'Нужно заполнить каждый уровень с 1 по 300.',
                ]);
            }

            $experience = (int) $data['requirements'][$level];
            if ($experience <= $previousExperience) {
                throw ValidationException::withMessages([
                    "requirements.$level" => 'Порог должен быть больше значения предыдущего уровня.',
                ]);
            }

            $requirements[] = [
                'skill_id' => $skill->id,
                'lvl' => $level,
                'exp_required' => $experience,
                'exp_diff' => $experience - $previousExperience,
            ];
            $previousExperience = $experience;
        }

        DB::transaction(function () use ($skill, $requirements): void {
            DB::table('skill_level_requirements')->upsert(
                $requirements,
                ['skill_id', 'lvl'],
                ['exp_required', 'exp_diff'],
            );

            $requirementsByLevel = collect($requirements)->keyBy('lvl');
            DB::table('player_skills')
                ->where('skill_id', $skill->id)
                ->orderBy('id')
                ->eachById(function (object $playerSkill) use ($requirementsByLevel): void {
                    $requirement = $requirementsByLevel->get(min(
                        self::PEACEFUL_PROFESSION_MAX_LEVEL,
                        max(1, (int) $playerSkill->lvl),
                    ));

                    DB::table('player_skills')->where('id', $playerSkill->id)->update([
                        'exp_up' => $requirement['exp_required'],
                        'exp_diff' => $requirement['exp_diff'],
                        'updated_at' => now(),
                    ]);
                });
        });

        return back()->with('success', 'Шкала мирной профессии сохранена.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillSkill(Skill $skill, Request $request): void
    {
        $skill->name = $request->input('name');
        $skill->description = $request->input('description');
        $skill->type = $request->input('type');
    }
}
