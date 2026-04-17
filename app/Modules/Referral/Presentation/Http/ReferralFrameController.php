<?php

declare(strict_types=1);

namespace App\Modules\Referral\Presentation\Http;

use App\Modules\Referral\Application\UseCases\GetReferralFrame;
use Illuminate\View\View;

final class ReferralFrameController
{
    public function __construct(private readonly GetReferralFrame $getReferralFrame) {}

    public function index(): View
    {
        return view('referral::frame', [
            'frame' => $this->getReferralFrame->handle(auth()->user()),
        ]);
    }
}
