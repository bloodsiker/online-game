<?php

namespace App\Http\Controllers;

use App\Events\PlayerChangeStat;
use App\Services\PlayerStatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller
{
    public function __construct(private PlayerStatService $statService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);

        $group = $request->get('group', 'character');

        return view('character.index', compact('user', 'player', 'playerDecorator', 'group'));
    }

    public function point(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);

        return view('character.points', compact('user', 'player', 'playerDecorator'));
    }

    public function pointSave(Request $request)
    {
        $data = $request->toArray();
        $user = Auth::user();
        $player = $user->player;

        $changeToSum = ['strength', 'intuition', 'agility', 'intelligence', 'wisdom'];
        $filtered = array_filter(
            $data,
            function($value, $key) use ($changeToSum) {
                return in_array($key, $changeToSum);
            },
            ARRAY_FILTER_USE_BOTH
        );

        $sumChange = array_sum(array_map('intval', $filtered));

        $nowToSum = ['ostrength', 'ointuition', 'oagility', 'ointelligence', 'owisdom'];
        $filteredNow = array_filter(
            $data,
            function($value, $key) use ($nowToSum) {
                return in_array($key, $nowToSum);
            },
            ARRAY_FILTER_USE_BOTH
        );

        if ($sumChange > $player->getFreeStats()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'У вас нет столько свободных характеристик'], 422);
            }
            session()->flash('message', 'У вас нет столько свободных характеристик');
            return redirect()->back();
        }

        $player->strength     += $data['strength'];
        $player->intuition    += $data['intuition'];
        $player->agility      += $data['agility'];
        $player->intelligence += $data['intelligence'];
        $player->wisdom       += $data['wisdom'];
        $player->free_stats -= $sumChange;
        $player->save();

        $sumChangeNow = array_sum(array_map('intval', $filteredNow));
        if ($sumChangeNow == $player->getSumStats()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'ok', 'message' => 'Основные характеристики остались прежними.']);
            }
            session()->flash('message', 'Основные характеристики остались прежними.');
            return redirect()->back();
        }

        event(new PlayerChangeStat($player));

        if ($request->expectsJson()) {
            $player->refresh();
            return response()->json([
                'status'     => 'ok',
                'message'    => 'Характеристики изменены.',
                'free_stats' => $player->free_stats,
                'strength'     => $player->getStrength(),
                'intuition'    => $player->getInt(),
                'agility'      => $player->getAgility(),
                'intelligence' => $player->getIntelligence(),
                'wisdom'       => $player->getMud(),
            ]);
        }

        session()->flash('message', 'Характеристики изменены.');
        return redirect()->route('character');
    }
}