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

        $players = $players->paginate(40)->withQueryString();

        return view('rating.index', compact('user', 'player', 'players', 'menu', 'type'));
    }

    public function search(Request $request)
    {
        $nick = trim($request->get('nick', ''));
        $type = $request->get('type', 'level');

        if ($nick === '') {
            return response()->json(['error' => 'Введите ник игрока.']);
        }

        $menu = [
            'level'     => ['direction' => 'desc', 'order_col' => 'lvl',     'order_col2' => 'exp'],
            'victories' => ['direction' => 'desc', 'order_col' => 'victory', 'order_col2' => null],
            'deaths'    => ['direction' => 'desc', 'order_col' => 'death',   'order_col2' => null],
            'wealth'    => ['direction' => 'desc', 'order_col' => null,      'order_col2' => null, 'join' => true],
        ];

        $cfg = $menu[$type] ?? $menu['level'];

        $query = Player::with('user');

        if (!empty($cfg['join'])) {
            $query->join('users', 'players.user_id', '=', 'users.id')
                  ->orderByDesc('users.money')
                  ->select('players.*');
        } else {
            $query->orderByDesc($cfg['order_col']);
            if ($cfg['order_col2']) {
                $query->orderByDesc($cfg['order_col2']);
            }
        }

        $position = null;
        $perPage  = 40;
        $chunk    = 0;

        $query->chunk(200, function ($players) use ($nick, $perPage, &$position, &$chunk) {
            foreach ($players as $i => $player) {
                $chunk++;
                if (mb_strtolower($player->user->name ?? '') === mb_strtolower($nick)) {
                    $position = $chunk;
                    return false; // stop chunk
                }
            }
        });

        if ($position === null) {
            return response()->json(['error' => "Игрок «{$nick}» не найден в рейтинге."]);
        }

        $page = (int) ceil($position / $perPage);

        return response()->json(['page' => $page, 'position' => $position]);
    }
}
