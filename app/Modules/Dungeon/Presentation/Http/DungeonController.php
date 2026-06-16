<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Dungeon\Application\UseCases\EnterDungeon;
use App\Modules\Dungeon\Application\UseCases\ExitDungeon;
use App\Modules\Dungeon\Application\UseCases\GetDungeonIndexPage;
use App\Modules\Dungeon\Application\UseCases\GetDungeonShowPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DungeonController extends Controller
{
    public function __construct(
        private readonly GetDungeonIndexPage $getDungeonIndexPage,
        private readonly GetDungeonShowPage $getDungeonShowPage,
        private readonly EnterDungeon $enterDungeon,
        private readonly ExitDungeon $exitDungeon,
    ) {}

    public function index(): mixed
    {
        return view('dungeon::index', [
            'page' => $this->getDungeonIndexPage->execute(Auth::id()),
        ]);
    }

    public function show(int $id): mixed
    {
        return view('dungeon::show', [
            'page' => $this->getDungeonShowPage->execute(Auth::id(), $id),
        ]);
    }

    public function enter(int $id): RedirectResponse
    {
        try {
            $this->enterDungeon->execute(Auth::user(), $id);

            return redirect()->route('location')->with('info', 'Вы вошли в данж!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['dungeon' => $e->getMessage()]);
        }
    }

    public function exit(): RedirectResponse
    {
        $user = Auth::user();
        if (! $this->exitDungeon->execute($user)) {
            return redirect()->route('location');
        }

        return redirect()->route('location')->with('info', 'Вы покинули данж.');
    }
}
