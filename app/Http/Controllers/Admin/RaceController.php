<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function list()
    {
        $list = Race::query()->orderByDesc('id')->get();

        return view('admin.race.list', compact('list'));
    }

    public function info(Request $request, Race $race)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $race->fill($data);
            $race->save();

            return redirect()->route('admin.race');
        }

        return view('admin.race.info', compact('race'));
    }
}
