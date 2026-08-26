<?php

namespace App\Http\Controllers;

use App\Modules\Player\Application\Services\Recovery\RecoveryStrategyFactory;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HealthController extends Controller
{
    public function __construct(private PlayerStatService $statService) {}

    public function index(Request $request, int $id): View|JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $structure = Structure::find($id);

        if (! $structure instanceof Structure) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $player = $user->player;

        $strategy = RecoveryStrategyFactory::make($structure);
        $resultDto = $strategy->recover($player, $structure);

        $playerDecorator = $this->statService->resolve($resultDto->player);

        if ($request->expectsJson()) {
            $message = 'Вы подошли к алтарю и, возложив на него руки, почувствовали, как энергия алтаря наполняет вас. Вы восстановили '
                .$resultDto->hpHealed.' единиц здоровья';

            if ($resultDto->mpHealed > 0) {
                $message .= ' и '.$resultDto->mpHealed.' единиц маны';
            }

            return response()->json([
                'message' => $message.'.',
                'hp' => ['current' => $resultDto->player->hp_now, 'max' => $playerDecorator->getHpMax()],
                'mp' => ['current' => $resultDto->player->mp_now, 'max' => $playerDecorator->getMpMax()],
            ]);
        }

        return view('health.heal', [
            'structure' => $structure,
            'player' => $resultDto->player,
            'playerDecorator' => $playerDecorator,
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
