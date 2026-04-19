<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Http\Controllers\Controller;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $recentPlayers = User::with('player.race')
            ->whereNotNull('last_online_at')
            ->orderByDesc('last_online_at')
            ->limit(10)
            ->get();

        $recentBattles = Battle::with(['location', 'detailsWithUsers.user', 'detailsWithMonsters.locationMonster.monster'])
            ->where('status', BattleStatus::FINISH)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('recentPlayers', 'recentBattles'));
    }
}
