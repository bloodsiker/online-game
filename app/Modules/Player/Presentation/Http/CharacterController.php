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
        private readonly GetCharacter $getCharacter,
        private readonly AllocateStats $allocateStats,
    ) {}

    public function index(Request $request): View
    {
        $player = Auth::user()->player;
        $character = $this->getCharacter->execute($player);
        $group = $request->get('group', 'character');

        return view('player::index', compact('character', 'group'));
    }

    public function point(): View
    {
        $player = Auth::user()->player;
        $character = $this->getCharacter->execute($player);

        return view('player::points', compact('character'));
    }

    public function pointSave(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->toArray();
        $player = Auth::user()->player;

        $statKeys = ['strength', 'intuition', 'agility', 'intelligence', 'wisdom'];
        $stats = array_map('intval', array_intersect_key($data, array_flip($statKeys)));

        try {
            $result = $this->allocateStats->execute($player, $stats);
        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            session()->flash('message', $e->getMessage());

            return redirect()->back();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Характеристики изменены.',
                'free_stats' => $result->freeStats,
                'strength' => $result->strength,
                'intuition' => $result->intuition,
                'agility' => $result->agility,
                'intelligence' => $result->intelligence,
                'wisdom' => $result->wisdom,
            ]);
        }

        session()->flash('message', 'Характеристики изменены.');

        return redirect()->route('character');
    }
}
