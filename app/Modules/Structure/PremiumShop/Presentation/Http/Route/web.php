<?php

use App\Modules\Structure\PremiumShop\Presentation\Http\PremiumShopController;
use Illuminate\Support\Facades\Route;

Route::get('/premium/shop', [PremiumShopController::class, 'index'])->name('premium.shop');
Route::post('/premium/buy', [PremiumShopController::class, 'buy'])->name('premium.buy');
Route::post('/premium/add-cart', [PremiumShopController::class, 'addCart'])->name('premium.add_cart');
Route::get('/premium/delete-cart/{id}', [PremiumShopController::class, 'deleteCart'])->name('premium.delete_cart');
Route::get('/premium/clear-cart', [PremiumShopController::class, 'clearCart'])->name('premium.clear_cart');
