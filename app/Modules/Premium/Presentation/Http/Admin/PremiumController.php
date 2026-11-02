<?php

declare(strict_types=1);

namespace App\Modules\Premium\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Premium\Application\Requests\SavePremiumRequest;
use App\Modules\Premium\Application\Services\ManagePremium;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PremiumController extends Controller
{
    public function index(): View
    {
        return view('premium::admin.index', [
            'levels' => PremiumLevel::query()->orderBy('level')->get(),
            'rate' => (int) DB::table('premium_settings')->where('id', 1)->value('gold_per_diamond'),
            'bonusTypes' => PremiumBonus::cases(),
            'exchanges' => DB::table('premium_exchanges')->join('users', 'users.id', '=', 'premium_exchanges.user_id')
                ->select('premium_exchanges.*', 'users.name as player_name')->orderByDesc('premium_exchanges.id')->paginate(50),
        ]);
    }

    public function save(SavePremiumRequest $request, ManagePremium $service): RedirectResponse
    {
        $service->execute($request->validated());

        return redirect()->route('admin.premium.index')->with('message', 'Настройки премиума сохранены.');
    }
}
