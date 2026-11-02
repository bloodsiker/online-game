<?php

declare(strict_types=1);

namespace App\Modules\Premium\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Premium\Application\Services\ExchangeDiamonds;
use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PremiumController extends Controller
{
    public function index(Request $request, PremiumBonusService $service): View
    {
        abort_unless($service->ready(), 503, 'Премиум ещё не установлен. Примените миграцию premium_system.');
        $user = $request->user();
        $levels = PremiumLevel::query()->orderBy('level')->get();
        $period = $service->period($user->id);
        $currentLevel = $service->level($user->id);
        $rate = (int) DB::table('premium_settings')->where('id', 1)->value('gold_per_diamond');
        $configured = $rate > 0 && $levels->count() === 5 && $levels->every(fn ($level) => $level->required_diamond_cents !== null);
        $selected = max(1, min(5, $request->integer('level', $currentLevel?->level ?? 1)));
        $selectedLevel = $levels->firstWhere('level', $selected);
        $bonusTypes = PremiumBonus::cases();
        $requestId = (string) Str::uuid();

        return view('premium::index', compact('user', 'levels', 'period', 'currentLevel', 'rate', 'configured', 'selected', 'selectedLevel', 'bonusTypes', 'requestId'));
    }

    public function exchange(Request $request, ExchangeDiamonds $service): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'string', 'max:10'], 'request_id' => ['required', 'uuid']]);
        $receipt = $service->execute($request->user()->id, $data['amount'], $data['request_id']);

        return redirect()->route('premium.status')->with('message', __('premium.exchanged', [
            'diamonds' => format_money($receipt->diamond_cents / 100), 'gold' => format_money($receipt->gold_amount),
        ]));
    }
}
