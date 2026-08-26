<?php

declare(strict_types=1);

namespace App\Modules\Referral\Presentation\Http;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Referral\Application\UseCases\GetReferralPage;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use Illuminate\View\View;

final class ReferralController
{
    public function __construct(
        private readonly GetReferralPage $getReferralPage,
        private readonly ReferralRepository $referralRepository,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(): View
    {
        $stages = $this->referralRepository->getActiveStages();

        $shareItems = $stages
            ->map->rewardItem
            ->filter()
            ->unique('id');

        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($shareItems));

        return view('referral::index', [
            'page' => $this->getReferralPage->handle(auth()->user()),
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }
}
