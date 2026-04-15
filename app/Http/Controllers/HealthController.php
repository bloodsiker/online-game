<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Services\Recovery\RecoveryStrategyFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HealthController extends Controller
{
    public function __construct(private PlayerStatService $statService) {}

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

        $playerDecorator = $this->statService->resolve($resultDto->player);

        return view('health.heal', [
            'structure'      => $structure,
            'player'         => $resultDto->player,
            'playerDecorator'=> $playerDecorator,
            'healHp'         => $resultDto->hpHealed,
            'healMp'         => $resultDto->mpHealed,
            'buffs'          => $resultDto->buffs,
        ]);
    }

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}
