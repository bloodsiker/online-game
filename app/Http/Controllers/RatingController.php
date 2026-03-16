<?php

namespace App\Http\Controllers;

use App\Models\Player\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;

        $menu = [
            'level' => [
                'title' => 'По уровню',
                'name' => 'Опыт',
                'column' => 'exp',
                'direction' => 'desc',
            ],

            'victories' => [
                'title' => 'По победам',
                'name' => 'Победы',
                'column' => 'victory',
                'direction' => 'desc',
            ],

            'deaths' => [
                'title' => 'По поражениям',
                'name' => 'Поражения',
                'column' => 'death',
                'direction' => 'desc',
            ],

            'wealth' => [
                'title' => 'По богатству',
                'name' => 'Монеты',
                'column' => 'user.money',
                'direction' => 'desc',
            ],
        ];

        $type = request('type', array_key_first($menu));

        $query = Player::with(['user.clanMembership']);

        $players = match ($type) {
            'lvl' => $query->orderByDesc('lvl')->orderByDesc('exp'),
            'victory' => $query->orderByDesc('victory'),
            'death' => $query->orderByDesc('death'),
            'wealth' => $query
                ->join('users', 'players.user_id', '=', 'users.id')
                ->orderByDesc('users.money')
                ->select('players.*'),

            // Сложный рейтинг по скиллам (Join с таблицей skills)
            'skill' => Player::select('players.*')
                ->join('skills', 'players.id', '=', 'skills.player_id')
                ->orderByDesc('skills.level')
                ->with('skills'),

            default => $query->orderByDesc('lvl')->orderByDesc('exp'),
        };

//        $players = Cache::remember('top_players_lvl', 600, function () {
//            return Player::orderBy('lvl', 'desc')->limit(50)->get();
//        });

        $players = $players->paginate(20);

        return view('rating.index', compact('user', 'player', 'players', 'menu', 'type'));
    }
}
