<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaceController extends Controller
{
    public function list(): View
    {
        $listRaces = Race::orderByDesc('id')->get();

        return view('admin.race.list', compact('listRaces'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $race = new Race();
            $this->fillRace($race, $request);
            $race->save();

            return redirect()->route('admin.race.info', $race->id)->with('success', 'Раса создана.');
        }

        return view('admin.race.create');
    }

    public function info(Request $request, Race $race): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillRace($race, $request);
            $race->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        return view('admin.race.info', compact('race'));
    }

    private function fillRace(Race $race, Request $request): void
    {
        $race->name       = $request->input('name');
        $race->str        = (float) $request->input('str', 0);
        $race->agil       = (float) $request->input('agil', 0);
        $race->int        = (float) $request->input('int', 0);
        $race->mud        = (float) $request->input('mud', 0);
        $race->intel      = (float) $request->input('intel', 0);
        $race->free_stats = (int) $request->input('free_stats', 0);
    }
}
