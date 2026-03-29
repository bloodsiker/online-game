<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clan\Clan;
use App\Models\Clan\ClanTreasuryLog;
use App\Models\Clan\ClanWarehouse;
use App\Models\Clan\ClanWarehouseLog;
use Illuminate\Http\Request;

class ClanController extends Controller
{
    public function list()
    {
        $clans = Clan::withCount('members')
            ->orderByDesc('id')
            ->get();

        return view('admin.clan.list', compact('clans'));
    }

    public function info(Request $request, Clan $clan)
    {
        $clan->load([
            'members.user',
            'members.role',
            'learnedSkills.definition',
            'roles',
        ]);

        $warehouse = ClanWarehouse::where('clan_id', $clan->id)
            ->with('item.itemInfo', 'depositor')
            ->get();

        $treasuryLogs = ClanTreasuryLog::where('clan_id', $clan->id)
            ->with('user')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $warehouseLogs = ClanWarehouseLog::where('clan_id', $clan->id)
            ->with('user', 'item.itemInfo')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('admin.clan.info', compact('clan', 'warehouse', 'treasuryLogs', 'warehouseLogs'));
    }
}