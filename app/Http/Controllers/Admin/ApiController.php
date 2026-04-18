<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Map;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function locations(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = Location::with('map')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', is_numeric($search) ? (int) $search : 0);
            })
            ->orderBy('name');

        $total = $query->count();
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'results' => $results->map(fn (Location $loc) => [
                'id' => $loc->id,
                'text' => "[{$loc->id}] {$loc->name}".($loc->map ? " ({$loc->map->name})" : ''),
            ]),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = ShareItem::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0);
        })
            ->orderBy('name');

        $total = $query->count();
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'results' => $results->map(fn (ShareItem $item) => [
                'id' => $item->id,
                'text' => "[{$item->id}] {$item->name}",
                'image' => $item->image,
            ]),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function npcs(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = Npc::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0);
        })
            ->orderBy('name');

        $total = $query->count();
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'results' => $results->map(fn (Npc $npc) => [
                'id' => $npc->id,
                'text' => "[{$npc->id}] {$npc->name}",
            ]),
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    public function maps(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = Map::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0);
        })
            ->orderBy('name');

        $total = $query->count();
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'results' => $results->map(fn (Map $map) => [
                'id' => $map->id,
                'text' => "[{$map->id}] {$map->name}",
            ]),
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    public function quests(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = Quest::when($search, function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0);
        })
            ->orderBy('id');

        $total = $query->count();
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'results' => $results->map(fn (Quest $quest) => [
                'id' => $quest->id,
                'text' => "[{$quest->id}] {$quest->title}",
            ]),
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }
}
