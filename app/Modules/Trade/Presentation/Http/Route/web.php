<?php

declare(strict_types=1);

use App\Modules\Trade\Presentation\Http\TradeController;
use Illuminate\Support\Facades\Route;

Route::get('/trade/{post}', [TradeController::class, 'show'])->name('trade.show');
Route::post('/trade/{post}/{action}', [TradeController::class, 'act'])
    ->whereIn('action', ['license', 'purchase', 'sale'])->name('trade.act');
