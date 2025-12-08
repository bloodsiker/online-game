<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use App\Services\Recovery\RecoveryStrategyFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HealthController extends Controller
{
    public function index($id)
    {
        $user = Auth::user();
        $structure = Structure::find($id);

        if (!$structure instanceof Structure) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $player = $user->player;

        $strategy = RecoveryStrategyFactory::make($structure);
        $resultDto = $strategy->recover($player, $structure);

        return view('health.heal', [
            'structure' => $structure,
            'player' => $resultDto->player,
            'healHp' => $resultDto->hpHealed,
            'healMp' => $resultDto->mpHealed,
            'buffs' => $resultDto->buffs,
        ]);
    }

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}
