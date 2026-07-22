<?php

use App\Modules\Structure\Shop\Presentation\Http\ShopController;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/shop/{id}/sell-item', [ShopController::class, 'sellItem'])->name('shop.sell_item');
Route::post('/shop/{id}/add-cart', [ShopController::class, 'addCart'])->name('shop.add_cart');
Route::get('/shop/{id}/delete-cart/{cartId}', [ShopController::class, 'deleteCart'])->name('shop.delete_cart');
Route::get('/shop/{id}/clear-cart', [ShopController::class, 'clearCart'])->name('shop.clear_cart');
Route::post('/shop/{id}/purchase', [ShopController::class, 'purchase'])->name('shop.purchase');
Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop');
