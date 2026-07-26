<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationGateController extends Controller
{
    public function list(): View
    {
        $gates = LocationGate::with(['fromLocation', 'toLocation', 'shareItem'])
            ->orderByDesc('id')
            ->get();

        return view('admin.location_gate.list', compact('gates'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $gate = new LocationGate;
            $this->fillGate($gate, $request);
            $gate->save();

            return redirect()->route('admin.location-gate.info', $gate->id)->with('success', 'Врата созданы.');
        }

        return view('admin.location_gate.create');
    }

    public function info(Request $request, LocationGate $locationGate): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillGate($locationGate, $request);
            $locationGate->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $locationGate->load(['fromLocation', 'toLocation', 'shareItem']);

        return view('admin.location_gate.info', ['gate' => $locationGate]);
    }

    public function delete(LocationGate $locationGate): RedirectResponse
    {
        $locationGate->delete();

        return redirect()->route('admin.location-gates')->with('success', 'Врата удалены.');
    }

    private function fillGate(LocationGate $gate, Request $request): void
    {
        $gate->from_location_id = (int) $request->input('from_location_id');
        $gate->to_location_id = (int) $request->input('to_location_id');
        $gate->share_item_id = (int) $request->input('share_item_id');
        $gate->mode = $request->input('mode', 'presence_pass');
        $gate->consume_item = (bool) $request->input('consume_item', false);
        $gate->button_label = $request->input('button_label') ?: null;
    }
}
