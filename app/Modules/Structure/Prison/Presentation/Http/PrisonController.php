<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\Structure\Prison\Infrastructure\Persistence\Models\PlayerDetention;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrisonController extends Controller
{
    public function index(int $id, Request $request, DetentionService $detentionService): View|RedirectResponse
    {
        $structure = Structure::query()->where('type', Structure::TYPE_PRISON)->findOrFail($id);
        $user = $request->user();
        $detentionService->releaseIfExpired($user);
        $activeDetention = $detentionService->activeFor($user);

        if ($activeDetention !== null && (int) $activeDetention->prison_structure_id !== $structure->id) {
            return redirect()->route('prison.index', ['id' => $activeDetention->prison_structure_id]);
        }

        if ($activeDetention === null && (int) $structure->location_id !== (int) $user->location_id) {
            return redirect()->route('location');
        }

        $detainees = PlayerDetention::query()
            ->with(['user.player', 'user.clanMembership.clan'])
            ->where('prison_structure_id', $structure->id)
            ->whereNull('released_at')
            ->where('release_at', '>', now())
            ->latest('starts_at')
            ->get();

        return view('prison::index', compact('structure', 'detainees', 'activeDetention'));
    }

    public function buyOut(int $id, Request $request, DetentionService $detentionService): RedirectResponse
    {
        if ($detentionService->releaseIfExpired($request->user())) {
            return redirect()->route('location')->with('message', 'Срок заключения завершился — вы освобождены.');
        }

        $amounts = $detentionService->buyOut($request->user(), $id);
        $paid = [];
        if ($amounts['money'] > 0) {
            $paid[] = number_format($amounts['money'], 0, '', ' ').' монет';
        }
        if ($amounts['diamond'] > 0) {
            $paid[] = number_format($amounts['diamond'], 2, '.', ' ').' бриллиантов';
        }

        return redirect()->route('location')
            ->with('message', 'Вы выкупили свободу за '.implode(' и ', $paid).'.')
            ->with('wallet_money', (int) $request->user()->fresh()->money)
            ->with('wallet_diamond', (float) $request->user()->fresh()->diamond);
    }
}
