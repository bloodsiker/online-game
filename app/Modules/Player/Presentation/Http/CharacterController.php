<?php

declare(strict_types=1);

namespace App\Modules\Player\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Player\Application\UseCases\AllocateStats;
use App\Modules\Player\Application\UseCases\GetCharacter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(
        private readonly GetCharacter  $getCharacter,
        private readonly AllocateStats $allocateStats,
    ) {}

    public function index(Request $request): View
    {
        $user            = Auth::user();
        $player          = $user->player;
        $playerDecorator = $this->getCharacter->execute($player);
        $group           = $request->get('group', 'character');

        return view('player::index', compact('user', 'player', 'playerDecorator', 'group'));
    }

    public function point(): View
    {
        $user            = Auth::user();
        $player          = $user->player;
        $playerDecorator = $this->getCharacter->execute($player);

        return view('player::points', compact('user', 'player', 'playerDecorator'));
    }

    public function pointSave(Request $request): RedirectResponse|JsonResponse
    {
        $data   = $request->toArray();
        $user   = Auth::user();
        $player = $user->player;

        $statKeys  = ['strength', 'intuition', 'agility', 'intelligence', 'wisdom'];
        $stats     = array_map('intval', array_intersect_key($data, array_flip($statKeys)));
        $sumChange = array_sum($stats);

        if ($sumChange > $player->getFreeStats()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'У вас нет столько свободных характеристик'], 422);
            }
            session()->flash('message', 'У вас нет столько свободных характеристик');

            return redirect()->back();
        }

        $nowToSum    = ['ostrength', 'ointuition', 'oagility', 'ointelligence', 'owisdom'];
        $filteredNow = array_filter($data, fn ($v, $k) => in_array($k, $nowToSum, true), ARRAY_FILTER_USE_BOTH);
        $oldStatSum  = array_sum(array_map('intval', $filteredNow));

        if ($sumChange === 0 || $oldStatSum === $player->getSumStats()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'ok', 'message' => 'Основные характеристики остались прежними.']);
            }
            session()->flash('message', 'Основные характеристики остались прежними.');

            return redirect()->back();
        }

        $this->allocateStats->execute($player, $stats);

        if ($request->expectsJson()) {
            $player->refresh();

            return response()->json([
                'status'       => 'ok',
                'message'      => 'Характеристики изменены.',
                'free_stats'   => $player->free_stats,
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