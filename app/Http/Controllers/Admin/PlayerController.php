<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backpack;
use App\Models\Player\Player;
use App\Models\Share\ShareItem;
use App\Services\BackpackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::with('user', 'race')->orderByDesc('id')->get();

        return view('admin.player.list', compact('players'));
    }

    public function info(Request $request, Player $player): mixed
    {
        if ($request->isMethod('POST')) {
            $player->lvl        = (int) $request->input('lvl', 1);
            $player->exp        = (int) $request->input('exp', 0);
            $player->hp_now     = (int) $request->input('hp_now');
            $player->hp_max     = (int) $request->input('hp_max');
            $player->mp_now     = (int) $request->input('mp_now');
            $player->mp_max     = (int) $request->input('mp_max');
            $player->strength     = (float) $request->input('strength');
            $player->agility      = (float) $request->input('agility');
            $player->intuition    = (float) $request->input('intuition');
            $player->wisdom       = (float) $request->input('wisdom');
            $player->intelligence = (float) $request->input('intelligence');
            $player->min_dmg    = (float) $request->input('min_dmg');
            $player->max_dmg    = (float) $request->input('max_dmg');
            $player->free_stats = (int) $request->input('free_stats');
            $player->save();

            $player->user->money   = (int) $request->input('money');
            $player->user->diamond = (int) $request->input('diamond');
            $player->user->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $player->load(['user', 'race', 'skills.skill']);

        $backpack = Backpack::with('item.itemInfo')
            ->where('user_id', $player->user_id)
            ->orderBy('equipped', 'desc')
            ->orderBy('id')
            ->get();

        return view('admin.player.info', compact('player', 'backpack'));
    }

    public function backpackAdd(Request $request, Player $player, BackpackService $backpackService): RedirectResponse
    {
        $shareItem = ShareItem::findOrFail((int) $request->input('share_item_id'));
        $count     = max(1, (int) $request->input('count', 1));

        $backpackService->addItemByShareItem($player->user, $shareItem, $count);

        return redirect()->back()->with('success', 'Предмет добавлен в рюкзак.');
    }

    public function backpackDelete(Player $player, Backpack $backpack): RedirectResponse
    {
        $backpack->item?->delete();
        $backpack->delete();

        return redirect()->back()->with('success', 'Предмет удалён из рюкзака.');
    }
}