<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function list()
    {
        $list = Skill::query()->orderByDesc('id')->get();

        return view('admin.skill.list', compact('list'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST')) {
            $npc = new Skill();
            $npc->fill($request->toArray());
            $npc->save();

            return redirect()->route('admin.skills');
        }

        return view('admin.skill.create');
    }

    public function info(Request $request, Skill $skill)
    {
        if ($request->isMethod('POST')) {
            $skill->fill($request->toArray());
            $skill->save();

            return redirect()->route('admin.skills');
        }

        return view('admin.skill.info', compact('skill'));
    }
}
