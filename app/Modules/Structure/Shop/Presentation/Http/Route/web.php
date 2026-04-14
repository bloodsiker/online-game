<?php

use App\Modules\Structure\Shop\Presentation\Http\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/shop/{id}/buy-item/{itemId}', [ShopController::class, 'buyItem'])->name('shop.buy_item');
Route::match(['GET', 'POST'], '/shop/{id}/sell-item', [ShopController::class, 'sellItem'])->name('shop.sell_item');
Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop');