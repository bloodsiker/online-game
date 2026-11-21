<?php

use App\Modules\Structure\Shop\Presentation\Http\BarterShopController;
use App\Modules\Structure\Shop\Presentation\Http\MageTowerController;
use App\Modules\Structure\Shop\Presentation\Http\ShopController;
use Illuminate\Support\Facades\Route;

Route::post('/barter-shop/{id}/add-cart', [BarterShopController::class, 'addCart'])->name('barter_shop.add_cart');
Route::post('/barter-shop/{id}/update-cart', [BarterShopController::class, 'updateCart'])->name('barter_shop.update_cart');
Route::get('/barter-shop/{id}/delete-cart/{cartId}', [BarterShopController::class, 'deleteCart'])->name('barter_shop.delete_cart');
Route::get('/barter-shop/{id}/clear-cart', [BarterShopController::class, 'clearCart'])->name('barter_shop.clear_cart');
Route::post('/barter-shop/{id}/purchase', [BarterShopController::class, 'purchase'])->name('barter_shop.purchase');
Route::get('/barter-shop/{id}', [BarterShopController::class, 'index'])->name('barter_shop');

Route::post('/mage-tower/{id}/add-cart', [MageTowerController::class, 'addCart'])->name('mage_tower.add_cart');
Route::post('/mage-tower/{id}/update-cart', [MageTowerController::class, 'updateCart'])->name('mage_tower.update_cart');
Route::get('/mage-tower/{id}/delete-cart/{cartId}', [MageTowerController::class, 'deleteCart'])->name('mage_tower.delete_cart');
Route::get('/mage-tower/{id}/clear-cart', [MageTowerController::class, 'clearCart'])->name('mage_tower.clear_cart');
Route::post('/mage-tower/{id}/purchase', [MageTowerController::class, 'purchase'])->name('mage_tower.purchase');
Route::get('/mage-tower/{id}', [MageTowerController::class, 'index'])->name('mage_tower');

Route::get('/shop/{id}/sell-item', [ShopController::class, 'sellItem'])->name('shop.sell_item');
// Корзина на продажу — подимена shop.sell_item.*, чтобы на них действовало отключение действия «Продать»
Route::post('/shop/{id}/sell-item/cart/add', [ShopController::class, 'sellCartAdd'])->name('shop.sell_item.cart_add');
Route::post('/shop/{id}/sell-item/cart/update', [ShopController::class, 'sellCartUpdate'])->name('shop.sell_item.cart_update');
Route::get('/shop/{id}/sell-item/cart/delete/{cartId}', [ShopController::class, 'sellCartDelete'])->name('shop.sell_item.cart_delete');
Route::get('/shop/{id}/sell-item/cart/clear', [ShopController::class, 'sellCartClear'])->name('shop.sell_item.cart_clear');
Route::post('/shop/{id}/sell-item/cart/checkout', [ShopController::class, 'sellCartCheckout'])->name('shop.sell_item.cart_checkout');
Route::post('/shop/{id}/add-cart', [ShopController::class, 'addCart'])->name('shop.add_cart');
Route::post('/shop/{id}/update-cart', [ShopController::class, 'updateCart'])->name('shop.update_cart');
Route::get('/shop/{id}/delete-cart/{cartId}', [ShopController::class, 'deleteCart'])->name('shop.delete_cart');
Route::get('/shop/{id}/clear-cart', [ShopController::class, 'clearCart'])->name('shop.clear_cart');
Route::post('/shop/{id}/purchase', [ShopController::class, 'purchase'])->name('shop.purchase');
Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop');
