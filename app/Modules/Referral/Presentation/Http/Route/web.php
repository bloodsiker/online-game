<?php

declare(strict_types=1);

use App\Modules\Referral\Presentation\Http\ReferralController;
use App\Modules\Referral\Presentation\Http\ReferralFrameController;
use Illuminate\Support\Facades\Route;

Route::get('/referral', [ReferralController::class, 'index'])->name('referral');
Route::get('/who/referrals', [ReferralFrameController::class, 'index'])->name('who.referrals');
