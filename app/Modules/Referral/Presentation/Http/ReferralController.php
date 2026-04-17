<?php

declare(strict_types=1);

namespace App\Modules\Referral\Presentation\Http;

use App\Modules\Referral\Application\UseCases\GetReferralPage;
use Illuminate\View\View;

final class ReferralController
{
    public function __construct(private readonly GetReferralPage $getReferralPage) {}

    public function index(): View
    {
        return view('referral::index', [
            'page' => $this->getReferralPage->handle(auth()->user()),
        ]);
    }
}
