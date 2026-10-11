<?php

declare(strict_types=1);

use App\Modules\Trade\Presentation\Http\Admin\TradeController;
use Illuminate\Support\Facades\Route;

Route::get('/trade', [TradeController::class, 'index'])->name('trade.index');
Route::post('/trade/{section}', [TradeController::class, 'save'])
    ->whereIn('section', ['posts', 'goods', 'routes', 'offers', 'stocks'])->name('trade.save');
