<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Carbon\Carbon;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function index(): View
    {
        $data = $this->referralService->getPlayerData(auth()->user());
        $data['onlineThreshold'] = Carbon::now()->subMinutes(10);

        return view('referral.index', $data);
    }
}