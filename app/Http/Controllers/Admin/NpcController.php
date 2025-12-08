<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Npc;
use Illuminate\Http\Request;

class NpcController extends Controller
{
    public function list()
    {
        $list = Npc::query()->orderByDesc('id')->get();

        return view('admin.npc.list', compact('list'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST')) {

            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp',
            ]);

            $data = $request->toArray();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('npc', 'public');
            }

            $npc = new Npc();
            $npc->fill($data);
            $npc->save();

            return redirect()->route('admin.npc');
        }

        $locations = Location::query()->orderByDesc('id')->get();

        return view('admin.npc.create', compact('locations'));
    }

    public function info(Request $request, Npc $npc)
    {
        if ($request->isMethod('POST')) {

            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp',
            ]);

            $data = $request->toArray();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('npc', 'public');
            }

            $npc->fill($data);
            $npc->save();

            return redirect()->route('admin.npc');
        }

        $locations = Location::query()->orderByDesc('id')->get();

        return view('admin.npc.info', compact('npc', 'locations'));
    }
}
