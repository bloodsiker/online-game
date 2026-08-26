<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
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

        return view('admin.skill.info', compact('skill'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillSkill(Skill $skill, Request $request): void
    {
        $skill->name = $request->input('name');
        $skill->description = $request->input('description');
        $skill->type = $request->input('type');
    }
}
