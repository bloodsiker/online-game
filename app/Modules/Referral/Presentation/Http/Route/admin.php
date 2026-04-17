<?php

declare(strict_types=1);

use App\Modules\Referral\Presentation\Http\Admin\ReferralController;
use Illuminate\Support\Facades\Route;

Route::get('/referral/stages', [ReferralController::class, 'stages'])->name('referral.stages');
Route::post('/referral/stages', [ReferralController::class, 'storeStage'])->name('referral.stage.store');
Route::get('/referral/stages/{stage}/delete', [ReferralController::class, 'deleteStage'])->name('referral.stage.delete');
Route::get('/referral/stats', [ReferralController::class, 'stats'])->name('referral.stats');
