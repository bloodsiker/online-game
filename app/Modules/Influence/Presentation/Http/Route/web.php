<?php

declare(strict_types=1);

use App\Modules\Influence\Presentation\Http\InfluenceShopController;
use Illuminate\Support\Facades\Route;

Route::get('/influence-shop/{structure}', [InfluenceShopController::class, 'index'])->name('influence.shop');
Route::post('/influence-shop/{structure}/buy/{shopItem}', [InfluenceShopController::class, 'buy'])->name('influence.shop.buy');
