<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Npc;
use Illuminate\Http\Request;

class NpcController extends Controller
{
    public function list()
    {
        $list = Npc::with('location')->orderByDesc('id')->get();

        return view('admin.npc.list', compact('list'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $npc = new Npc;
            $this->fillNpc($npc, $request);
            $npc->save();

            return redirect()->route('admin.npc.info', $npc->id)
                ->with('success', 'НПС создан.');
        }

        return view('admin.npc.create');
    }

    public function info(Request $request, Npc $npc): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillNpc($npc, $request);
            $npc->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        return view('admin.npc.info', compact('npc'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillNpc(Npc $npc, Request $request): void
    {
        $npc->name = $request->input('name');
        $npc->description = $request->input('description');
        $npc->location_id = $request->input('location_id') ?: null;

        if ($request->hasFile('image')) {
            $npc->image = $request->file('image')->store('npc', 'public');
        }
    }
}
