<?php

namespace App\Http\Controllers;

use App\Models\Player\Player;
use App\Models\Player\PlayerSkill;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $player = $user->player;

        $menu = [
            'level' => [
                'title'  => 'По уровню',
                'name'   => 'Опыт',
                'column' => 'exp',
            ],
            'victories' => [
                'title'  => 'По победам',
                'name'   => 'Победы',
                'column' => 'victory',
            ],
            'deaths' => [
                'title'  => 'По поражениям',
                'name'   => 'Поражения',
                'column' => 'death',
            ],
            'wealth' => [
                'title'  => 'По богатству',
                'name'   => 'Монеты',
                'column' => 'user.money',
            ],
        ];

        foreach (Skill::orderBy('id')->get() as $skill) {
            $menu['skill_' . $skill->id] = [
                'title'  => $skill->name,
                'name'   => 'Уровень навыка',
                'column' => 'lvl',
            ];
        }

        $type          = request('type', array_key_first($menu));
        $isSkillRating = str_starts_with($type, 'skill_');

        if (! array_key_exists($type, $menu)) {
            $type = array_key_first($menu);
        }

        if ($isSkillRating) {
            $skillId = (int) str_replace('skill_', '', $type);
            $players = PlayerSkill::with('player.user.clanMembership')
                ->where('skill_id', $skillId)
                ->orderByDesc('lvl')
                ->orderByDesc('exp')
                ->paginate(40)
                ->withQueryString();
        } else {
            $query = Player::with(['user.clanMembership']);

            $query = match ($type) {
                'victories' => $query->orderByDesc('victory'),
                'deaths'    => $query->orderByDesc('death'),
                'wealth'    => $query
                    ->join('users', 'players.user_id', '=', 'users.id')
                    ->orderByDesc('users.money')
                    ->select('players.*'),
                default     => $query->orderByDesc('lvl')->orderByDesc('exp'),
            };

            $players = $query->paginate(40)->withQueryString();
        }

        return view('rating.index', compact('user', 'player', 'players', 'menu', 'type', 'isSkillRating'));
    }

    public function search(Request $request): JsonResponse
    {
        $nick = trim($request->get('nick', ''));
        $type = $request->get('type', 'level');

        if ($nick === '') {
            return response()->json(['error' => 'Введите ник игрока.']);
        }

        $perPage  = 40;
        $position = null;
        $chunk    = 0;

        if (str_starts_with($type, 'skill_')) {
            $skillId = (int) str_replace('skill_', '', $type);

            PlayerSkill::with('player.user')
                ->where('skill_id', $skillId)
                ->orderByDesc('lvl')
                ->orderByDesc('exp')
                ->chunk(200, function ($items) use ($nick, &$position, &$chunk) {
                    foreach ($items as $item) {
                        $chunk++;
                        if (mb_strtolower($item->player->user->name ?? '') === mb_strtolower($nick)) {
                            $position = $chunk;
                            return false;
                        }
                    }
                });
        } else {
            $query = Player::with('user');

            $query = match ($type) {
                'victories' => $query->orderByDesc('victory'),
                'deaths'    => $query->orderByDesc('death'),
                'wealth'    => $query
                    ->join('users', 'players.user_id', '=', 'users.id')
                    ->orderByDesc('users.money')
                    ->select('players.*'),
                default     => $query->orderByDesc('lvl')->orderByDesc('exp'),
            };

            $query->chunk(200, function ($players) use ($nick, &$position, &$chunk) {
                foreach ($players as $player) {
                    $chunk++;
                    if (mb_strtolower($player->user->name ?? '') === mb_strtolower($nick)) {
                        $position = $chunk;
                        return false;
                    }
                }
            });
        }

        if ($position === null) {
            return response()->json(['error' => "Игрок «{$nick}» не найден в рейтинге."]);
        }

        return response()->json(['page' => (int) ceil($position / $perPage), 'position' => $position]);
    }
}