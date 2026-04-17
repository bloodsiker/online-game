<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dungeon\Dungeon;
use App\Services\DungeonService;
use App\Services\PartyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DungeonController extends Controller
{
    public function __construct(
        private readonly DungeonService $dungeonService,
        private readonly PartyService $partyService,
    ) {}

    public function index()
    {
        $dungeons = Dungeon::where('is_active', true)->get();
        $activeSession = $this->dungeonService->getActiveSession(Auth::id());

        return view('dungeon.index', compact('dungeons', 'activeSession'));
    }

    public function show(int $id)
    {
        $dungeon = Dungeon::where('is_active', true)->findOrFail($id);
        $activeSession = $this->dungeonService->getActiveSession(Auth::id());

        return view('dungeon.show', compact('dungeon', 'activeSession'));
    }

    public function enter(int $id): RedirectResponse
    {
        try {
            $party = $this->partyService->getMyParty();
            $dungeon = Dungeon::findOrFail($id);

            if ($dungeon->isGroup() && $party !== null) {
                $this->dungeonService->enterWithParty($id, $party);
            } else {
                $this->dungeonService->enterSolo($id);
            }

            return redirect()->route('location')->with('info', 'Вы вошли в данж!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['dungeon' => $e->getMessage()]);
        }
    }

    public function exit(): RedirectResponse
    {
        $user = Auth::user();

        $session = $this->dungeonService->getActiveSession($user->id);
        if ($session === null) {
            return redirect()->route('location');
        }

        $this->dungeonService->exitDungeon($user);

        return redirect()->route('location')->with('info', 'Вы покинули данж.');
    }
}
