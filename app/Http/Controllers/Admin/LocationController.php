<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function list()
    {
        $list = Skill::query()->orderByDesc('id')->get();

        return view('admin.skill.list', compact('list'));
    }

    public function info(Request $request, Skill $skill)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $skill->fill($data);
            $skill->save();

            return redirect()->route('admin.skills');
        }

        return view('admin.skill.info', compact('skill'));
    }
}
