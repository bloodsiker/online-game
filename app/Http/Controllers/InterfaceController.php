<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\Referral\Referral;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Request;

class InterfaceController extends Controller
{
    public function __construct(private PlayerStatService $statService) {}

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

    public function interface(Request $request)
    {
        return view('interface.interface');
    }

    public function onMap(Request $request)
    {
        if ($request->get('s')) {
            $map = Map::where('slug', $request->get('s'))->first();
            $view = $map ? sprintf('maps.%s.frame', $map->folder) : 'maps.city.frame';
        } else {
            $user = Auth::user();
            $location = $user->currentLocation;
            $view = sprintf('maps.%s.frame', $location->map->folder);
        }

        if (!View::exists($view)) {
            $view = 'maps.city.frame';
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

        $onlineOnLocation = User::with(['player', 'clanMembership.clan'])->where('location_id', $user->location_id)
            ->orderByDesc('last_online_at')
            ->get();

        $onlineOnLocationFiltered = $onlineOnLocation->filter(function ($user) use ($tenMinutesAgo) {
            return $user->last_online_at > $tenMinutesAgo;
        });

        $countOnlineLocation = $onlineOnLocationFiltered->count();

        $onlineInGame = User::with(['player', 'clanMembership.clan'])->where('last_online_at', '>=', $tenMinutesAgo)
            ->orderByDesc('last_online_at')
            ->get();

        return view('interface.who', compact('onlineOnLocation', 'onlineInGame', 'countOnlineLocation', 'tenMinutesAgo'));
    }

    public function referralsFrame(): \Illuminate\View\View
    {
        $user = Auth::user();

        $referrals = Referral::where('referrer_user_id', $user->id)
            ->with(['referred.player', 'claims'])
            ->get();

        return view('interface.referrals_frame', compact('referrals'));
    }

    public function hero()
    {
        $user   = Auth::user();
        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);

        $activeEffects = \App\Models\Player\PlayerActiveEffect::where('player_id', $player->id)
            ->whereNull('battle_id')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('effect')
            ->get()
            ->map(function ($activeEffect) {
                $name = $activeEffect->effect?->name
                    ?? ($activeEffect->type ? ucfirst($activeEffect->type->value) : 'Эффект');

                $remainingSeconds = $activeEffect->expires_at
                    ? (int) now()->diffInSeconds($activeEffect->expires_at, false)
                    : 0;

                // Определяем тип: blessing или curse
                $isCurse = $activeEffect->type?->isDoT() || $activeEffect->type?->isStun()
                    || ($activeEffect->effect && in_array($activeEffect->effect->type, ['debuff']));

                return [
                    'id'       => $activeEffect->effect?->slug . '_' . $activeEffect->id,
                    'name'     => $name,
                    'duration' => max(0, $remainingSeconds),
                    'is_curse' => $isCurse,
                ];
            });

        return view('interface.hero', compact('player', 'playerDecorator', 'activeEffects'));
    }
}
