<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request;

class InterfaceController extends Controller
{
    public function index()
    {
        return view('interface.index');
    }

    public function main()
    {
        return view('interface.index');
    }

    public function game()
    {
        return view('interface.index');
    }

    public function onMap(Request $request)
    {
        if ($request->get('s')) {
            $map = Map::where('slug', $request->get('s'))->first();
            $view = sprintf('maps.%s.frame', $map->folder);
        } else {
            $user = Auth::user();
            $location = $user->currentLocation;
            $view = sprintf('maps.%s.frame', $location->map->folder);
        }

        return view($view);
    }

    public function menu()
    {
        return view('interface.menu');
    }

    public function who()
    {
        $user = Auth::user();

        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        $onlineOnLocation = User::with(['player'])->where('location_id', $user->location_id)
            ->orderByDesc('last_online_at')
            ->get();

        $onlineOnLocationFiltered = $onlineOnLocation->filter(function ($user) use ($tenMinutesAgo) {
            return $user->last_online_at > $tenMinutesAgo;
        });

        $countOnlineLocation = $onlineOnLocationFiltered->count();

        $onlineInGame = User::with(['player'])->where('last_online_at', '>=', $tenMinutesAgo)
            ->orderByDesc('last_online_at')
            ->get();

        return view('interface.who', compact('onlineOnLocation', 'onlineInGame', 'countOnlineLocation', 'tenMinutesAgo'));
    }

    public function hero()
    {
        $user = Auth::user();
        $player = $user->player;

        return view('interface.hero', compact('player'));
    }
}
