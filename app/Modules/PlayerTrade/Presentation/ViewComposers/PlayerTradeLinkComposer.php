<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Presentation\ViewComposers;

use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final readonly class PlayerTradeLinkComposer
{
    public function __construct(private PlayerTradeService $service) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $view->with('resumableTrade', $user ? $this->service->resumableForUser($user) : null);
    }
}
